@extends('layouts.app')

@section('title', 'Stock Movement Ledger')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-book text-primary me-2"></i>Stock Movement Ledger (اسٹاک لیجر)</h3>
        <p class="text-muted mb-0"><span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">Golden Rule</span> Immutable log of every physical quantity change across warehouses and shops.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary me-2">
            <i class="ti ti-building-warehouse me-1"></i>Stock Overview
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="ti ti-printer me-1"></i>Print Ledger
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.ledger') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Product</label>
                <select name="product_id" class="form-select">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Movement Type</label>
                <select name="movement_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="grn" {{ request('movement_type') == 'grn' ? 'selected' : '' }}>GRN (Purchase)</option>
                    <option value="wholesale_sale" {{ request('movement_type') == 'wholesale_sale' ? 'selected' : '' }}>Wholesale Sale</option>
                    <option value="pos_sale" {{ request('movement_type') == 'pos_sale' ? 'selected' : '' }}>POS Retail Sale</option>
                    <option value="transfer_in" {{ request('movement_type') == 'transfer_in' ? 'selected' : '' }}>Transfer IN</option>
                    <option value="transfer_out" {{ request('movement_type') == 'transfer_out' ? 'selected' : '' }}>Transfer OUT</option>
                    <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Stock Adjustment</option>
                    <option value="return_customer" {{ request('movement_type') == 'return_customer' ? 'selected' : '' }}>Sales Return</option>
                    <option value="return_supplier" {{ request('movement_type') == 'return_supplier' ? 'selected' : '' }}>Supplier Return</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Ledger Movements Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference / Document</th>
                        <th>Location</th>
                        <th>Product & Batch</th>
                        <th>Type</th>
                        <th class="text-center">Prev Qty</th>
                        <th class="text-center text-success">IN (+)</th>
                        <th class="text-center text-danger">OUT (-)</th>
                        <th class="text-center fw-bold">Balance</th>
                        <th class="text-end">Unit Cost</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <div>{{ $m->movement_date ? \Carbon\Carbon::parse($m->movement_date)->format('d M, Y') : $m->created_at->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $m->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong class="font-monospace text-primary">{{ $m->reference_type }}: #{{ $m->reference_id }}</strong>
                                @if($m->notes)
                                    <div><small class="text-muted">{{ $m->notes }}</small></div>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $m->location->name ?? 'Main Godown' }}</span></td>
                            <td>
                                <strong>{{ $m->product->name ?? 'Product' }}</strong>
                                @if($m->batch)
                                    <div><small class="text-muted">Batch: {{ $m->batch->batch_number }} (Exp: {{ $m->batch->expiry_date ? \Carbon\Carbon::parse($m->batch->expiry_date)->format('m/y') : '-' }})</small></div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeBadge = [
                                        'grn' => 'bg-success',
                                        'wholesale_sale' => 'bg-primary',
                                        'pos_sale' => 'bg-info',
                                        'transfer_in' => 'bg-success-subtle text-success border border-success-subtle',
                                        'transfer_out' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        'adjustment' => 'bg-warning text-dark',
                                        'return_customer' => 'bg-secondary',
                                        'return_supplier' => 'bg-dark text-white',
                                    ];
                                @endphp
                                <span class="badge {{ $typeBadge[$m->movement_type] ?? 'bg-secondary' }}">
                                    {{ str_replace('_', ' ', strtoupper($m->movement_type)) }}
                                </span>
                            </td>
                            <td class="text-center text-muted">{{ number_format($m->previous_balance, 0) }}</td>
                            <td class="text-center fw-bold text-success">
                                {{ $m->quantity > 0 ? '+'.number_format($m->quantity, 0) : '-' }}
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ $m->quantity < 0 ? number_format(abs($m->quantity), 0) : '-' }}
                            </td>
                            <td class="text-center fw-bold fs-6">
                                {{ number_format($m->resulting_balance, 0) }}
                            </td>
                            <td class="text-end">
                                Rs. {{ number_format($m->unit_cost, 2) }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $m->user->name ?? 'System' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="ti ti-file-analytics fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No stock ledger entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
