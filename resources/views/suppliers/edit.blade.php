@extends('layouts.app')
@section('title', 'Edit Supplier - ' . $supplier->name)

@section('content')
<div class="mb-4">
  <a href="{{ route('suppliers.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Suppliers
  </a>
  <h4 class="fw-bold mt-1">Edit Supplier: {{ $supplier->name }}</h4>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
  <div class="card-body p-4">
    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
      @csrf
      @method('PUT')

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-truck me-1"></i> Supplier Information</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Supplier / Agency Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Contact Person</label>
          <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">NTN</label>
          <input type="text" name="ntn" class="form-control font-monospace" value="{{ old('ntn', $supplier->ntn) }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Payment Terms</label>
          <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $supplier->payment_terms) }}">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Warehouse / Office Address</label>
          <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-wallet me-1"></i> Balance & Terms</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Credit Limit</label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit', $supplier->credit_limit) }}" min="0" required>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Current Payable Balance</label>
          <input type="text" class="form-control bg-light fw-bold text-danger" value="Rs. {{ number_format($supplier->current_balance, 2) }}" readonly>
        </div>
        <div class="col-12">
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="suppActive" value="1" {{ $supplier->is_active ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="suppActive">Supplier account is active</label>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Update Supplier
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
