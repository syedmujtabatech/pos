@extends('layouts.app')
@section('title', 'Wholesale Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-file-invoice text-primary me-1"></i> Wholesale Sales & Invoices (تھوک فروخت اور بل)</h4>
    <p class="text-secondary small mb-0">Manage wholesale bills, credit sales, payment allocations and delivery (FR-80 to FR-90)</p>
  </div>
  <a href="{{ route('sales.wholesale.create') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-plus me-1"></i> New Wholesale Invoice
  </a>
</div>

<!-- Filters -->
<div class="card mb-4 shadow-sm">
  <div class="card-body p-3">
    <form action="{{ route('sales.wholesale') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <select name="customer_id" class="form-select form-select-sm">
          <option value="">All Customers</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->shop_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted Bills</option>
          <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided Bills</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
        <a href="{{ route('sales.wholesale') }}" class="btn btn-sm btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Invoice #</th>
          <th>Customer / Shop</th>
          <th>Salesman</th>
          <th>Date</th>
          <th>Payment Type</th>
          <th class="text-end">Grand Total</th>
          <th class="text-end">Paid Amount</th>
          <th class="text-end">Balance Due</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr class="{{ $inv->status === 'voided' ? 'table-secondary opacity-75' : '' }}">
            <td class="fw-bold font-monospace">
              <a href="{{ route('sales.show-invoice', $inv->id) }}" class="text-decoration-none">
                {{ $inv->invoice_number }}
              </a>
            </td>
            <td>
              <div class="fw-semibold">{{ $inv->customer->shop_name }}</div>
              <small class="text-muted">{{ $inv->customer->name }}</small>
            </td>
            <td>
              @if($inv->salesman)
                <span class="badge bg-light text-dark border">{{ $inv->salesman->name }}</span>
              @else
                <span class="text-muted small">Counter</span>
              @endif
            </td>
            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $inv->payment_type === 'cash' ? 'success' : ($inv->payment_type === 'credit' ? 'warning text-dark' : 'info') }} text-uppercase">
                {{ $inv->payment_type }}
              </span>
            </td>
            <td class="text-end fw-bold text-primary">Rs. {{ number_format($inv->grand_total, 2) }}</td>
            <td class="text-end text-success fw-semibold">Rs. {{ number_format($inv->paid_amount, 2) }}</td>
            <td class="text-end fw-bold {{ $inv->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
              Rs. {{ number_format($inv->due_amount, 2) }}
            </td>
            <td>
              @if($inv->status === 'posted')
                <span class="badge badge-subtle-success">Posted</span>
              @else
                <span class="badge bg-danger" title="{{ $inv->void_reason }}">Voided</span>
              @endif
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="{{ route('sales.show-invoice', $inv->id) }}" class="btn btn-light border" title="View Bill">
                  <i class="ti ti-eye"></i>
                </a>
                <a href="{{ route('sales.print-a4', $inv->id) }}" class="btn btn-light border" target="_blank" title="Print A4">
                  <i class="ti ti-printer"></i>
                </a>
                <a href="{{ route('sales.print-thermal', $inv->id) }}" class="btn btn-light border" target="_blank" title="80mm Thermal">
                  <i class="ti ti-receipt"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-5">No wholesale invoices found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($invoices->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $invoices->links() }}
    </div>
  @endif
</div>
@endsection
