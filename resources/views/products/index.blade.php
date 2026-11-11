@extends('layouts.app')
@section('title', 'Products & Items (پروڈکٹس اور اشیاء)')

@section('content')
@include('master._nav')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-box-seam text-primary me-2"></i>Products & Items (پروڈکٹس اور اشیاء)</h4>
    <p class="text-secondary small mb-0">کل <strong>{{ $products->total() }}</strong> رجسٹرڈ آئٹمز اور پراڈکٹس کی تفصیلات اور ریٹس</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('products.export-csv') }}" class="btn btn-sm btn-outline-secondary">
      <i class="ti ti-file-spreadsheet me-1"></i> Export CSV
    </a>
    <a href="{{ route('products.barcodes') }}" class="btn btn-sm btn-outline-primary">
      <i class="ti ti-barcode me-1"></i> Barcode Labels
    </a>
    <a href="{{ route('products.bulk-rates') }}" class="btn btn-sm btn-outline-info">
      <i class="ti ti-percentage me-1"></i> Bulk Rates
    </a>
    <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">
      <i class="ti ti-plus me-1"></i> Add Product
    </a>
  </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
  <div class="card-body p-3">
    <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by name, Urdu name, SKU, barcode..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-3">
        <select name="category_id" class="form-select form-select-sm">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select name="brand_id" class="form-select form-select-sm">
          <option value="">All Brands</option>
          @foreach($brands as $b)
            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Products Table -->
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>SKU / Barcode</th>
          <th>Product Name</th>
          <th>Category & Brand</th>
          <th>Base Unit</th>
          @if(!auth()->user()->isSalesman())
            <th class="text-end">Cost (خرید)</th>
          @endif
          <th class="text-end">Wholesale (تھوک)</th>
          <th class="text-end">Retail (پرچون)</th>
          <th class="text-center">Total Stock</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $p)
          <tr>
            <td>
              <div class="fw-semibold font-monospace small">{{ $p->sku }}</div>
              <small class="text-muted" style="font-size: 0.7rem;">{{ $p->barcode }}</small>
            </td>
            <td>
              <div class="fw-bold">{{ $p->name }}</div>
              @if($p->name_ur)
                <div class="text-muted font-urdu fs-8">{{ $p->name_ur }}</div>
              @endif
            </td>
            <td>
              <div>{{ $p->category ? $p->category->name : '-' }}</div>
              <small class="text-muted">{{ $p->brand ? $p->brand->name : '-' }}</small>
            </td>
            <td><span class="badge bg-light text-dark border">{{ $p->baseUnit ? $p->baseUnit->name : 'Piece' }}</span></td>
            @if(!auth()->user()->isSalesman())
              <td class="text-end">Rs. {{ number_format($p->purchase_cost, 2) }}</td>
            @endif
            <td class="text-end fw-semibold text-primary">Rs. {{ number_format($p->wholesale_price, 2) }}</td>
            <td class="text-end fw-semibold text-success">Rs. {{ number_format($p->retail_price, 2) }}</td>
            <td class="text-center">
              @php $stock = $p->total_stock; @endphp
              <span class="badge {{ $stock <= $p->reorder_level ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                {{ number_format($stock) }}
              </span>
            </td>
            <td>
              @if($p->is_active)
                <span class="badge badge-subtle-success">Active</span>
              @else
                <span class="badge badge-subtle-danger">Inactive</span>
              @endif
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-light border" title="Edit Product">
                  <i class="ti ti-edit"></i>
                </a>
                <form action="{{ route('products.toggle', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Change product active status?')">
                  @csrf
                  <button type="submit" class="btn btn-light border text-{{ $p->is_active ? 'warning' : 'success' }}" title="Toggle Status">
                    <i class="ti ti-power"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-5">
              No products found matching the criteria.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($products->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $products->links() }}
    </div>
  @endif
</div>
@endsection
