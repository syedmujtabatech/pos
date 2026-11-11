@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
  <div>
    <h4 class="fw-bold mb-1 text-dark"><i class="ti ti-layout-dashboard text-primary me-2"></i> Store Dashboard</h4>
    <p class="text-secondary small mb-0">Overview of retail counter sales, inventory stock &amp; customer khata for <strong>POS Store</strong></p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="{{ route('pos.index') }}" class="btn btn-success fw-bold shadow-sm px-3">
      <i class="ti ti-device-tablet me-1"></i> Open POS Counter
    </a>
  </div>
</div>

<!-- Pending Approval Alert (if any) -->
@if($pendingApprovalsCount > 0 && auth()->user()->isManager())
  <div class="alert alert-warning border border-warning shadow-sm d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
      <i class="ti ti-shield-alert fs-3 text-warning"></i>
      <div>
        <div class="fw-bold">Pending Approvals Action Required!</div>
        <small class="text-secondary">There are <strong>{{ $pendingApprovalsCount }}</strong> pending requests (bill voids, stock adjustments, or credit limit overrides) waiting for approval.</small>
      </div>
    </div>
    <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-warning fw-semibold">View Approval Queue</a>
  </div>
@endif

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
  <!-- Today's Sales -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-primary">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Today's Sales (آج کی فروخت)</span>
          <h4 class="fw-bold my-1 text-primary">Rs. {{ number_format($todaySales) }}</h4>
          <span class="badge bg-primary-subtle text-primary">{{ $todayInvoicesCount }} Bills Issued</span>
        </div>
        <div class="bg-primary-subtle text-primary p-2 rounded-circle">
          <i class="ti ti-receipt fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Customer Recovery -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-success">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Today's Recovery (وصولی)</span>
          <h4 class="fw-bold my-1 text-success">Rs. {{ number_format($todayCollections) }}</h4>
          <span class="text-muted small">Udhaar Collections</span>
        </div>
        <div class="bg-success-subtle text-success p-2 rounded-circle">
          <i class="ti ti-wallet fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Purchases -->
  @if(!auth()->user()->isSalesman())
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-info">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Today's Inward (خریداری)</span>
          <h4 class="fw-bold my-1 text-info">Rs. {{ number_format($todayPurchases) }}</h4>
          <span class="text-muted small">Goods Receipts (GRN)</span>
        </div>
        <div class="bg-info-subtle text-info p-2 rounded-circle">
          <i class="ti ti-package-import fs-3"></i>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Cash in Hand -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-dark">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Cash In Hand (روکڑ)</span>
          <h4 class="fw-bold my-1 text-dark">Rs. {{ number_format($cashInHand) }}</h4>
          <span class="text-muted small">Daily Cash Book Balance</span>
        </div>
        <div class="bg-light text-dark p-2 rounded-circle border">
          <i class="ti ti-cash-banknote fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Customer Receivables -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-warning">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Total Receivables (کل ادھار)</span>
          <h4 class="fw-bold my-1 text-warning">Rs. {{ number_format($totalReceivables) }}</h4>
          <a href="{{ route('customers.aging') }}" class="small text-decoration-none">View Aging Report &rarr;</a>
        </div>
        <div class="bg-warning-subtle text-warning p-2 rounded-circle">
          <i class="ti ti-users-minus fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Supplier Payables -->
  @if(!auth()->user()->isSalesman())
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-danger">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Supplier Payables (واجبات)</span>
          <h4 class="fw-bold my-1 text-danger">Rs. {{ number_format($totalPayables) }}</h4>
          <a href="{{ route('suppliers.index') }}" class="small text-decoration-none">View Supplier Ledger &rarr;</a>
        </div>
        <div class="bg-danger-subtle text-danger p-2 rounded-circle">
          <i class="ti ti-truck fs-3"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock Valuation -->
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100 p-3 border-start border-4 border-secondary">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="text-secondary small fw-semibold text-uppercase">Stock Valuation (اسٹاک مالیت)</span>
          <h4 class="fw-bold my-1 text-secondary">Rs. {{ number_format($stockValuation) }}</h4>
          <span class="text-muted small">At Cost Price (Godown + Shop)</span>
        </div>
        <div class="bg-secondary-subtle text-secondary p-2 rounded-circle">
          <i class="ti ti-boxes fs-3"></i>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>

