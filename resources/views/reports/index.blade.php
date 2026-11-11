@extends('layouts.app')

@section('title', 'Business Reports & Analytics')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h3 class="fw-bold mb-1"><i class="ti ti-chart-dots text-primary me-2"></i>Business Reports Center (رپورٹس و گوشوارے)</h3>
        <p class="text-muted mb-0">Financial, inventory valuation, profitability, customer credit, and system audit reports (SRS Section 7).</p>
    </div>
    <div class="col-md-5 text-end">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="ti ti-printer me-1"></i>Print Report
        </button>
    </div>
</div>

<!-- Report Navigation Tabs -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-2">
        <ul class="nav nav-pills flex-column flex-md-row">
            <li class="nav-item">
                <a class="nav-link {{ in_array($reportType, ['sales_summary', 'daily_sales']) ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'sales_summary']) }}">
                    <i class="ti ti-receipt me-1"></i>Sales Summary
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'product_profit' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'product_profit']) }}">
                    <i class="ti ti-trending-up me-1"></i>Product Profitability
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ in_array($reportType, ['current_stock', 'stock_valuation']) ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'current_stock']) }}">
                    <i class="ti ti-building-warehouse me-1"></i>Stock Valuation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'low_stock' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'low_stock']) }}">
                    <i class="ti ti-alert-triangle me-1"></i>Low Stock
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'near_expiry' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'near_expiry']) }}">
                    <i class="ti ti-clock-exclamation me-1"></i>Near Expiry
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'customer_outstanding' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'customer_outstanding']) }}">
                    <i class="ti ti-users me-1"></i>Customer Udhaar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'supplier_payable' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'supplier_payable']) }}">
                    <i class="ti ti-truck me-1"></i>Supplier Payables
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'expenses' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'expenses']) }}">
                    <i class="ti ti-receipt-2 me-1"></i>Expenses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $reportType === 'audit_logs' ? 'active' : '' }}" href="{{ route('reports.index', ['type' => 'audit_logs']) }}">
                    <i class="ti ti-shield-check me-1"></i>Audit Logs
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Filters for Date-based reports -->
@if(in_array($reportType, ['sales_summary', 'daily_sales', 'product_profit', 'expenses']))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="type" value="{{ $reportType }}">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Active Report Content -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">

        <!-- 1. SALES SUMMARY -->
        @if(in_array($reportType, ['sales_summary', 'daily_sales']))
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">Sales Summary ({{ \Carbon\Carbon::parse($startDate)->format('d M') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }})</span>
                <div>
                    <span class="badge bg-primary fs-6 me-2">Total Sales: Rs. {{ number_format($data['total_sales'] ?? 0, 2) }}</span>
                    @if(auth()->user()->isOwner() || auth()->user()->isManager())
                        <span class="badge bg-success fs-6">Gross Profit: Rs. {{ number_format($data['total_profit'] ?? 0, 2) }}</span>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Salesman</th>
                            <th>Location</th>
                            <th>Payment</th>
                            <th class="text-end">Total Amount</th>
                            @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                <th class="text-end">Net Profit</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['invoices'] ?? [] as $inv)
                            <tr>
                                <td><a href="{{ route('sales.show-invoice', $inv->id) }}" class="fw-bold text-primary">{{ $inv->invoice_number }}</a></td>
                                <td>{{ $inv->invoice_date->format('d M, Y') }}</td>
                                <td>{{ $inv->customer->name ?? 'Walk-in' }}</td>
                                <td>{{ $inv->salesman->name ?? 'Direct' }}</td>
                                <td>{{ $inv->location->name ?? 'Shop' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($inv->payment_type) }}</span></td>
                                <td class="text-end fw-bold">Rs. {{ number_format($inv->grand_total, 2) }}</td>
                                @if(auth()->user()->isOwner() || auth()->user()->isManager())
                                    <td class="text-end fw-bold text-success">Rs. {{ number_format($inv->profit_total, 2) }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No sales found in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($data['invoices']) && method_exists($data['invoices'], 'hasPages') && $data['invoices']->hasPages())
                <div class="card-footer bg-white border-0 py-3">{{ $data['invoices']->links() }}</div>
            @endif

        <!-- 2. PRODUCT PROFITABILITY -->
        @elseif($reportType === 'product_profit')
            <div class="p-3 bg-light border-bottom">
                <span class="fw-bold fs-6">Product-Wise Sales & Gross Profit Margin</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th class="text-center">Sold Qty</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">COGS (Cost)</th>
                            <th class="text-end">Gross Profit</th>
                            <th class="text-end">Margin %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['items'] ?? [] as $item)
                            @php
                                $margin = $item->total_revenue > 0 ? ($item->total_profit / $item->total_revenue) * 100 : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $item->product->name ?? 'Product' }}</strong></td>
                                <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                                <td class="text-center">{{ number_format($item->total_qty, 0) }}</td>
                                <td class="text-end">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($item->total_cogs, 2) }}</td>
                                <td class="text-end fw-bold text-success">Rs. {{ number_format($item->total_profit, 2) }}</td>
                                <td class="text-end fw-semibold {{ $margin >= 15 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($margin, 1) }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No sold items recorded in this date range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 3. CURRENT STOCK & VALUATION -->
        @elseif(in_array($reportType, ['current_stock', 'stock_valuation']))
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">Current Stock Valuation Report</span>
                <span class="badge bg-success fs-6">Total Asset Value: Rs. {{ number_format($data['total_val'] ?? 0, 2) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th class="text-center">Total Stock</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Wholesale Rate</th>
                            <th class="text-end">Retail Rate</th>
                            <th class="text-end">Valuation (Cost)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['products'] ?? [] as $prod)
                            @php
                                $qty = $prod->stockBalances->sum('quantity');
                                $val = $qty * $prod->cost_price;
                            @endphp
                            <tr>
                                <td><strong>{{ $prod->name }}</strong></td>
                                <td><code>{{ $prod->sku }}</code></td>
                                <td>{{ $prod->category->name ?? 'General' }}</td>
                                <td class="text-center fw-bold">{{ number_format($qty, 0) }} {{ $prod->baseUnit->short_name ?? 'pcs' }}</td>
                                <td class="text-end">Rs. {{ number_format($prod->cost_price, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($prod->wholesale_price, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($prod->retail_price, 2) }}</td>
                                <td class="text-end fw-bold text-success">Rs. {{ number_format($val, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No stock items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 4. LOW STOCK -->
        @elseif($reportType === 'low_stock')
            <div class="p-3 bg-danger-subtle text-danger border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6"><i class="ti ti-alert-triangle me-1"></i>Critical Low Stock / Reorder Needed</span>
                <span>Items requiring immediate purchase order placement</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-center">Reorder Level</th>
                            <th class="text-center">Deficit</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['products'] ?? [] as $p)
                            @php
                                $stock = $p->total_stock;
                                $deficit = max(0, $p->reorder_level - $stock);
                            @endphp
                            <tr>
                                <td><strong>{{ $p->name }}</strong></td>
                                <td><code>{{ $p->sku }}</code></td>
                                <td>{{ $p->category->name ?? '-' }}</td>
                                <td class="text-center fw-bold text-danger">{{ number_format($stock, 0) }}</td>
                                <td class="text-center">{{ number_format($p->reorder_level, 0) }}</td>
                                <td class="text-center text-danger fw-bold">-{{ number_format($deficit, 0) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('purchases.orders.create') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-plus me-1"></i>Order From Supplier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-success fw-bold"><i class="ti ti-check me-1"></i>All stock levels are above reorder thresholds!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 5. NEAR EXPIRY -->
        @elseif($reportType === 'near_expiry')
            <div class="p-3 bg-warning-subtle text-dark border-bottom">
                <span class="fw-bold fs-6"><i class="ti ti-clock-exclamation me-1"></i>Batches Expiring in Next 90 Days</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch #</th>
                            <th>Product</th>
                            <th>Location</th>
                            <th>Expiry Date</th>
                            <th class="text-center">Remaining Quantity</th>
                            <th class="text-center">Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['batches'] ?? [] as $b)
                            @php
                                $days = now()->diffInDays(\Carbon\Carbon::parse($b->expiry_date), false);
                            @endphp
                            <tr>
                                <td><strong class="font-monospace text-primary">{{ $b->batch_number }}</strong></td>
                                <td><strong>{{ $b->product->name ?? 'Product' }}</strong></td>
                                <td>{{ $b->location->name ?? 'Main Godown' }}</td>
                                <td>{{ \Carbon\Carbon::parse($b->expiry_date)->format('d M, Y') }}</td>
                                <td class="text-center fw-bold">{{ number_format($b->quantity, 0) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $days <= 30 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $days > 0 ? $days . ' days left' : 'EXPIRED' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-success fw-bold"><i class="ti ti-check me-1"></i>No batches expiring within 90 days.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 6. CUSTOMER OUTSTANDING -->
        @elseif($reportType === 'customer_outstanding')
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">Customer Outstanding Balances (Udhaar Recovery List)</span>
                <span class="badge bg-danger fs-6">Total Udhaar: Rs. {{ number_format($data['total_outstanding'] ?? 0, 2) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Name</th>
                            <th>Shop / Business</th>
                            <th>Area / Route</th>
                            <th>Assigned Salesman</th>
                            <th class="text-end">Credit Limit</th>
                            <th class="text-end">Outstanding Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['customers'] ?? [] as $cust)
                            <tr>
                                <td><strong>{{ $cust->name }}</strong></td>
                                <td>{{ $cust->shop_name ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $cust->area->name ?? 'Karachi' }}</span></td>
                                <td>{{ $cust->assignedSalesman->name ?? 'Direct' }}</td>
                                <td class="text-end">Rs. {{ number_format($cust->credit_limit, 2) }}</td>
                                <td class="text-end fw-bold text-danger fs-6">Rs. {{ number_format($cust->current_balance, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('customers.ledger', $cust->id) }}" class="btn btn-sm btn-outline-primary">Ledger</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No outstanding customer balances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 7. SUPPLIER PAYABLE -->
        @elseif($reportType === 'supplier_payable')
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">Supplier Outstanding Payables</span>
                <span class="badge bg-danger fs-6">Total Payable: Rs. {{ number_format($data['total_payable'] ?? 0, 2) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Company / Distributor</th>
                            <th>Contact Phone</th>
                            <th class="text-end">Payable Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['suppliers'] ?? [] as $sup)
                            <tr>
                                <td><strong>{{ $sup->name }}</strong></td>
                                <td>{{ $sup->company_name ?? '-' }}</td>
                                <td>{{ $sup->phone ?? '-' }}</td>
                                <td class="text-end fw-bold text-danger fs-6">Rs. {{ number_format($sup->current_balance, 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('suppliers.ledger', $sup->id) }}" class="btn btn-sm btn-outline-primary">Ledger</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No outstanding supplier balances.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 8. EXPENSES -->
        @elseif($reportType === 'expenses')
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-6">Operating Expenses Breakdown</span>
                <span class="badge bg-danger fs-6">Total Expenses: Rs. {{ number_format($data['total_expense'] ?? 0, 2) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Payee</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['expenses'] ?? [] as $exp)
                            <tr>
                                <td>{{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d M, Y') : $exp->created_at->format('d M, Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $exp->category->name ?? 'General' }}</span></td>
                                <td>{{ $exp->location->name ?? '-' }}</td>
                                <td>{{ $exp->payee_name ?? '-' }}</td>
                                <td>{{ $exp->description ?? '-' }}</td>
                                <td class="text-end fw-bold text-danger">Rs. {{ number_format($exp->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No expenses recorded in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- 9. AUDIT LOGS -->
        @elseif($reportType === 'audit_logs')
            <div class="p-3 bg-light border-bottom">
                <span class="fw-bold fs-6"><i class="ti ti-shield-check me-2"></i>Security & Operations Audit Trail (FR-156 - FR-158)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['logs'] ?? [] as $log)
                            <tr>
                                <td>
                                    <div>{{ $log->created_at->format('d M, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                                </td>
                                <td><strong>{{ $log->user->name ?? 'System' }}</strong></td>
                                <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $log->action }}</span></td>
                                <td>{{ $log->description ?? '-' }}</td>
                                <td><code>{{ $log->ip_address ?? '127.0.0.1' }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No audit logs recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($data['logs']) && method_exists($data['logs'], 'hasPages') && $data['logs']->hasPages())
                <div class="card-footer bg-white border-0 py-3">{{ $data['logs']->links() }}</div>
            @endif

        @endif

    </div>
</div>
@endsection
