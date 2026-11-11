@extends('layouts.app')

@section('title', 'Stock Overview & Valuation')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-building-warehouse text-primary me-2"></i>Godown & Shop Inventory (اسٹاک جائزہ)</h3>
        <p class="text-muted mb-0">Separate multi-location tracking: Main Godown vs. Retail Shop with batch levels and valuation (FR-107 - FR-112).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.ledger') }}" class="btn btn-outline-primary me-2">
            <i class="ti ti-book me-1"></i>Stock Movement Ledger
        </a>
        <a href="{{ route('inventory.transfers.create') }}" class="btn btn-primary me-2">
            <i class="ti ti-arrows-transfer-down me-1"></i>Transfer Stock
        </a>
        <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-warning text-dark">
            <i class="ti ti-adjustments me-1"></i>Adjust Stock
        </a>
    </div>
</div>

<!-- Filter card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Search Product / SKU / Barcode</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search product name or SKU..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filter By Location / Godown</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations (Godown + Retail Shop)</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ ucfirst($loc->type) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-primary px-4"><i class="ti ti-filter me-1"></i>Filter Stock</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-light ms-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Stock summary table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product / SKU</th>
                        <th>Category</th>
                        @foreach($locations as $loc)
                            <th class="text-center">{{ $loc->name }}</th>
                        @endforeach
                        <th class="text-center">Total On-Hand</th>
                        <th class="text-end">Cost Price</th>
                        <th class="text-end">Stock Valuation (Cost)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandValuation = 0;
                    @endphp
                    @forelse($products as $product)
                        @php
                            $totalQty = $product->stockBalances->sum('quantity');
                            $valuation = $totalQty * $product->cost_price;
                            $grandValuation += $valuation;
                        @endphp
                        <tr>
                            <td>
                                <div><strong>{{ $product->name }}</strong></div>
                                <code class="text-muted">{{ $product->sku }}</code>
                                @if($product->urdu_name)
                                    <span class="urdu-text small text-secondary ms-1">({{ $product->urdu_name }})</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->category->name ?? 'General' }}</span></td>
                            @foreach($locations as $loc)
                                @php
                                    $locBalance = $product->stockBalances->firstWhere('location_id', $loc->id);
                                    $locQty = $locBalance ? $locBalance->quantity : 0;
                                @endphp
                                <td class="text-center">
                                    <span class="fw-semibold {{ $locQty <= 0 ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($locQty, 0) }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="text-center">
                                <span class="badge {{ $totalQty <= ($product->reorder_level ?? 5) ? 'bg-danger' : 'bg-primary' }} fs-6">
                                    {{ number_format($totalQty, 0) }} {{ $product->baseUnit->short_name ?? 'pcs' }}
                                </span>
                            </td>
                            <td class="text-end">Rs. {{ number_format($product->cost_price, 2) }}</td>
                            <td class="text-end fw-bold text-success">Rs. {{ number_format($valuation, 2) }}</td>
                            <td class="text-center">
                                @if($totalQty <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($totalQty <= ($product->reorder_level ?? 5))
                                    <span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle me-1"></i>Low Stock</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($locations) }}" class="text-center py-5 text-muted">
                                <i class="ti ti-package-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No inventory records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
