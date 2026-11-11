@extends('layouts.app')
@section('title', 'Add Customer')

@section('content')
<div class="mb-4">
  <a href="{{ route('customers.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Customers
  </a>
  <h4 class="fw-bold mt-1">Register New Customer (نیا گاہک رجسٹر کریں)</h4>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
  <div class="card-body p-4">
    <form action="{{ route('customers.store') }}" method="POST">
      @csrf

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-user me-1"></i> Customer & Shop Profile</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Shop / Store Name <span class="text-danger">*</span></label>
          <input type="text" name="shop_name" class="form-control" placeholder="e.g. Al-Madina Super Store" value="{{ old('shop_name') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Proprietor / Contact Person <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Haji Bashir Ahmed" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" placeholder="e.g. 0300-1234567" value="{{ old('phone') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="store@example.pk" value="{{ old('email') }}">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Market Address in Karachi</label>
          <textarea name="address" class="form-control" rows="2" placeholder="e.g. Shop #12, Bohri Bazaar, Saddar, Karachi">{{ old('address') }}</textarea>
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-map-pin me-1"></i> Route Assignment & Customer Type</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label fw-semibold mb-0">Area / Market Route <span class="text-danger">*</span></label>
            <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#quickAddAreaModal">
              <i class="ti ti-plus"></i> New Area
            </a>
          </div>
          <select name="area_id" id="areaSelectDropdown" class="form-select" required>
            <option value="">Select Area</option>
            @foreach($areas as $area)
              <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Assigned Order Booking Salesman</label>
          <select name="assigned_salesman_id" class="form-select">
            <option value="">None / Direct Store</option>
            @foreach($salesmen as $sm)
              <option value="{{ $sm->id }}" {{ old('assigned_salesman_id') == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Customer Type <span class="text-danger">*</span></label>
          <select name="customer_type" class="form-select" required>
            <option value="wholesale" selected>Wholesale (تھوک گاہک)</option>
            <option value="retail">Retail (پرچون گاہک)</option>
          </select>
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-wallet me-1"></i> Credit Limit & Opening Udhaar Balance</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Credit Limit (ادھار کی حد) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit', 500000) }}" min="0" required>
          </div>
          <small class="text-muted">Sale is blocked or requires Manager approval when exceeded (FR-62).</small>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Payment Terms (Allowed Credit Days)</label>
          <div class="input-group">
            <input type="number" name="payment_terms_days" class="form-control" value="{{ old('payment_terms_days', 30) }}" min="0" required>
            <span class="input-group-text">Days</span>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Existing Opening Udhaar Balance</label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}" min="0" required>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('customers.index') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Save Customer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Quick Modal: Add New Area -->
<div class="modal fade" id="quickAddAreaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="quickAddAreaForm" onsubmit="handleQuickAddArea(event)">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="ti ti-map-pin text-primary me-2"></i>Add New Area / Market Route</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Area / Market Name <span class="text-danger">*</span></label>
            <input type="text" id="quickAreaName" class="form-control" placeholder="e.g. Tariq Road, Hyderi Market, Liaquatabad..." required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Route Code (Optional)</label>
            <input type="text" id="quickAreaCode" class="form-control" placeholder="e.g. RT-09, TR-01">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">City</label>
            <input type="text" id="quickAreaCity" class="form-control" value="Karachi">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="quickAreaSubmitBtn">Save & Select Area</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function handleQuickAddArea(e) {
  e.preventDefault();
  const name = document.getElementById('quickAreaName').value.trim();
  const route_code = document.getElementById('quickAreaCode').value.trim();
  const city = document.getElementById('quickAreaCity').value.trim();
  const btn = document.getElementById('quickAreaSubmitBtn');

  if(!name) return;
  btn.disabled = true;
  btn.innerText = 'Saving...';

  fetch('{{ route("areas.store") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ name, route_code, city })
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    btn.innerText = 'Save & Select Area';
    if(data.success && data.area) {
      const select = document.getElementById('areaSelectDropdown');
      const opt = document.createElement('option');
      opt.value = data.area.id;
      opt.text = data.area.name;
      opt.selected = true;
      select.appendChild(opt);

      // Close modal
      const modalEl = document.getElementById('quickAddAreaModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      if(modal) modal.hide();
      document.getElementById('quickAddAreaForm').reset();
      document.getElementById('quickAreaCity').value = 'Karachi';
    } else {
      alert('Error creating area. Please try again.');
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.innerText = 'Save & Select Area';
    alert('Failed to save area. Please check input.');
  });
}
</script>
@endsection
