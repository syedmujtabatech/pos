@extends('layouts.app')
@section('title', 'Add Supplier')

@section('content')
<div class="mb-4">
  <a href="{{ route('suppliers.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Suppliers
  </a>
  <h4 class="fw-bold mt-1">Register New Supplier (نیا سپلائر رجسٹر کریں)</h4>
</div>

<div class="card shadow-sm" style="max-width: 800px;">
  <div class="card-body p-4">
    <form action="{{ route('suppliers.store') }}" method="POST">
      @csrf

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-truck me-1"></i> Supplier Information</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Supplier / Agency Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Shan Foods Central Distribution" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Contact Person</label>
          <input type="text" name="contact_person" class="form-control" placeholder="e.g. Muhammad Kashif" value="{{ old('contact_person') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" placeholder="e.g. 0321-1234567" value="{{ old('phone') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="supplier@agency.pk" value="{{ old('email') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">NTN (Tax Registration Number)</label>
          <input type="text" name="ntn" class="form-control font-monospace" placeholder="e.g. 1234567-8" value="{{ old('ntn') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Payment Terms</label>
          <input type="text" name="payment_terms" class="form-control" placeholder="e.g. Net 30 Days" value="{{ old('payment_terms', 'Net 30 Days') }}">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Warehouse / Office Address in Karachi</label>
          <textarea name="address" class="form-control" rows="2" placeholder="e.g. Plot 28, Sector 15, Korangi Industrial Area, Karachi">{{ old('address') }}</textarea>
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-wallet me-1"></i> Credit Limit & Opening Payable Balance</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Credit Limit from Supplier</label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" name="credit_limit" class="form-control" value="{{ old('credit_limit', 1000000) }}" min="0" required>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Existing Opening Payable Balance (ہمارا واجب الادا ادھار)</label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}" min="0" required>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Save Supplier
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
