@extends('layouts.app')
@section('title', 'Warehouses & Locations')

@section('content')
@include('master._nav')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-building-warehouse text-primary me-2"></i>Warehouses & Godowns (گودام اور دکانیں)</h4>
    <p class="text-secondary small mb-0">Separate inventory tracking between Main Godown, Retail Shop, and sub-locations</p>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
    <i class="ti ti-plus me-1"></i> Add Location
  </button>
</div>

<div class="row g-3">
  @foreach($locations as $loc)
    <div class="col-md-6">
      <div class="card h-100 shadow-sm border-start border-4 border-{{ $loc->type === 'godown' ? 'primary' : 'success' }}">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <span class="badge bg-light text-dark border font-monospace">{{ $loc->code }}</span>
              <h5 class="fw-bold mt-1 mb-0">{{ $loc->name }}</h5>
            </div>
            <span class="badge bg-{{ $loc->type === 'godown' ? 'primary' : 'success' }}-subtle text-{{ $loc->type === 'godown' ? 'primary' : 'success' }} px-3 py-2 text-uppercase">
              {{ $loc->type === 'godown' ? 'Central Warehouse (گودام)' : 'Retail Counter Shop (دکان)' }}
            </span>
          </div>

          <p class="text-muted small mb-3"><i class="ti ti-map-pin me-1"></i> {{ $loc->address ?? 'Karachi' }}</p>

          <div class="border-top pt-3 d-flex justify-content-between align-items-center">
            <span class="text-secondary small">Registered Products in Stock:</span>
            <span class="fw-bold fs-6">{{ $loc->stock_balances_count }} Items</span>
          </div>

          <div class="mt-3">
            <a href="{{ route('inventory.index', ['location_id' => $loc->id]) }}" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ti ti-boxes me-1"></i> View Location Stock
            </a>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('locations.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add Warehouse or Shop</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Location Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Godown #2 Korangi" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Location Code</label>
              <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. GDN-02" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Type</label>
              <select name="type" class="form-select" required>
                <option value="godown">Godown (Warehouse)</option>
                <option value="retail_shop">Retail Shop</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Address in Karachi</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fw-semibold">Save Location</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
