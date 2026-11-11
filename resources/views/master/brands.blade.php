@extends('layouts.app')
@section('title', 'Brands & Manufacturers')

@section('content')
@include('master._nav')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-tag text-primary me-2"></i>Brands & Manufacturers (برانڈز اور مینوفیکچررز)</h4>
    <p class="text-secondary small mb-0">Manage manufacturers and product brands</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
      <i class="ti ti-plus me-1"></i> Add Brand
    </button>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMfgModal">
      <i class="ti ti-plus me-1"></i> Add Manufacturer
    </button>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-badge text-primary me-1"></i> Registered Brands</h6>
        <span class="badge bg-primary">{{ $brands->total() }}</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Brand Name</th>
              <th>Total Products</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($brands as $b)
              <tr>
                <td class="fw-bold">{{ $b->name }}</td>
                <td>{{ $b->products()->count() }} items</td>
                <td><span class="badge badge-subtle-success">Active</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-building text-info me-1"></i> Manufacturers & Agencies</h6>
        <span class="badge bg-info">{{ $manufacturers->total() }}</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Company Name</th>
              <th>Contact / Agency</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($manufacturers as $m)
              <tr>
                <td class="fw-bold">{{ $m->name }}</td>
                <td><small class="text-muted">{{ $m->contact ?? 'Pakistan Agency' }}</small></td>
                <td><span class="badge badge-subtle-success">Active</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form action="{{ route('brands.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add Brand</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Brand Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Shan, National, Lux" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Save Brand</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Manufacturer Modal -->
<div class="modal fade" id="addMfgModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form action="{{ route('manufacturers.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add Manufacturer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Manufacturer Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Unilever Pakistan" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Save Manufacturer</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
