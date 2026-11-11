<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Location;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\StockBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display the Modern Fast POS Terminal.
     * Never blocks on shifts or complex accounting barriers!
     */
    public function index()
    {
        $user = auth()->user();
        $shop = Location::where('type', 'retail_shop')->first() ?? Location::first();

        // Auto-ensure background active shift exists so database constraints stay valid
        $shift = PosShift::firstOrCreate(
            ['cashier_id' => $user->id, 'status' => 'open'],
            [
                'location_id' => $shop->id,
                'opened_at' => now(),
                'opening_cash' => 0,
                'notes' => 'Auto-opened register session',
            ]
        );

        // Fetch active products with category and stock balance
        $products = Product::where('is_active', true)
            ->with(['category', 'baseUnit'])
            ->get()
            ->map(function ($p) use ($shop) {
                $bal = StockBalance::where('product_id', $p->id)
                    ->where('location_id', $shop->id)
                    ->first();

                $stock = $bal ? (float)$bal->available : 100;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'barcode' => $p->barcode ?: $p->sku,
                    'sku' => $p->sku,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name ?? 'General',
                    'price' => (float)$p->retail_price,
                    'cost' => (float)$p->purchase_cost,
                    'stock' => $stock,
                    'image' => $p->image,
                ];
            });

        // Categories for quick-filter tabs
        $categories = Category::all(['id', 'name']);

        // Active customers with their current Khata/Credit balances
        $customers = Customer::where('is_active', true)
            ->get(['id', 'name', 'phone', 'current_balance', 'credit_limit']);

        // Store settings for receipt & calculations
        $settings = [
            'store_name' => Setting::get('company_name', 'ApexPOS Store'),
            'store_name_ur' => Setting::get('company_name_ur', 'ApexPOS'),
            'tagline' => Setting::get('tagline', 'Smart Retail & Point of Sale System'),
            'address' => Setting::get('address', 'Main Commercial Market, Counter #1'),
            'phone' => Setting::get('phone', '+92 300 1234567'),
            'currency' => Setting::get('currency_symbol', 'Rs.'),
            'footer_note' => Setting::get('invoice_footer_note', 'Thank you for shopping with us! Please keep this receipt.'),
            'default_tax' => (float)Setting::get('default_tax_percent', 0),
        ];

        // Today's summary stats
        $today = Carbon::today()->toDateString();
        $todayInvoices = Invoice::where('invoice_date', $today)->where('status', 'posted');
        $todaySalesCount = $todayInvoices->count();
        $todayRevenue = (float)$todayInvoices->sum('grand_total');

        // Recent 6 sales for fast reprinting
        $recentInvoices = Invoice::with(['customer', 'items'])
            ->latest('id')
            ->take(6)
            ->get();

        return view('pos.index', compact(
            'products',
            'categories',
            'customers',
            'settings',
            'todaySalesCount',
            'todayRevenue',
            'recentInvoices',
            'shift'
        ));
    }

    /**
     * Process POS Fast Checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,card,online,credit',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        $shop = Location::where('type', 'retail_shop')->first() ?? Location::first();
        $user = auth()->user();

        // Ensure active shift
        $shift = PosShift::firstOrCreate(
            ['cashier_id' => $user->id, 'status' => 'open'],
            ['location_id' => $shop->id, 'opened_at' => now(), 'opening_cash' => 0]
        );

        $customer = Customer::findOrFail($request->customer_id);
        $subtotal = (float)$request->subtotal;
        $discount = (float)($request->discount ?? 0);
        $tax = (float)($request->tax ?? 0);
        $grandTotal = (float)$request->grand_total;
        $paymentType = $request->payment_type;
        $cashReceived = (float)($request->cash_received ?? $grandTotal);
        $changeDue = max(0, $cashReceived - $grandTotal);

        try {
            $invoice = DB::transaction(function () use (
                $request, $shop, $shift, $user, $customer,
                $subtotal, $discount, $tax, $grandTotal, $paymentType, $cashReceived
            ) {
                // Generate Invoice Number
                $todayDate = now()->format('ymd');
                $randomNum = rand(1000, 9999);
                $invoiceNumber = "POS-{$todayDate}-{$randomNum}";

                $isCredit = ($paymentType === 'credit');
                $paidAmount = $isCredit ? 0 : $grandTotal;
                $dueAmount = $isCredit ? $grandTotal : 0;

                $cogsTotal = 0;

                // Create master invoice
                $inv = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'invoice_type' => 'retail_pos',
                    'customer_id' => $customer->id,
                    'salesman_id' => $user->id,
                    'location_id' => $shop->id,
                    'pos_shift_id' => $shift->id,
                    'invoice_date' => now()->toDateString(),
                    'payment_type' => $paymentType,
                    'subtotal' => $subtotal,
                    'product_discount' => 0,
                    'invoice_discount' => $discount,
                    'tax' => $tax,
                    'delivery_charges' => 0,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                    'cogs_total' => 0,
                    'profit_total' => 0,
                    'status' => 'posted',
                    'created_by' => $user->id,
                ]);

                // Create items & deduct stock
                foreach ($request->items as $item) {
                    $prod = Product::find($item['product_id']);
                    $qty = (float)$item['quantity'];
                    $price = (float)$item['unit_price'];
                    $cost = (float)($prod->purchase_cost ?? 0);

                    $lineTotal = round($qty * $price, 2);
                    $lineCost = round($qty * $cost, 2);
                    $lineProfit = $lineTotal - $lineCost;
                    $cogsTotal += $lineCost;

                    InvoiceItem::create([
                        'invoice_id' => $inv->id,
                        'product_id' => $prod->id,
                        'batch_id' => null,
                        'unit_id' => $prod->base_unit_id ?? 1,
                        'quantity' => $qty,
                        'base_quantity' => $qty,
                        'unit_price' => $price,
                        'unit_cost' => $cost,
                        'discount' => 0,
                        'tax' => 0,
                        'total_price' => $lineTotal,
                        'total_cost' => $lineCost,
                        'profit' => $lineProfit,
                    ]);

                    // Update stock balance
                    $stockBal = StockBalance::where('product_id', $prod->id)
                        ->where('location_id', $shop->id)
                        ->first();

                    if ($stockBal) {
                        $stockBal->decrement('on_hand', $qty);
                        $stockBal->decrement('available', $qty);
                    }
                }

                // Update profit & cogs
                $inv->update([
                    'cogs_total' => $cogsTotal,
                    'profit_total' => $grandTotal - $cogsTotal,
                ]);

                // Record payment record
                if (!$isCredit) {
                    InvoicePayment::create([
                        'invoice_id' => $inv->id,
                        'amount' => $grandTotal,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => $paymentType,
                        'reference_number' => $request->reference ?? "POS-PAY-{$inv->id}",
                        'notes' => 'Counter POS receipt checkout',
                        'recorded_by' => $user->id,
                    ]);
                } else {
                    // Udhar: Add to customer's current balance
                    $customer->increment('current_balance', $grandTotal);
                }

                return $inv;
            });

            // Load items with product for thermal receipt rendering
            $invoice->load(['items.product', 'customer']);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'date_time' => $invoice->created_at->format('d M Y, h:i A'),
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'cashier_name' => $user->name,
                    'payment_type' => strtoupper($paymentType),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'cash_received' => $cashReceived,
                    'change_due' => $changeDue,
                    'items' => $invoice->items->map(function ($it) {
                        return [
                            'name' => $it->product?->name ?? 'Item',
                            'quantity' => (float)$it->quantity,
                            'price' => (float)$it->unit_price,
                            'total' => (float)$it->total_price,
                        ];
                    }),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fast customer registration from POS counter modal.
     */
    public function quickCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:25',
            'credit_limit' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address ?? 'Local Area',
            'customer_type' => 'retail',
            'opening_balance' => 0,
            'current_balance' => 0,
            'credit_limit' => $request->credit_limit ?? 50000,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'current_balance' => 0,
                'credit_limit' => (float)$customer->credit_limit,
            ],
            'message' => "Customer {$customer->name} added successfully!",
        ]);
    }

    /**
     * Get thermal receipt data for reprinting any invoice.
     */
    public function getReceipt(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'createdBy']);

        return response()->json([
            'success' => true,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'date_time' => $invoice->created_at->format('d M Y, h:i A'),
                'customer_name' => $invoice->customer?->name ?? 'Walk-in Customer',
                'customer_phone' => $invoice->customer?->phone ?? '',
                'cashier_name' => $invoice->createdBy?->name ?? 'Cashier',
                'payment_type' => strtoupper($invoice->payment_type),
                'subtotal' => (float)$invoice->subtotal,
                'discount' => (float)$invoice->invoice_discount,
                'tax' => (float)$invoice->tax,
                'grand_total' => (float)$invoice->grand_total,
                'cash_received' => (float)$invoice->paid_amount,
                'change_due' => 0,
                'items' => $invoice->items->map(function ($it) {
                    return [
                        'name' => $it->product?->name ?? 'Item',
                        'quantity' => (float)$it->quantity,
                        'price' => (float)$it->unit_price,
                        'total' => (float)$it->total_price,
                    ];
                }),
            ],
        ]);
    }

    /**
     * View POS Invoices History.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::where('invoice_type', 'retail_pos')
            ->with(['customer', 'items', 'createdBy'])
            ->latest('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->where('invoice_date', $request->date);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $invoices = $query->paginate(20)->withQueryString();

        $totalAmount = Invoice::where('invoice_type', 'retail_pos')->sum('grand_total');
        $todayAmount = Invoice::where('invoice_type', 'retail_pos')->where('invoice_date', now()->toDateString())->sum('grand_total');

        return view('pos.invoices', compact('invoices', 'totalAmount', 'todayAmount'));
    }
}
