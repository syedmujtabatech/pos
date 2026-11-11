@extends('layouts.app')
@section('title', 'Purchase Returns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-arrow-back-up text-danger me-1"></i> Purchase Returns & Supplier Debit Notes (خریداری کی واپسی)</h4>
    <p class="text-secondary small mb-0">Record returns to suppliers and adjust payable balances with debit notes (FR-73)</p>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Return #</th>
          <th>Supplier Agency</th>
          <th>Location</th>
          <th>Return Date</th>
          <th>Reason</th>
          <th class="text-end">Debit Note Amount</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($returns as $ret)
          <tr>
            <td class="fw-bold font-monospace">{{ $ret->return_number }}</td>
            <td>
              <div class="fw-semibold">{{ $ret->supplier->name }}</div>
              @if($ret->goodsReceipt)
                <small class="text-muted">GRN Ref: {{ $ret->goodsReceipt->grn_number }}</small>
              @endif
            </td>
            <td><span class="badge bg-light text-dark border">{{ $ret->location->name }}</span></td>
            <td>{{ $ret->return_date->format('d M Y') }}</td>
            <td>{{ Str::limit($ret->reason, 40) }}</td>
            <td class="text-end fw-bold text-danger">Rs. {{ number_format($ret->total_amount, 2) }}</td>
            <td>
              <span class="badge bg-{{ $ret->status === 'posted' ? 'success' : 'warning' }}">
                {{ ucfirst($ret->status) }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-5">No purchase return records found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($returns->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $returns->links() }}
    </div>
  @endif
</div>
@endsection
