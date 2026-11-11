<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\CashBookEntry;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\DayClosing;
use App\Models\GoodsReceipt;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Today's KPIs
        $todaySalesQuery = Invoice::where('status', 'posted')->whereDate('invoice_date', $today);
        if ($user->isSalesman()) {
            $todaySalesQuery->where('salesman_id', $user->id);
        }
        $todaySales = $todaySalesQuery->sum('grand_total');
        $todayInvoicesCount = $todaySalesQuery->count();

        // Today's Collections
        $todayCollectionsQuery = CustomerReceipt::whereDate('receipt_date', $today);
        if ($user->isSalesman()) {
            $todayCollectionsQuery->where('salesman_id', $user->id);
        }
        $todayCollections = $todayCollectionsQuery->sum('amount');

        // Today's Purchases (restricted for salesman)
        $todayPurchases = $user->isSalesman() ? 0 : GoodsReceipt::where('status', 'posted')->whereDate('received_date', $today)->sum('landed_cost_total');

        // Receivables
        $customersQuery = Customer::where('is_active', true);
        if ($user->isSalesman()) {
            $customersQuery->where('assigned_salesman_id', $user->id);
        }
        $totalReceivables = $customersQuery->sum('current_balance');

        // Payables (Restricted for salesman)
        $totalPayables = $user->isSalesman() ? 0 : Supplier::where('is_active', true)->sum('current_balance');

        // Total Stock Value (Valuation) (Restricted for salesman)
        $stockValuation = 0;
        if (!$user->isSalesman()) {
            $stockValuation = DB::table('stock_balances')
                ->join('products', 'stock_balances.product_id', '=', 'products.id')
                ->selectRaw('SUM(stock_balances.on_hand * products.purchase_cost) as total_val')
                ->value('total_val') ?? 0;
        }

        // Low stock products
        $lowStockProducts = Product::where('is_active', true)
            ->whereHas('stockBalances')
            ->with(['stockBalances.location', 'category'])
            ->get()
            ->filter(function ($prod) {
                return $prod->total_stock <= $prod->reorder_level;
            })->take(6);

        // Near-expiry batches (next 90 days)
        $nearExpiryBatches = InventoryBatch::where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->with(['product', 'location'])
            ->orderBy('expiry_date', 'asc')
            ->take(6)
            ->get();

        // Top Selling Products (Last 30 Days)
        $topProducts = InvoiceItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_revenue'))
            ->whereHas('invoice', function ($q) {
                $q->where('status', 'posted');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(5)
            ->get();

        // Top Customers (by sales volume)
        $topCustomers = Customer::withSum(['invoices' => function ($q) {
            $q->where('status', 'posted');
        }], 'grand_total')
            ->orderByDesc('invoices_sum_grand_total')
            ->take(5)
            ->get();

        // Salesmen Performance Summary
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->withSum(['assignedCustomers' => function ($q) {
            $q->where('is_active', true);
        }], 'current_balance')
        ->get();

        // Cash in Hand
        $lastCashEntry = CashBookEntry::latest('id')->first();
        $cashInHand = $lastCashEntry ? $lastCashEntry->balance_after : 0;

        // Pending Approvals Count
        $pendingApprovalsCount = ApprovalRequest::where('status', 'pending')->count();

        // Day End Status
        $todayClosed = DayClosing::whereDate('closing_date', $today)->exists();

        // Monthly sales trend for chart (last 7 days)
        $dates = [];
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $label = now()->subDays($i)->format('D, d M');
            $dates[] = $label;
            $amt = Invoice::where('status', 'posted')->whereDate('invoice_date', $d)->sum('grand_total');
            $salesTrend[] = (float) $amt;
        }

        return view('dashboard.index', compact(
            'todaySales',
            'todayInvoicesCount',
            'todayCollections',
            'todayPurchases',
            'totalReceivables',
            'totalPayables',
            'stockValuation',
            'lowStockProducts',
            'nearExpiryBatches',
            'topProducts',
            'topCustomers',
            'salesmen',
            'cashInHand',
            'pendingApprovalsCount',
            'todayClosed',
            'dates',
            'salesTrend'
        ));
    }
}
