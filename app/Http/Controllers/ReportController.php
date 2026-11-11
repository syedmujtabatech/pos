<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\GoodsReceipt;
use App\Models\InventoryBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('type', 'sales_summary');
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $user = auth()->user();

        $data = [];
        $locations = Location::all();
        $products = Product::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $salesmen = User::whereHas('role', function ($q) {
            $q->where('slug', 'salesman');
        })->get();

        switch ($reportType) {
            case 'current_stock':
            case 'stock_valuation':
                $data['products'] = Product::with(['category', 'baseUnit', 'stockBalances.location'])->get();
                $data['total_val'] = DB::table('stock_balances')
                    ->join('products', 'stock_balances.product_id', '=', 'products.id')
                    ->selectRaw('SUM(stock_balances.on_hand * products.purchase_cost) as val')
                    ->value('val') ?? 0;
                break;

            case 'low_stock':
                $data['products'] = Product::with(['category', 'stockBalances.location'])
                    ->where('is_active', true)
                    ->get()
                    ->filter(function ($p) {
                        return $p->total_stock <= $p->reorder_level;
                    });
                break;

            case 'near_expiry':
                $data['batches'] = InventoryBatch::with(['product', 'location'])
                    ->where('quantity', '>', 0)
                    ->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays(90))
                    ->orderBy('expiry_date', 'asc')
                    ->get();
                break;

            case 'sales_summary':
            case 'daily_sales':
                $data['invoices'] = Invoice::with(['customer', 'salesman', 'location'])
                    ->where('status', 'posted')
                    ->whereBetween('invoice_date', [$startDate, $endDate])
                    ->latest('id')
                    ->paginate(30);
                $data['total_sales'] = Invoice::where('status', 'posted')->whereBetween('invoice_date', [$startDate, $endDate])->sum('grand_total');
                $data['total_profit'] = Invoice::where('status', 'posted')->whereBetween('invoice_date', [$startDate, $endDate])->sum('profit_total');
                break;

            case 'product_profit':
                $data['items'] = InvoiceItem::select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_qty'),
                    DB::raw('SUM(total_price) as total_revenue'),
                    DB::raw('SUM(total_cost) as total_cogs'),
                    DB::raw('SUM(profit) as total_profit')
                )->whereHas('invoice', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')->whereBetween('invoice_date', [$startDate, $endDate]);
                })->groupBy('product_id')->with('product')->get();
                break;

            case 'customer_outstanding':
                $data['customers'] = Customer::where('is_active', true)
                    ->where('current_balance', '>', 0)
                    ->with(['assignedSalesman', 'area'])
                    ->orderByDesc('current_balance')
                    ->get();
                $data['total_outstanding'] = Customer::where('is_active', true)->sum('current_balance');
                break;

            case 'supplier_payable':
                $data['suppliers'] = Supplier::where('is_active', true)
                    ->where('current_balance', '>', 0)
                    ->orderByDesc('current_balance')
                    ->get();
                $data['total_payable'] = Supplier::where('is_active', true)->sum('current_balance');
                break;

            case 'expenses':
                $data['expenses'] = Expense::with(['category', 'location'])
                    ->whereBetween('expense_date', [$startDate, $endDate])
                    ->latest('id')
                    ->get();
                $data['total_expense'] = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
                break;

            case 'price_history':
                $data['histories'] = ProductPriceHistory::with(['product', 'user'])->latest('id')->paginate(30);
                break;

            case 'audit_logs':
                $data['logs'] = AuditLog::with('user')->latest('id')->paginate(40);
                break;
        }

        return view('reports.index', compact('reportType', 'startDate', 'endDate', 'data', 'locations', 'products', 'customers', 'suppliers', 'salesmen'));
    }
}
