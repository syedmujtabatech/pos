@extends('layouts.app')
@section('title', 'Bulk Rates Revision')

@section('content')
<div class="mb-4">
  <a href="{{ route('products.index') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Products
  </a>
  <h4 class="fw-bold mt-1"><i class="ti ti-percentage text-primary me-1"></i> Bulk Price Revision (تمام اشیاء کی قیمتوں میں اجتماعی تبدیلی)</h4>
  <p class="text-secondary small mb-0">Update wholesale or retail prices across an entire category at once (FR-34).</p>
</div>

<div class="card shadow-sm" style="max-width: 650px;">
  <div class="card-body p-4">
    <form action="{{ route('products.bulk-rates') }}" method="POST">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-semibold">Select Category (کیٹیگری منتخب کریں)</label>
        <select name="category_id" class="form-select" required>
          <option value="">-- Choose Category --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Target Price Field (کونسی قیمت تبدیل کرنی ہے؟)</label>
        <select name="field" class="form-select" required>
          <option value="wholesale_price">Wholesale Price (تھوک قیمت)</option>
          <option value="retail_price">Retail Price (پرچون قیمت)</option>
        </select>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Operation</label>
          <select name="operation" class="form-select" required>
            <option value="increase">Increase (+ اضافہ)</option>
            <option value="decrease">Decrease (- کمی)</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Calculation Type</label>
          <select name="change_type" class="form-select" required>
            <option value="percentage">Percentage (% فیصد)</option>
            <option value="fixed">Fixed Amount (روپے)</option>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Value to Apply (مقدار)</label>
        <input type="number" step="0.01" name="value" class="form-control form-control-lg" placeholder="e.g. 5 for 5% or 10 for Rs. 10" required>
      </div>

      <div class="alert alert-warning small">
        <i class="ti ti-alert-triangle me-1"></i> <strong>Note:</strong> All price modifications will be logged automatically to the permanent Price History ledger with user and timestamp.
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" onclick="return confirm('Are you sure you want to apply this bulk price revision?')">
        Apply Bulk Rate Revision
      </button>
    </form>
  </div>
</div>
@endsection
