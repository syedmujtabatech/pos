@extends('layouts.app')
@section('title', 'Category Management')

@section('content')
@include('master._nav')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-category text-primary me-2"></i>Categories (پراڈکٹ کیٹیگریز)</h4>
    <p class="text-secondary small mb-0">Organize inventory with hierarchical categories & Urdu descriptions</p>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
    <i class="ti ti-plus me-1"></i> Add Category
  </button>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Category Name (English)</th>
          <th>Urdu Name (اردو نام)</th>
          <th>Parent Category</th>
          <th>Products Count</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $cat)
          <tr>
            <td class="fw-bold">{{ $cat->name }}</td>
            <td class="font-urdu text-muted fs-7">{{ $cat->name_ur ?? '-' }}</td>
            <td>
              @if($cat->parent)
                <span class="badge bg-light text-dark border">{{ $cat->parent->name }}</span>
              @else
                <span class="text-muted small">Top Level</span>
              @endif
            </td>
            <td>{{ $cat->products()->count() }} items</td>
            <td><span class="badge badge-subtle-success">Active</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Category Name (English) <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Spices & Masalay">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Urdu Name (اردو نام)</label>
            <input type="text" name="name_ur" class="form-control font-urdu text-end" placeholder="مثال: مصالحہ جات">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Parent Category</label>
            <select name="parent_id" class="form-select">
              <option value="">None (Top Level Category)</option>
              @foreach($parentCategories as $pCat)
                <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary fw-semibold">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