<!-- CHARTS & CRITICAL ALERTS -->
<div class="row g-4 mb-4">
  <!-- Sales Trend Chart -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0 fw-bold"><i class="ti ti-chart-line text-primary me-1"></i> Sales Revenue Trend (گزشتہ 7 دن کی فروخت)</h6>
          <small class="text-muted">Daily wholesale and retail revenue in PKR</small>
        </div>
        <a href="{{ route('reports.index', ['type' => 'daily_sales']) }}" class="btn btn-sm btn-outline-primary">Details</a>
      </div>
      <div class="card-body">
        <div id="salesChart" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>

  <!-- Near-Expiry Alert (FEFO) -->
  <div class="col-lg-4">
    <div class="card h-100 border-danger border-opacity-50">
      <div class="card-header bg-danger-subtle text-danger d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-calendar-due me-1"></i> Near Expiry (جلد ایکسپائر ہونے والا مال)</h6>
        <span class="badge bg-danger">{{ count($nearExpiryBatches) }}</span>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse($nearExpiryBatches as $b)
            <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold small">{{ $b->product->name }}</div>
                <small class="text-muted">Batch: {{ $b->batch_number }} • Loc: {{ $b->location->name }}</small>
              </div>
              <div class="text-end">
                <span class="badge bg-danger text-uppercase">{{ $b->expiry_date->format('d M Y') }}</span>
                <small class="d-block text-danger fw-semibold mt-1">{{ number_format($b->quantity) }} units</small>
              </div>
            </li>
          @empty
            <li class="list-group-item p-4 text-center text-muted small">
              <i class="ti ti-check-circle text-success fs-3 d-block mb-1"></i>
              No batches expiring within 90 days.
            </li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- LOW STOCK & TOP PERFORMERS -->
<div class="row g-4">
  <!-- Low Stock Alert -->
  <div class="col-lg-6">
    <div class="card h-100 border-warning border-opacity-50">
      <div class="card-header bg-warning-subtle text-warning d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-alert-triangle me-1"></i> Low Stock & Reorder Alerts (کم اسٹاک کی اطلاع)</h6>
        <a href="{{ route('purchases.orders.create') }}" class="btn btn-xs btn-outline-warning text-dark fw-semibold">Generate PO</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Reorder Level</th>
              <th>Current Stock</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lowStockProducts as $p)
              <tr>
                <td class="fw-semibold">{{ $p->name }}</td>
                <td><code>{{ $p->sku }}</code></td>
                <td>{{ $p->reorder_level }}</td>
                <td>
                  <span class="badge bg-danger">{{ $p->total_stock }}</span>
                </td>
                <td>
                  <a href="{{ route('purchases.orders.create') }}" class="btn btn-sm btn-outline-primary py-0 px-2">Order</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-3">All product stock levels are healthy.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Salesmen Recovery Performance -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-users text-primary me-1"></i> Salesmen Recovery & Route Portfolio</h6>
        <span class="badge bg-light text-secondary border">3 Salesmen Active</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Salesman</th>
              <th>Assigned Customers</th>
              <th>Total Outstanding</th>
              <th>Route / Area</th>
            </tr>
          </thead>
          <tbody>
            @foreach($salesmen as $sm)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $sm->name }}</div>
                  <small class="text-muted">{{ $sm->phone }}</small>
                </td>
                <td>{{ $sm->assignedCustomers->count() }} Stores</td>
                <td class="fw-bold text-danger">Rs. {{ number_format($sm->assigned_customers_sum_current_balance ?? 0) }}</td>
                <td><span class="badge bg-light text-dark border">{{ $sm->username === 'salesman1' ? 'Saddar / Clifton' : ($sm->username === 'salesman2' ? 'Jodia Bazaar / Kharadar' : 'Gulshan / Korangi') }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    var options = {
      series: [{
        name: 'Daily Sales (PKR)',
        data: @json($salesTrend)
      }],
      chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false }
      },
      colors: ['#2563eb'],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05,
          stops: [20, 100]
        }
      },
      xaxis: {
        categories: @json($dates),
      },
      yaxis: {
        labels: {
          formatter: function (val) {
            return "Rs. " + val.toLocaleString();
          }
        }
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return "Rs. " + val.toLocaleString();
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#salesChart"), options);
    chart.render();
  });
</script>
@endpush
