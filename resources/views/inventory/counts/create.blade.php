@extends('layouts.app')

@section('title', 'Conduct Physical Stock Count')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-clipboard-check text-primary me-2"></i>Conduct Physical Stock Count (اسٹاک پڑتال فارم)</h3>
        <p class="text-muted mb-0">Record physical shelf counts, compare with system quantities, and compute variance in real-time.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.counts') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Counts
        </a>
    </div>
</div>

<form action="{{ route('inventory.counts.store') }}" method="POST">
    @csrf
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">1. Audit Setup</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Location to Audit <span class="text-danger">*</span></label>
                    <select name="location_id" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ ucfirst($loc->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Count Date <span class="text-danger">*</span></label>
                    <input type="date" name="count_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">2. Physical Count Entries</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="addCountRow()">
                <i class="ti ti-plus me-1"></i>Add Product
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="countsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">Product Name & SKU</th>
                            <th style="width: 25%;">Physical Counted Qty</th>
                            <th style="width: 25%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products->take(5) as $i => $prod)
                            <tr>
                                <td>
                                    <select name="items[{{ $i }}][product_id]" class="form-select" required>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ $p->id === $prod->id ? 'selected' : '' }}>
                                                {{ $p->name }} ({{ $p->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="any" min="0" name="items[{{ $i }}][counted_qty]" class="form-control" placeholder="Counted Qty" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCountRow(this)"><i class="ti ti-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="ti ti-check me-1"></i>Save Physical Count & Compute Variance
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let cIndex = {{ $products->take(5)->count() }};
    const pOptions = `@foreach($products as $p)<option value="{{ $p->id }}">{{ addslashes($p->name) }} ({{ $p->sku }})</option>@endforeach`;

    function addCountRow() {
        const tbody = document.querySelector('#countsTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items/${cIndex}/[product_id]" class="form-select" required>
                    <option value="">Select product...</option>
                    ${pOptions}
                </select>
            </td>
            <td>
                <input type="number" step="any" min="0" name="items[${cIndex}][counted_qty]" class="form-control" placeholder="Counted Qty" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCountRow(this)"><i class="ti ti-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        cIndex++;
    }

    function removeCountRow(btn) {
        const tbody = document.querySelector('#countsTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('At least one item is required in the count.');
        }
    }
</script>
@endpush
@endsection
