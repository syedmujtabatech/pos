<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DocumentSequence;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryBatch;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // --- Purchase Orders ---
    public function orders(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'location', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('id')->paginate(20);
        return view('purchases.orders.index', compact('orders'));
    }

    public function createOrder()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('purchases.orders.create', compact('suppliers', 'locations', 'products'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $poNumber = DocumentSequence::getNextNumber('PO');
            $subtotal = 0;

            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['unit_cost']);
            }

            $discount = (float)($request->discount ?? 0);
            $total = max(0, $subtotal - $discount);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'location_id' => $request->location_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'status' => 'approved', // Auto approved or pending
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_cost']);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total' => $itemTotal,
                    'received_quantity' => 0,
                ]);
            }

            AuditLog::log('create', $po, null, $po->toArray(), 'Purchase order created');
        });

        return redirect()->route('purchases.orders')->with('success', 'Purchase order created successfully.');
    }

    public function showOrder(PurchaseOrder $order)
    {
        $order->load(['supplier', 'location', 'items.product', 'goodsReceipts']);
        return view('purchases.orders.show', compact('order'));
    }

    // --- Goods Receiving (GRN) ---
    public function grns(Request $request)
    {
        $grns = GoodsReceipt::with(['supplier', 'location', 'purchaseOrder', 'receivedBy'])
            ->latest('id')
            ->paginate(20);

        return view('purchases.grn.index', compact('grns'));
    }

    public function createGrn(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $po = null;

        if ($request->filled('po_id')) {
            $po = PurchaseOrder::with(['items.product', 'supplier', 'location'])->find($request->po_id);
        }

        return view('purchases.grn.create', compact('suppliers', 'locations', 'products', 'po'));
    }

    public function storeGrn(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
            'received_date' => 'required|date',
            'freight_charge' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_qty' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $grnNumber = DocumentSequence::getNextNumber('GRN');
            $freight = (float)($request->freight_charge ?? 0);
            $totalQty = 0;
            $subtotal = 0;

            foreach ($request->items as $item) {
                $qty = (float)$item['received_qty'];
                $totalQty += $qty;
                $subtotal += ($qty * (float)$item['unit_cost']);
            }

            // Landed cost freight allocation per unit (FR-76)
            $freightPerUnit = $totalQty > 0 ? ($freight / $totalQty) : 0;
            $landedCostTotal = $subtotal + $freight;

            $grn = GoodsReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'location_id' => $request->location_id,
                'received_date' => $request->received_date,
                'status' => 'posted',
                'freight_charge' => $freight,
                'other_charges' => 0,
                'landed_cost_total' => $landedCostTotal,
                'notes' => $request->notes,
                'received_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $qty = (float)$item['received_qty'];
                $unitCost = (float)$item['unit_cost'];
                $allocatedLanded = $unitCost + $freightPerUnit;
                $itemTotalCost = $qty * $allocatedLanded;

                $batchNumber = !empty($item['batch_number']) ? $item['batch_number'] : ('BATCH-' . date('ymd') . '-' . rand(100, 999));
                $expiryDate = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                $grnItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'po_item_id' => $item['po_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'received_qty' => $qty,
                    'base_qty' => $qty,
                    'unit_cost' => $unitCost,
                    'allocated_landed_cost' => $freightPerUnit,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'total_cost' => $itemTotalCost,
                ]);

                // Create or update batch
                $batch = InventoryBatch::create([
                    'product_id' => $item['product_id'],
                    'location_id' => $request->location_id,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'cost_price' => $allocatedLanded,
                    'quantity' => $qty,
                    'status' => 'active',
                ]);

                // Record permanent stock movement (FR-70, FR-113)
                StockMovement::recordMovement(
                    $item['product_id'],
                    $request->location_id,
                    $qty,
                    'purchase_grn',
                    'GRN',
                    $grn->grn_number,
                    $allocatedLanded,
                    $batch->id,
                    "Goods received from {$grn->supplier->name} via GRN {$grn->grn_number}",
                    auth()->id()
                );

                // If linked to PO, update PO received qty
                if (!empty($item['po_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['po_item_id']);
                    if ($poItem) {
                        $poItem->increment('received_quantity', $qty);
                    }
                }
            }

            // Generate Supplier Invoice automatically (FR-71)
            $invNumber = 'SUP-INV-' . DocumentSequence::getNextNumber('SINV');
            SupplierInvoice::create([
                'invoice_number' => $invNumber,
                'goods_receipt_id' => $grn->id,
                'supplier_id' => $request->supplier_id,
                'invoice_date' => $request->received_date,
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'freight' => $freight,
                'tax' => 0,
                'total_amount' => $landedCostTotal,
                'paid_amount' => 0,
                'balance_amount' => $landedCostTotal,
                'status' => 'unpaid',
            ]);

            // Update Supplier payable balance
            $supplier = Supplier::find($request->supplier_id);
            $supplier->increment('current_balance', $landedCostTotal);

            AuditLog::log('create', $grn, null, $grn->toArray(), "GRN posted and inventory increased by {$totalQty} units");
        });

        return redirect()->route('purchases.grn')->with('success', 'GRN posted successfully. Stock and supplier ledger updated.');
    }

    public function showGrn(GoodsReceipt $grn)
    {
        $grn->load(['supplier', 'location', 'purchaseOrder', 'receivedBy', 'items.product']);
        return view('purchases.grn.show', compact('grn'));
    }

    // --- Supplier Invoices ---
    public function invoices()
    {
        $invoices = SupplierInvoice::with(['supplier', 'goodsReceipt'])->latest('id')->paginate(20);
        return view('purchases.invoices.index', compact('invoices'));
    }

    // --- Supplier Payments ---
    public function payments()
    {
        $payments = SupplierPayment::with(['supplier', 'createdBy', 'allocations.invoice'])->latest('id')->paginate(20);
        $suppliers = Supplier::where('is_active', true)->where('current_balance', '>', 0)->get();
        return view('purchases.payments.index', compact('payments', 'suppliers'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank,cheque',
            'amount' => 'required|numeric|min:1',
            'reference_no' => 'nullable|string',
            'cheque_no' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $payNumber = DocumentSequence::getNextNumber('PAY');
            $payment = SupplierPayment::create([
                'payment_number' => $payNumber,
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'reference_no' => $request->reference_no,
                'cheque_no' => $request->cheque_no,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Allocate against unpaid supplier invoices
            $amountToAllocate = (float)$request->amount;
            $unpaidInvoices = SupplierInvoice::where('supplier_id', $request->supplier_id)
                ->where('balance_amount', '>', 0)
                ->orderBy('invoice_date', 'asc')
                ->get();

            foreach ($unpaidInvoices as $inv) {
                if ($amountToAllocate <= 0) break;

                $alloc = min($amountToAllocate, (float)$inv->balance_amount);
                $inv->paid_amount += $alloc;
                $inv->balance_amount -= $alloc;
                $inv->status = $inv->balance_amount <= 0 ? 'paid' : 'partial';
                $inv->save();

                SupplierPaymentAllocation::create([
                    'supplier_payment_id' => $payment->id,
                    'supplier_invoice_id' => $inv->id,
                    'allocated_amount' => $alloc,
                ]);

                $amountToAllocate -= $alloc;
            }

            // Deduct supplier current balance
            $supplier = Supplier::find($request->supplier_id);
            $supplier->decrement('current_balance', $request->amount);

            // Record cash book outflow if paid via cash
            if ($request->payment_method === 'cash') {
                \App\Models\CashBookEntry::record('outflow', $request->amount, 'supplier_payment', $payNumber, "Payment to supplier {$supplier->name}");
            }

            AuditLog::log('create', $payment, null, $payment->toArray(), "Supplier payment {$payNumber} recorded");
        });

        return redirect()->route('purchases.payments')->with('success', 'Supplier payment recorded successfully.');
    }

    // --- Purchase Returns & Debit Notes ---
    public function returns()
    {
        $returns = PurchaseReturn::with(['supplier', 'goodsReceipt', 'items.product'])->latest('id')->paginate(20);
        return view('purchases.returns.index', compact('returns'));
    }
}
