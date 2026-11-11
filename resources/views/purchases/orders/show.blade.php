@extends('layouts.app')
@section('title', 'PO Details - ' . $order->po_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <a href="{{ route('purchases.orders') }}" class="text-decoration-none small text-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to Orders
    </a>
    <h4 class="fw-bold mt-1 mb-0">Purchase Order: {{ $order->po_number }}</h4>
    <small class="text-secondary">Ordered on {{ $order->order_date->format('d M Y') }}</small>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
      <i class="ti ti-printer me-1"></i> Print PO
    </button>
    @if($order->status !== 'received')
      <a href="{{ route('purchases.grn.create', ['po_id' => $order->id]) }}" class="btn btn-sm btn-success">
        <i class="ti ti-package-import me-1"></i> Receive Inward GRN
      </a>
    @endif
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="row g-4">
      <div class="col-md-4">
        <span class="text-muted small d-block">Supplier Agency</span>
        <h5 class="fw-bold mb-1">{{ $order->supplier->name }}</h5>
        <div class="text-secondary small">{{ $order->supplier->contact_person }} • {{ $order->supplier->phone }}</div>
        <small class="text-muted d-block">{{ $order->supplier->address }}</small>
      </div>
      <div class="col-md-4">
        <span class="text-muted small d-block">Receiving Warehouse</span>
        <h5 class="fw-bold mb-1">{{ $order->location->name }}</h5>
        <small class="text-secondary">Code: {{ $order->location->code }}</small>
        <div class="mt-2">
          <span class="text-muted small">Expected Date:</span>
          <span class="fw-semibold">{{ $order->expected_date ? $order->expected_date->format('d M Y') : 'Immediate' }}</span>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <span class="text-muted small d-block">Order Status & Total</span>
        <h3 class="fw-bold text-primary mb-1">Rs. {{ number_format($order->total, 2) }}</h3>
        <span class="badge bg-{{ $order->status === 'received' ? 'success' : 'primary' }} px-3 py-1 text-uppercase">
          {{ ucfirst($order->status) }}
        </span>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white py-3">
    <h6 class="mb-0 fw-bold">Ordered Products List</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>SKU</th>
          <th class="text-end">Ordered Qty</th>
          <th class="text-end">Received Qty</th>
          <th class="text-end">Unit Cost</th>
          <th class="text-end">Line Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $idx => $it)
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td class="fw-bold">{{ $it->product->name }}</td>
            <td><code>{{ $it->product->sku }}</code></td>
            <td class="text-end fw-semibold">{{ number_format($it->quantity) }}</td>
            <td class="text-end fw-semibold text-{{ $it->received_quantity >= $it->quantity ? 'success' : 'warning' }}">
              {{ number_format($it->received_quantity) }}
            </td>
            <td class="text-end">Rs. {{ number_format($it->unit_cost, 2) }}</td>
            <td class="text-end fw-bold text-primary">Rs. {{ number_format($it->total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="6" class="text-end">Order Total:</td>
          <td class="text-end text-primary fs-6">Rs. {{ number_format($order->total, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
