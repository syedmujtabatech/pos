<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\CashBookEntry;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\DeliveryChallan;
use App\Models\DocumentSequence;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Location;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // --- Wholesale Invoices ---
    public function wholesale(Request $request)
    {
        $user = auth()->user();
        $query = Invoice::where('invoice_type', 'wholesale')->with(['customer', 'salesman', 'location']);

        if ($user->isSalesman()) {
            $query->where('salesman_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->latest('id')->paginate(20)->withQueryString();
        $customers = Customer::where('is_active', true)->get();

        return view('sales.wholesale.index', compact('invoices', 'customers'));
    }

    public function createWholesale()
    {
        $user = auth()->user();
        $customersQuery = Customer::where('is_active', true);
        if ($user->isSalesman()) {
            $customersQuery->where('assigned_salesman_id', $user->id);
        }
        $customers = $customersQuery->get();
        $locations = Location::where('is_active', true)->get();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        $products = Product::where('is_active', true)
            ->with(['baseUnit', 'stockBalances.location', 'batches'])
            ->get();

        return view('sales.wholesale.create', compact('customers', 'locations', 'salesmen', 'products'));
    }

    public function storeWholesale(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'location_id' => 'required|exists:locations,id',
            'invoice_date' => 'required|date',
            'payment_type' => 'required|in:cash,credit,mixed,bank,cheque',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        // Check 60-day overdue rule (FR-63)
        if ($customer->hasOverdueInvoices(60) && !$request->boolean('credit_override_approved')) {
            if (!auth()->user()->isManager()) {
                return back()->withInput()->withErrors([
                    'customer_id' => "Customer has invoices overdue for 60+ days! Credit sale blocked without Manager approval.",
                ]);
            }
        }

        // Calculate totals
        $subtotal = 0;
        $productDiscountTotal = 0;
        $cogsTotal = 0;

        foreach ($request->items as $item) {
            $prod = Product::find($item['product_id']);
            $qty = (float)$item['quantity'];
            $price = (float)$item['unit_price'];
            $disc = (float)($item['discount'] ?? 0);

            $subtotal += ($qty * $price);
            $productDiscountTotal += $disc;
            $cogsTotal += ($qty * (float)$prod->purchase_cost);
        }

        $invoiceDiscount = (float)($request->invoice_discount ?? 0);
        $deliveryCharges = (float)($request->delivery_charges ?? 0);
        $grandTotal = max(0, $subtotal - $productDiscountTotal - $invoiceDiscount + $deliveryCharges);

        // Check Credit Limit rule (FR-62)
        $paid = (float)($request->paid_amount ?? 0);
        $due = max(0, $grandTotal - $paid);

        if ($due > 0 && $customer->isCreditLimitExceeded($due) && !$request->boolean('credit_override_approved')) {
            if (!auth()->user()->isManager()) {
                return back()->withInput()->withErrors([
                    'customer_id' => "Customer credit limit of Rs. " . number_format($customer->credit_limit) . " will be exceeded! Current balance: Rs. " . number_format($customer->current_balance) . ". Manager authorization required.",
                ]);
            }
        }

        // Stock availability verification (FR-86)
        foreach ($request->items as $item) {
            $balance = StockBalance::where('product_id', $item['product_id'])
                ->where('location_id', $request->location_id)
                ->first();
            $available = $balance ? $balance->available : 0;
            if ($item['quantity'] > $available) {
                $p = Product::find($item['product_id']);
                return back()->withInput()->withErrors([
                    'items' => "Insufficient stock for '{$p->name}'. Available: {$available}, Requested: {$item['quantity']}.",
                ]);
            }
        }

        $invoice = DB::transaction(function () use ($request, $customer, $subtotal, $productDiscountTotal, $invoiceDiscount, $deliveryCharges, $grandTotal, $paid, $due, $cogsTotal) {
            $invNumber = DocumentSequence::getNextNumber('INV');
            $profitTotal = $grandTotal - $deliveryCharges - $cogsTotal;

            $inv = Invoice::create([
                'invoice_number' => $invNumber,
                'invoice_type' => 'wholesale',
                'customer_id' => $customer->id,
                'salesman_id' => $request->salesman_id ?? $customer->assigned_salesman_id ?? auth()->id(),
                'location_id' => $request->location_id,
                'invoice_date' => $request->invoice_date,
                'payment_type' => $request->payment_type,
                'subtotal' => $subtotal,
                'product_discount' => $productDiscountTotal,
                'invoice_discount' => $invoiceDiscount,
                'tax' => 0,
                'delivery_charges' => $deliveryCharges,
                'grand_total' => $grandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'cogs_total' => $cogsTotal,
                'profit_total' => $profitTotal,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $prod = Product::find($item['product_id']);
                $qty = (float)$item['quantity'];
                $price = (float)$item['unit_price'];
                $disc = (float)($item['discount'] ?? 0);
                $cost = (float)$prod->purchase_cost;
                $lineTotal = ($qty * $price) - $disc;
                $lineCost = ($qty * $cost);
                $lineProfit = $lineTotal - $lineCost;

                // Batch allocation FEFO (First Expired First Out per FR-89 / FR-125)
                $batch = InventoryBatch::where('product_id', $prod->id)
                    ->where('location_id', $request->location_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'product_id' => $prod->id,
                    'batch_id' => $batch ? $batch->id : null,
                    'unit_id' => $prod->base_unit_id,
                    'quantity' => $qty,
                    'base_quantity' => $qty,
                    'unit_price' => $price,
                    'unit_cost' => $cost,
                    'discount' => $disc,
                    'tax' => 0,
                    'total_price' => $lineTotal,
                    'total_cost' => $lineCost,
                    'profit' => $lineProfit,
                ]);

                // Record permanent stock movement (FR-88, FR-113)
                StockMovement::recordMovement(
                    $prod->id,
                    $request->location_id,
                    -$qty,
                    'sale_invoice',
                    'INV',
                    $inv->invoice_number,
                    $cost,
                    $batch ? $batch->id : null,
                    "Wholesale invoice to {$customer->shop_name}",
                    auth()->id()
                );
            }

            // Payment record if paid on the spot
            if ($paid > 0) {
                InvoicePayment::create([
                    'invoice_id' => $inv->id,
                    'payment_method' => $request->payment_type === 'credit' ? 'cash' : $request->payment_type,
                    'amount' => $paid,
                    'reference_no' => 'ON-BILL',
                ]);

                CashBookEntry::record('inflow', $paid, 'wholesale_receipt', $inv->invoice_number, "Spot payment for wholesale invoice {$inv->invoice_number}");
            }

            // Update customer balance
            if ($due > 0) {
                $customer->increment('current_balance', $due);
            }

            AuditLog::log('create', $inv, null, $inv->toArray(), "Wholesale Invoice {$inv->invoice_number} posted");

            return $inv;
        });

        return redirect()->route('sales.show-invoice', $invoice->id)->with('success', 'Wholesale invoice created successfully.');
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['customer.area', 'salesman', 'location', 'items.product.baseUnit', 'payments']);
        return view('sales.invoice-view', compact('invoice'));
    }

    public function printThermal(Invoice $invoice)
    {
        $invoice->load(['customer', 'salesman', 'location', 'items.product']);
        return view('sales.print-thermal', compact('invoice'));
    }

    public function printA4(Invoice $invoice)
    {
        $invoice->load(['customer.area', 'salesman', 'location', 'items.product']);
        return view('sales.print-a4', compact('invoice'));
    }

    // --- Bill Void Process (FR-98) ---
    public function voidInvoice(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        if (!auth()->user()->isManager()) {
            // Create approval request instead of direct voiding
            ApprovalRequest::create([
                'request_type' => 'bill_void',
                'requested_by' => auth()->id(),
                'status' => 'pending',
                'reference_type' => 'Invoice',
                'reference_id' => $invoice->id,
                'data' => [
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->grand_total,
                ],
                'reason' => $request->reason,
            ]);

            return back()->with('info', 'Bill void request submitted for Manager approval.');
        }

        DB::transaction(function () use ($invoice, $request) {
            $invoice->load('items');

            // Revert stock movement (Restock items)
            foreach ($invoice->items as $item) {
                StockMovement::recordMovement(
                    $item->product_id,
                    $invoice->location_id,
                    $item->quantity, // positive to return stock
                    'sale_return',
                    'VOID',
                    $invoice->invoice_number,
                    $item->unit_cost,
                    $item->batch_id,
                    "Stock restored from voided invoice {$invoice->invoice_number}",
                    auth()->id()
                );
            }

            // Revert customer balance if credit sale
            if ($invoice->due_amount > 0) {
                $invoice->customer->decrement('current_balance', $invoice->due_amount);
            }

            // Revert cash inflow if paid
            if ($invoice->paid_amount > 0) {
                CashBookEntry::record('outflow', $invoice->paid_amount, 'void_refund', $invoice->invoice_number, "Cash reversed due to void of invoice {$invoice->invoice_number}");
            }

            $invoice->status = 'voided';
            $invoice->void_reason = $request->reason;
            $invoice->void_approved_by = auth()->id();
            $invoice->save();

            AuditLog::log('void', $invoice, null, ['status' => 'voided', 'reason' => $request->reason], "Invoice {$invoice->invoice_number} voided");
        });

        return back()->with('success', 'Invoice has been voided and stock restored.');
    }

    // --- Quotations (FR-100) ---
    public function quotations()
    {
        $quotations = Quotation::with(['customer', 'salesman'])->latest('id')->paginate(20);
        return view('sales.quotations.index', compact('quotations'));
    }

    // --- Sales Orders & Challans (FR-92 - FR-95) ---
    public function orders()
    {
        $orders = SalesOrder::with(['customer', 'salesman', 'location', 'challans'])->latest('id')->paginate(20);
        return view('sales.orders.index', compact('orders'));
    }

    public function challans()
    {
        $challans = DeliveryChallan::with(['salesOrder', 'customer'])->latest('id')->paginate(20);
        return view('sales.challans.index', compact('challans'));
    }

    // --- Sales Returns & Credit Notes (FR-96, FR-97) ---
    public function returns()
    {
        $returns = SalesReturn::with(['invoice', 'customer', 'items.product', 'creditNote'])->latest('id')->paginate(20);
        return view('sales.returns.index', compact('returns'));
    }

    public function createReturn(Request $request)
    {
        $invoice = null;
        if ($request->filled('invoice_number')) {
            $invoice = Invoice::where('invoice_number', $request->invoice_number)->with(['items.product', 'customer'])->first();
        }
        return view('sales.returns.create', compact('invoice'));
    }

    public function storeReturn(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.return_qty' => 'required|numeric|min:0.1',
            'items.*.condition' => 'required|in:saleable,damaged',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        DB::transaction(function () use ($request, $invoice) {
            $retNumber = DocumentSequence::getNextNumber('RET');
            $refundTotal = 0;

            foreach ($request->items as $it) {
                $invItem = InvoiceItem::find($it['invoice_item_id']);
                $qty = (float)$it['return_qty'];
                $refundTotal += ($qty * (float)$invItem->unit_price);
            }

            $salesReturn = SalesReturn::create([
                'return_number' => $retNumber,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'location_id' => $invoice->location_id,
                'return_date' => now()->toDateString(),
                'refund_amount' => $refundTotal,
                'return_status' => 'approved',
                'reason' => $request->reason,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            foreach ($request->items as $it) {
                $invItem = InvoiceItem::find($it['invoice_item_id']);
                $qty = (float)$it['return_qty'];
                $isSaleable = $it['condition'] === 'saleable';

                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $invItem->product_id,
                    'batch_id' => $invItem->batch_id,
                    'unit_id' => $invItem->unit_id,
                    'quantity' => $qty,
                    'base_quantity' => $qty,
                    'unit_price' => $invItem->unit_price,
                    'condition' => $it['condition'],
                    'is_restocked' => $isSaleable,
                ]);

                // If saleable, restore stock. If damaged, record to damaged bin per FR-97!
                if ($isSaleable) {
                    StockMovement::recordMovement(
                        $invItem->product_id,
                        $invoice->location_id,
                        $qty,
                        'sale_return',
                        'RET',
                        $retNumber,
                        $invItem->unit_cost,
                        $invItem->batch_id,
                        "Sale return accepted as saleable stock",
                        auth()->id()
                    );
                } else {
                    StockMovement::recordMovement(
                        $invItem->product_id,
                        $invoice->location_id,
                        0, // Not added to saleable stock
                        'damage',
                        'RET',
                        $retNumber,
                        $invItem->unit_cost,
                        $invItem->batch_id,
                        "Sale return classified as damaged/non-saleable",
                        auth()->id()
                    );
                }
            }

            // Generate Credit Note (FR-96)
            $cnNumber = DocumentSequence::getNextNumber('CN');
            CreditNote::create([
                'credit_note_number' => $cnNumber,
                'customer_id' => $invoice->customer_id,
                'sales_return_id' => $salesReturn->id,
                'amount' => $refundTotal,
                'status' => 'adjusted',
                'date' => now()->toDateString(),
                'notes' => "Credit note against Sales Return {$retNumber}",
            ]);

            // Adjust Customer Balance
            $customer = $invoice->customer;
            $customer->decrement('current_balance', $refundTotal);

            AuditLog::log('create', $salesReturn, null, $salesReturn->toArray(), "Sales return {$retNumber} processed. Credit note {$cnNumber} issued for Rs. {$refundTotal}.");
        });

        return redirect()->route('sales.returns')->with('success', 'Sales return and credit note processed successfully.');
    }
}
