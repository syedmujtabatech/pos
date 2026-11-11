@extends('layouts.app')
@section('title', 'GRN Details - ' . $grn->grn_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <a href="{{ route('purchases.grn') }}" class="text-decoration-none small text-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to GRN List
    </a>
    <h4 class="fw-bold mt-1 mb-0">Goods Received Note: {{ $grn->grn_number }}</h4>
    <small class="text-secondary">Received on {{ $grn->received_date->format('d M Y') }} by {{ $grn->receivedBy->name }}</small>
  </div>
  <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
    <i class="ti ti-printer me-1"></i> Print GRN Slip
  </button>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="row g-4">
      <div class="col-md-4">
        <span class="text-muted small d-block">Supplier Agency</span>
        <h5 class="fw-bold mb-1">{{ $grn->supplier->name }}</h5>
        <div class="text-secondary small">{{ $grn->supplier->contact_person }} • {{ $grn->supplier->phone }}</div>
      </div>
      <div class="col-md-4">
        <span class="text-muted small d-block">Destination Warehouse</span>
        <h5 class="fw-bold mb-1">{{ $grn->location->name }}</h5>
        <small class="text-secondary">Code: {{ $grn->location->code }}</small>
        @if($grn->purchaseOrder)
          <div class="mt-1">
            <span class="badge bg-light text-dark border">PO Reference: {{ $grn->purchaseOrder->po_number }}</span>
          </div>
        @endif
      </div>
      <div class="col-md-4 text-md-end">
        <span class="text-muted small d-block">Total Landed Acquisition Cost</span>
        <h3 class="fw-bold text-success mb-1">Rs. {{ number_format($grn->landed_cost_total, 2) }}</h3>
        <small class="text-muted">Includes Rs. {{ number_format($grn->freight_charge, 2) }} Freight</small>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white py-3">
    <h6 class="mb-0 fw-bold">Received Inventory Lots & Batches</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>Batch Number</th>
          <th>Expiry Date</th>
          <th class="text-end">Received Qty</th>
          <th class="text-end">Base Rate</th>
          <th class="text-end">Allocated Freight</th>
          <th class="text-end">Total Landed Cost</th>
        </tr>
      </thead>
      <tbody>
        @foreach($grn->items as $idx => $it)
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td class="fw-bold">{{ $it->product->name }}</td>
            <td><code class="fw-bold text-dark">{{ $it->batch_number }}</code></td>
            <td>
              @if($it->expiry_date)
                <span class="badge bg-light text-dark border">{{ $it->expiry_date->format('d M Y') }}</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td class="text-end fw-bold text-success">{{ number_format($it->received_qty) }}</td>
            <td class="text-end">Rs. {{ number_format($it->unit_cost, 2) }}</td>
            <td class="text-end text-muted">+ Rs. {{ number_format($it->allocated_landed_cost, 2) }}</td>
            <td class="text-end fw-bold text-dark">Rs. {{ number_format($it->total_cost, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="7" class="text-end">Total Landed Cost:</td>
          <td class="text-end text-success fs-6">Rs. {{ number_format($grn->landed_cost_total, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
