@extends('layouts.app')
@section('title', 'Market Areas & Routes')

@section('content')
@include('master._nav')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-map-pin text-primary me-2"></i>Market Areas & Delivery Routes (مارکیٹ روٹس اور علاقے)</h4>
    <p class="text-secondary small mb-0">Manage customer sales routes in Karachi for order booking, salesman visits, and udhaar recovery</p>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAreaModal">
    <i class="ti ti-plus me-1"></i> Add New Area / Route
  </button>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-white">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
          <i class="ti ti-map-pins fs-4"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-semibold">Total Market Routes</span>
          <h4 class="fw-bold mb-0 text-dark">{{ $areas->total() }}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-white">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
          <i class="ti ti-users fs-4"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-semibold">Active Customers</span>
          <h4 class="fw-bold mb-0 text-success">{{ \App\Models\Customer::count() }}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm bg-white">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
          <i class="ti ti-truck fs-4"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-semibold">Delivery Coverage</span>
          <h4 class="fw-bold mb-0 text-info">Karachi Markets</h4>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Market Area Name (مارکیٹ / علاقہ)</th>
            <th>Route Code</th>
            <th>Assigned Salesman (انچارج سیلز مین)</th>
            <th>City</th>
            <th>Registered Customers</th>
            <th class="text-end" style="width: 120px;">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($areas as $area)
            <tr>
              <td class="text-muted small">{{ $area->id }}</td>
              <td>
                <div class="fw-bold text-dark fs-6">{{ $area->name }}</div>
              </td>
              <td>
                @if($area->route_code)
                  <span class="badge bg-light text-dark border font-monospace">{{ $area->route_code }}</span>
                @else
                  <span class="text-muted small">-</span>
                @endif
              </td>
              <td>
                @if($area->assignedSalesman)
                  <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="ti ti-user me-1"></i>{{ $area->assignedSalesman->name }}
                  </span>
                @else
                  <span class="text-muted small">Shared / All Salesmen</span>
                @endif
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary">{{ $area->city ?? 'Karachi' }}</span>
              </td>
              <td>
                <a href="{{ route('customers.index', ['area_id' => $area->id]) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2 py-1">
                  <i class="ti ti-users me-1"></i>{{ $area->customers_count }} Customers
                </a>
              </td>
              <td class="text-end">
                <a href="{{ route('customers.index', ['area_id' => $area->id]) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="View Customers">
                  <i class="ti ti-eye me-1"></i> View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="ti ti-map-pin-off fs-1 d-block mb-2 text-muted"></i>
                No areas created yet. Click "Add New Area / Route" above.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
      <span class="text-muted small">Showing {{ $areas->firstItem() ?? 0 }} to {{ $areas->lastItem() ?? 0 }} of {{ $areas->total() }} areas</span>
      <div>{{ $areas->links() }}</div>
    </div>
  </div>
</div>

<!-- Modal: Add New Area -->
<div class="modal fade" id="addAreaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('areas.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="ti ti-map-pin text-primary me-2"></i>Add Market Area / Route (نیا علاقہ شامل کریں)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Area / Market Name (علاقہ / مارکیٹ کا نام) <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Tariq Road, Hyderi Market, Liaquatabad, Bohri Bazaar..." required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Route Code (روٹ کوڈ - اختیاری)</label>
            <input type="text" name="route_code" class="form-control" placeholder="e.g. RT-09, JB-01, SD-04">
            <small class="text-muted">Short code used for order booking and delivery challans.</small>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Assigned Order Booking Salesman (روٹ کا انچارج سیلز مین)</label>
            <select name="assigned_salesman_id" class="form-select">
              <option value="">-- No Specific Salesman / Shared Route --</option>
              @foreach($salesmen as $sm)
                <option value="{{ $sm->id }}">{{ $sm->name }} ({{ $sm->username }})</option>
              @endforeach
            </select>
            <small class="text-muted">Customers created in this route will automatically be tagged to this salesman.</small>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">City (شہر)</label>
            <input type="text" name="city" class="form-control" value="Karachi">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4"><i class="ti ti-check me-1"></i>Save Area</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
