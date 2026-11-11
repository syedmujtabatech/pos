@extends('layouts.app')
@section('title', 'Purchase Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-shopping-cart text-primary me-1"></i> Purchase Orders (خریداری کے آرڈرز)</h4>
    <p class="text-secondary small mb-0">Official purchase orders sent to suppliers (FR-65 to FR-67)</p>
  </div>
  <a href="{{ route('purchases.orders.create') }}" class="btn btn-sm btn-primary">
    <i class="ti ti-plus me-1"></i> Create Purchase Order
  </a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>PO Number</th>
          <th>Supplier Agency</th>
          <th>Destination Location</th>
          <th>Order Date</th>
          <th>Expected Date</th>
          <th class="text-end">Total Amount</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $po)
          <tr>
            <td class="fw-bold font-monospace">
              <a href="{{ route('purchases.orders.show', $po->id) }}" class="text-decoration-none">
                {{ $po->po_number }}
              </a>
            </td>
            <td>
              <div class="fw-semibold">{{ $po->supplier->name }}</div>
              <small class="text-muted">{{ $po->supplier->phone }}</small>
            </td>
            <td><span class="badge bg-light text-dark border">{{ $po->location->name }}</span></td>
            <td>{{ $po->order_date->format('d M Y') }}</td>
            <td>{{ $po->expected_date ? $po->expected_date->format('d M Y') : '-' }}</td>
            <td class="text-end fw-bold text-primary">Rs. {{ number_format($po->total, 2) }}</td>
            <td>
              <span class="badge bg-{{ $po->status === 'received' ? 'success' : ($po->status === 'approved' ? 'primary' : 'warning') }}">
                {{ ucfirst($po->status) }}
              </span>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="{{ route('purchases.orders.show', $po->id) }}" class="btn btn-light border" title="View PO Details">
                  <i class="ti ti-eye"></i>
                </a>
                @if($po->status !== 'received')
                  <a href="{{ route('purchases.grn.create', ['po_id' => $po->id]) }}" class="btn btn-success" title="Receive Goods / GRN">
                    <i class="ti ti-package-import me-1"></i> Receive GRN
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No purchase orders found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($orders->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $orders->links() }}
    </div>
  @endif
</div>
@endsection
