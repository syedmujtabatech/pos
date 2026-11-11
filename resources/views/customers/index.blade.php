@extends('layouts.app')
@section('title', 'Customers Directory')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
  <div>
    <h4 class="fw-bold mb-1">Customers & Accounts (گاہک اور کھاتے دار)</h4>
    <p class="text-secondary small mb-0">Total <strong>{{ $customers->total() }}</strong> wholesale & retail stores with credit limits and ledgers</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('customers.aging') }}" class="btn btn-sm btn-outline-danger">
      <i class="ti ti-clock-pause me-1"></i> Aging Report (0-90+ Days)
    </a>
    <a href="{{ route('finance.receipts') }}" class="btn btn-sm btn-outline-success">
      <i class="ti ti-wallet me-1"></i> Collect Payment
    </a>
    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
      <i class="ti ti-plus me-1"></i> Add Customer
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body p-3">
    <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search customer, shop name, phone..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-3">
        <select name="area_id" class="form-select form-select-sm">
          <option value="">All Areas (Karachi)</option>
          @foreach($areas as $area)
            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="salesman_id" class="form-select form-select-sm">
          <option value="">All Salesmen</option>
          @foreach($salesmen as $sm)
            <option value="{{ $sm->id }}" {{ request('salesman_id') == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Customers Table -->
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Shop & Customer</th>
          <th>Area / Route</th>
          <th>Assigned Salesman</th>
          <th>Type</th>
          <th class="text-end">Credit Limit</th>
          <th class="text-end">Current Udhaar (بقایا)</th>
          <th>Credit Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $c)
          <tr>
            <td>
              <div class="fw-bold">{{ $c->shop_name }}</div>
              <small class="text-muted">{{ $c->name }} • {{ $c->phone }}</small>
            </td>
            <td>
              <div>{{ $c->area ? $c->area->name : 'Karachi' }}</div>
              <small class="text-muted font-monospace">{{ $c->area ? $c->area->route_code : '' }}</small>
            </td>
            <td>
              @if($c->assignedSalesman)
                <span class="badge bg-light text-dark border">{{ $c->assignedSalesman->name }}</span>
              @else
                <span class="text-muted small">None</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $c->customer_type === 'wholesale' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} text-capitalize">
                {{ $c->customer_type }}
              </span>
            </td>
            <td class="text-end">Rs. {{ number_format($c->credit_limit) }}</td>
            <td class="text-end fw-bold {{ $c->current_balance > 0 ? 'text-danger' : 'text-success' }}">
              Rs. {{ number_format($c->current_balance, 2) }}
            </td>
            <td>
              @if($c->credit_limit > 0 && $c->current_balance >= $c->credit_limit)
                <span class="badge bg-danger">Limit Cross</span>
              @elseif($c->hasOverdueInvoices(60))
                <span class="badge bg-warning text-dark">60+ Days Due</span>
              @else
                <span class="badge bg-success-subtle text-success">Good</span>
              @endif
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="{{ route('customers.ledger', $c->id) }}" class="btn btn-outline-primary" title="View Customer Ledger">
                  <i class="ti ti-book me-1"></i> Ledger
                </a>
                <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-light border" title="Edit Customer">
                  <i class="ti ti-edit"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No customers found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($customers->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $customers->links() }}
    </div>
  @endif
</div>
@endsection
