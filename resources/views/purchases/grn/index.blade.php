@extends('layouts.app')
@section('title', 'Goods Receiving Notes (GRN)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-package-import text-success me-1"></i> Goods Receiving Notes (GRN - مال کی وصولی)</h4>
    <p class="text-secondary small mb-0">Inward stock receiving, batch registration, and landed cost freight allocation (FR-68 to FR-70, FR-76)</p>
  </div>
  <a href="{{ route('purchases.grn.create') }}" class="btn btn-sm btn-success">
    <i class="ti ti-plus me-1"></i> Receive Inward Goods (GRN)
  </a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>GRN Number</th>
          <th>Supplier Agency</th>
          <th>Destination Godown</th>
          <th>Received Date</th>
          <th>Freight Charge</th>
          <th class="text-end">Total Landed Cost</th>
          <th>Received By</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($grns as $grn)
          <tr>
            <td class="fw-bold font-monospace">
              <a href="{{ route('purchases.grn.show', $grn->id) }}" class="text-decoration-none">
                {{ $grn->grn_number }}
              </a>
            </td>
            <td>
              <div class="fw-semibold">{{ $grn->supplier->name }}</div>
              @if($grn->purchaseOrder)
                <small class="text-muted">PO: {{ $grn->purchaseOrder->po_number }}</small>
              @endif
            </td>
            <td><span class="badge bg-light text-dark border">{{ $grn->location->name }}</span></td>
            <td>{{ $grn->received_date->format('d M Y') }}</td>
            <td>Rs. {{ number_format($grn->freight_charge, 2) }}</td>
            <td class="text-end fw-bold text-success">Rs. {{ number_format($grn->landed_cost_total, 2) }}</td>
            <td><small class="text-muted">{{ $grn->receivedBy->name }}</small></td>
            <td class="text-end">
              <a href="{{ route('purchases.grn.show', $grn->id) }}" class="btn btn-sm btn-light border" title="View GRN">
                <i class="ti ti-eye"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No goods receipt records found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($grns->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $grns->links() }}
    </div>
  @endif
</div>
@endsection
