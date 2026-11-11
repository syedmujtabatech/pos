@extends('layouts.app')
@section('title', 'Edit Product - ' . $product->name)

@section('content')
<div class="mb-4">
  <a href="{{ route('products.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Products
  </a>
  <h4 class="fw-bold mt-1">Edit Product: {{ $product->name }}</h4>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form action="{{ route('products.update', $product->id) }}" method="POST">
          @csrf
          @method('PUT')

          <h6 class="fw-bold text-primary mb-3"><i class="ti ti-info-circle me-1"></i> Product Identification</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Name (English)</label>
              <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Product Name (Urdu - اردو نام)</label>
              <input type="text" name="name_ur" class="form-control font-urdu text-end" value="{{ old('name_ur', $product->name_ur) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">SKU</label>
              <input type="text" name="sku" class="form-control font-monospace" value="{{ old('sku', $product->sku) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Barcode</label>
              <input type="text" name="barcode" class="form-control font-monospace" value="{{ old('barcode', $product->barcode) }}">
            </div>
          </div>

          <h6 class="fw-bold text-primary mb-3"><i class="ti ti-category me-1"></i> Categories & Brands</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_id" class="form-select" required>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Brand</label>
              <select name="brand_id" class="form-select">
                <option value="">None</option>
                @foreach($brands as $b)
                  <option value="{{ $b->id }}" {{ $product->brand_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Manufacturer</label>
              <select name="manufacturer_id" class="form-select">
                <option value="">None</option>
                @foreach($manufacturers as $m)
                  <option value="{{ $m->id }}" {{ $product->manufacturer_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <h6 class="fw-bold text-primary mb-3"><i class="ti ti-cash me-1"></i> Pricing & Rate History Audit</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Purchase Cost (خرید قیمت)</label>
              <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="purchase_cost" class="form-control" value="{{ old('purchase_cost', $product->purchase_cost) }}" required>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Wholesale Price (تھوک ریٹ)</label>
              <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="wholesale_price" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price) }}" required>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Retail Price (پرچون ریٹ)</label>
              <div class="input-group">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="retail_price" class="form-control" value="{{ old('retail_price', $product->retail_price) }}" required>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Price Change Reason (وجہ تبدیلی ریٹ)</label>
              <input type="text" name="price_change_reason" class="form-control" placeholder="e.g. Factory price increase, budget revision...">
              <small class="text-muted">Recorded into permanent Price Change History log per FR-33.</small>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center border-top pt-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $product->is_active ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="isActive">Product is Active for Trading</label>
            </div>
            <button type="submit" class="btn btn-primary px-4 fw-semibold">
              <i class="ti ti-check me-1"></i> Update Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Price History Sidebar -->
  <div class="col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold"><i class="ti ti-history me-1"></i> Price Revision History (FR-33)</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 480px;">
          <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Cost</th>
                <th>Wholesale</th>
                <th>Retail</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody>
              @forelse($priceHistories as $ph)
                <tr>
                  <td>{{ $ph->created_at->format('d/m/y') }}</td>
                  <td>{{ $ph->new_cost }}</td>
                  <td>{{ $ph->new_wholesale_price }}</td>
                  <td>{{ $ph->new_retail_price }}</td>
                  <td><small class="text-muted">{{ $ph->reason }}</small></td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No price revisions logged yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
