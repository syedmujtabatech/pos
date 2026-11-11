@extends('layouts.app')

@section('title', 'Create Stock Adjustment')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-adjustments text-warning me-2"></i>Create Stock Adjustment (اسٹاک درستگی)</h3>
        <p class="text-muted mb-0">Record physical count discrepancies, breakage, damages, or expired batches.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.adjustments') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Adjustments
        </a>
    </div>
</div>

<form action="{{ route('inventory.adjustments.store') }}" method="POST">
    @csrf
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">1. Adjustment Parameters</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Location (گودام یا دکان) <span class="text-danger">*</span></label>
                    <select name="location_id" class="form-select" required>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ ucfirst($loc->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Adjustment Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="damage">Damage / Expired Stock (Write-off)</option>
                        <option value="decrease">Physical Shortage (Minus Count)</option>
                        <option value="increase">Found Stock (Surplus Plus Count)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Reason / Note <span class="text-danger">*</span></label>
                    <input type="text" name="reason" class="form-control" placeholder="e.g., Water leakage damage in godown, or year-end physical count" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">2. Adjusted Items</h5>
            <button type="button" class="btn btn-sm btn-warning text-dark" onclick="addAdjustmentRow()">
                <i class="ti ti-plus me-1"></i>Add Another Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="adjItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%;">Product Name & SKU</th>
                            <th style="width: 30%;">Adjustment Quantity (+ for surplus, - for loss)</th>
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
                                <input type="number" step="any" name="items[0][diff_qty]" class="form-control" placeholder="e.g. -5 or 10" required>
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
            <button type="submit" class="btn btn-warning btn-lg px-5 text-dark fw-bold">
                <i class="ti ti-check me-1"></i>Submit for Approval / Post Adjustment
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let adjIndex = 1;
    const productsList = `@foreach($products as $p)<option value="{{ $p->id }}">{{ addslashes($p->name) }} ({{ $p->sku }})</option>@endforeach`;

    function addAdjustmentRow() {
        const tbody = document.querySelector('#adjItemsTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${adjIndex}][product_id]" class="form-select" required>
                    <option value="">Select product...</option>
                    ${productsList}
                </select>
            </td>
            <td>
                <input type="number" step="any" name="items[${adjIndex}][diff_qty]" class="form-control" placeholder="e.g. -5 or 10" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="ti ti-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        adjIndex++;
    }

    function removeRow(btn) {
        const tbody = document.querySelector('#adjItemsTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('At least one item is required.');
        }
    }
</script>
@endpush
@endsection
