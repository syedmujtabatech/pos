@extends('layouts.app')
@section('title', 'Add New Product')

@section('content')
<div class="mb-4">
  <a href="{{ route('products.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Products
  </a>
  <h4 class="fw-bold mt-1">Register New Product (نئی پراڈکٹ شامل کریں)</h4>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form action="{{ route('products.store') }}" method="POST">
      @csrf

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-info-circle me-1"></i> Basic Identification</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Product Name (English) <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Shan Biryani Masala 50g" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Product Name (Urdu - اردو نام)</label>
          <input type="text" name="name_ur" class="form-control font-urdu text-end" placeholder="مثال: شان بریانی مصالحہ" value="{{ old('name_ur') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">SKU (Unique Item Code) <span class="text-danger">*</span></label>
          <input type="text" name="sku" class="form-control font-monospace text-uppercase" placeholder="e.g. SHAN-BM-50" value="{{ old('sku') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Barcode (EAN/UPC)</label>
          <input type="text" name="barcode" class="form-control font-monospace" placeholder="Scan or leave empty for auto barcode" value="{{ old('barcode') }}">
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-category me-1"></i> Classification & Units</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <option value="">Select Category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Brand</label>
          <select name="brand_id" class="form-select">
            <option value="">Select Brand</option>
            @foreach($brands as $b)
              <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Manufacturer</label>
          <select name="manufacturer_id" class="form-select">
            <option value="">Select Manufacturer</option>
            @foreach($manufacturers as $m)
              <option value="{{ $m->id }}" {{ old('manufacturer_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Base Inventory Unit <span class="text-danger">*</span></label>
          <select name="base_unit_id" class="form-select" required>
            @foreach($units as $u)
              <option value="{{ $u->id }}" {{ $u->short_name === 'pcs' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_name }})</option>
            @endforeach
          </select>
          <small class="text-muted">Stock ledger will be recorded in this base unit.</small>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Carton Conversion (Pieces in 1 Carton)</label>
          <input type="number" name="carton_conversion" class="form-control" placeholder="e.g. 24 or 48" value="{{ old('carton_conversion', 24) }}">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Reorder Warning Level</label>
          <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', 15) }}" min="0">
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-cash me-1"></i> Pricing & Margins (PKR)</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Purchase Cost (خرید قیمت) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="purchase_cost" class="form-control" placeholder="0.00" value="{{ old('purchase_cost') }}" required>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Wholesale Price (تھوک ریٹ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="wholesale_price" class="form-control" placeholder="0.00" value="{{ old('wholesale_price') }}" required>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Retail Price (پرچون ریٹ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="retail_price" class="form-control" placeholder="0.00" value="{{ old('retail_price') }}" required>
          </div>
        </div>
      </div>

      <h6 class="fw-bold text-primary mb-3"><i class="ti ti-box me-1"></i> Opening Stock & Batch Tracking</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <label class="form-label fw-semibold">Opening Stock Quantity</label>
          <input type="number" step="0.01" name="opening_stock" class="form-control" placeholder="0" value="{{ old('opening_stock', 0) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Opening Stock Location</label>
          <select name="opening_location_id" class="form-select">
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}">{{ $loc->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Batch Number</label>
          <input type="text" name="batch_number" class="form-control" placeholder="BATCH-01" value="{{ old('batch_number') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Expiry Date</label>
          <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
        </div>
        <div class="col-12">
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="expiry_tracking" id="expiryTracking" value="1" checked>
            <label class="form-check-label fw-semibold" for="expiryTracking">
              Enable Expiry & Batch FEFO tracking for this item (تجویز کردہ: گروسری اور مصالحہ جات کے لیے آن رکھیں)
            </label>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('products.index') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Save Product
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
