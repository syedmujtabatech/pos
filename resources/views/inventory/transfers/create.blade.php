@extends('layouts.app')

@section('title', 'New Stock Transfer')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-arrows-transfer-down text-primary me-2"></i>New Stock Transfer (مال کی منتقلی)</h3>
        <p class="text-muted mb-0">Dispatch stock from Godown to Retail Shop with automatic transit locking.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.transfers') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Transfers
        </a>
    </div>
</div>

<form action="{{ route('inventory.transfers.store') }}" method="POST">
    @csrf
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">1. Transfer Route & Locations</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">From Location (بھیجنے کا گودام) <span class="text-danger">*</span></label>
                    <select name="from_location_id" id="fromLocation" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ ucfirst($loc->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-center d-flex align-items-center justify-content-center pt-3">
                    <i class="ti ti-arrow-big-right-filled text-primary fs-2"></i>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">To Destination Location (وصول کرنے والی دکان) <span class="text-danger">*</span></label>
                    <select name="to_location_id" id="toLocation" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loop->last ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ ucfirst($loc->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Notes / Transit Instructions</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Sent via Rickshaw, urgent morning restock for retail counter">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">2. Transfer Items (اشیاء کی فہرست)</h5>
            <button type="button" class="btn btn-sm btn-primary" onclick="addTransferRow()">
                <i class="ti ti-plus me-1"></i>Add Another Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="transferItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%;">Product Name & SKU</th>
                            <th style="width: 30%;">Transfer Quantity (Base Units)</th>
                            <th style="width: 10%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="items[0][product_id]" class="form-select" required>
                                    <option value="">Select product...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="any" min="1" name="items[0][quantity]" class="form-control" placeholder="Qty (e.g. 24)" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="ti ti-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="ti ti-send me-1"></i>Dispatch Transfer
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let rowIndex = 1;
    const productsOptions = `@foreach($products as $p)<option value="{{ $p->id }}">{{ addslashes($p->name) }} ({{ $p->sku }})</option>@endforeach`;

    function addTransferRow() {
        const tbody = document.querySelector('#transferItemsTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select" required>
                    <option value="">Select product...</option>
                    ${productsOptions}
                </select>
            </td>
            <td>
                <input type="number" step="any" min="1" name="items[${rowIndex}][quantity]" class="form-control" placeholder="Qty" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="ti ti-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
    }

    function removeRow(btn) {
        const tbody = document.querySelector('#transferItemsTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('At least one item is required in the transfer.');
        }
    }
</script>
@endpush
@endsection
