@extends('layouts.app')
@section('title', 'Supplier Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-receipt text-primary me-1"></i> Supplier Invoices (سپلائر انوائسز اور بل)</h4>
    <p class="text-secondary small mb-0">Generated from GRN goods receipts and tracked against payments (FR-71)</p>
  </div>
  <a href="{{ route('purchases.payments') }}" class="btn btn-sm btn-success">
    <i class="ti ti-cash-banknote me-1"></i> Make Payment
  </a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Invoice #</th>
          <th>Supplier Agency</th>
          <th>Invoice Date</th>
          <th>Due Date</th>
          <th class="text-end">Total Amount</th>
          <th class="text-end">Paid Amount</th>
          <th class="text-end">Balance Due</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td class="fw-bold font-monospace">{{ $inv->invoice_number }}</td>
            <td>
              <div class="fw-semibold">{{ $inv->supplier->name }}</div>
              @if($inv->goodsReceipt)
                <small class="text-muted">GRN: {{ $inv->goodsReceipt->grn_number }}</small>
              @endif
            </td>
            <td>{{ $inv->invoice_date->format('d M Y') }}</td>
            <td>{{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}</td>
            <td class="text-end fw-semibold">Rs. {{ number_format($inv->total_amount, 2) }}</td>
            <td class="text-end text-success">Rs. {{ number_format($inv->paid_amount, 2) }}</td>
            <td class="text-end fw-bold text-danger">Rs. {{ number_format($inv->balance_amount, 2) }}</td>
            <td>
              <span class="badge bg-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'partial' ? 'warning' : 'danger') }}">
                {{ ucfirst($inv->status) }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No supplier invoices found.</td>
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
