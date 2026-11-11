@extends('layouts.app')
@section('title', 'Units of Measure & Conversions')

@section('content')
@include('master._nav')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-ruler-2 text-primary me-2"></i>Units of Measure (پیمائش کے یونٹس اور اکائیاں)</h4>
    <p class="text-secondary small mb-0">Manage measurement units (Carton, Dozen, Piece, Kg) and conversion ratios</p>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
    <i class="ti ti-plus me-1"></i> Add Unit
  </button>
</div>

<!-- Business Rule Notice -->
<div class="alert alert-info border border-info shadow-sm mb-4">
  <div class="d-flex align-items-center gap-2">
    <i class="ti ti-info-circle fs-3 text-info"></i>
    <div>
      <div class="fw-bold">SRS Example Conversion (FR-40):</div>
      <small><strong>1 Carton = 12 Dozen = 144 Pieces.</strong> Transactions can be entered in cartons, dozens, or individual pieces. The system records movements accurately in base-unit equivalents.</small>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="ti ti-scale text-primary me-1"></i> Units of Measure (UoM)</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Unit Name</th>
              <th>Short Code</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody>
            @foreach($units as $u)
              <tr>
                <td class="fw-bold">{{ $u->name }}</td>
                <td><code>{{ $u->short_name }}</code></td>
                <td>
                  @if($u->is_base_unit)
                    <span class="badge bg-success">Base Unit</span>
                  @else
                    <span class="badge bg-secondary">Packaging Unit</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="ti ti-arrows-exchange text-success me-1"></i> Active Conversion Ratios</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Product Specific</th>
              <th>From Unit</th>
              <th>Ratio</th>
              <th>To Unit (Base)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($conversions as $cv)
              <tr>
                <td>{{ $cv->product ? $cv->product->name : 'Global Standard' }}</td>
                <td><span class="badge bg-primary">1 {{ $cv->fromUnit->name }}</span></td>
                <td class="fw-bold text-center">= {{ number_format($cv->conversion_factor) }}</td>
                <td><span class="badge bg-success">{{ $cv->toUnit->name }}s</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">Standard 1 ctn = 24 pcs / 1 doz = 12 pcs default active.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form action="{{ route('units.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add New Unit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Unit Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Bundle, Box" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Short Code</label>
            <input type="text" name="short_name" class="form-control" placeholder="e.g. bdl, box" required>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_base_unit" id="isBase" value="1">
            <label class="form-check-label" for="isBase">Is Base Unit</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Save Unit</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
