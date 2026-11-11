@extends('layouts.app')

@section('title', 'Process Sales Return')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-rotate-clockwise-2 text-danger me-2"></i>New Sales Return (مال واپسی)</h3>
        <p class="text-muted mb-0">Select invoice, specify returned quantities and condition (Saleable vs Damaged).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('sales.returns') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Returns
        </a>
    </div>
</div>

@if(!$invoice)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4 text-center">
            <h5 class="fw-bold mb-3">Lookup Original Invoice</h5>
            <p class="text-muted">Enter the Invoice Number (e.g., <code>INV-20261010-0001</code>) to load sold items.</p>
            <form action="{{ route('sales.returns.create') }}" method="GET" class="d-inline-flex justify-content-center w-50">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                    <input type="text" name="invoice_number" class="form-control" placeholder="Enter invoice number..." required autofocus>
                    <button type="submit" class="btn btn-primary px-4">Find Bill</button>
                </div>
            </form>
        </div>
    </div>
@else
    <form action="{{ route('sales.returns.store') }}" method="POST">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><i class="ti ti-file-invoice text-primary me-2"></i>Invoice Details: {{ $invoice->invoice_number }}</span>
                <a href="{{ route('sales.returns.create') }}" class="btn btn-sm btn-outline-secondary">Search Different Bill</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Customer</small>
                        <strong class="fs-6">{{ $invoice->customer->name ?? 'Walk-in Retail' }}</strong>
                        @if($invoice->customer)
                            <div class="small text-muted">{{ $invoice->customer->shop_name }} | Bal: Rs. {{ number_format($invoice->customer->current_balance, 2) }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Invoice Date</small>
                        <strong>{{ $invoice->invoice_date->format('d M, Y h:i A') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Salesman / Counter</small>
                        <strong>{{ $invoice->salesman->name ?? 'Direct Counter' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total Billed</small>
                        <strong class="text-primary fs-5">Rs. {{ number_format($invoice->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="ti ti-box-seam me-2 text-danger"></i>Select Return Items</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th class="text-center">Sold Qty</th>
                                <th class="text-end">Sold Rate</th>
                                <th style="width: 140px;" class="text-center">Return Qty</th>
                                <th style="width: 180px;">Condition</th>
                                <th class="text-end">Return Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $idx => $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name ?? 'Product' }}</strong>
                                        <div class="small text-muted">Batch: {{ $item->batch_number ?? '-' }} | SKU: {{ $item->product->sku ?? '-' }}</div>
                                        <input type="hidden" name="items[{{ $idx }}][invoice_item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $item->quantity }} {{ $item->unit_name }}</span>
                                    </td>
                                    <td class="text-end">
                                        Rs. {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="any" min="0" max="{{ $item->quantity }}" name="items[{{ $idx }}][return_qty]" value="0" class="form-control form-control-sm text-center return-qty" data-price="{{ $item->unit_price }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $idx }}][condition]" class="form-select form-select-sm">
                                            <option value="saleable">Saleable (Restock)</option>
                                            <option value="damaged">Damaged (Faulty/Segregate)</option>
                                        </select>
                                    </td>
                                    <td class="text-end fw-bold return-item-total">
                                        Rs. 0.00
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">Total Return Credit Note Value:</th>
                                <th class="text-end fw-bold fs-5 text-danger" id="totalReturnAmount">Rs. 0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Reason for Return (واپسی کی وجہ) <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g., Customer over-ordered, expired batch, or carton damaged in delivery" required>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-danger btn-lg px-4">
                            <i class="ti ti-check me-1"></i>Confirm Return & Issue Credit Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.querySelectorAll('.return-qty').forEach(input => {
            input.addEventListener('input', function() {
                const tr = this.closest('tr');
                const qty = parseFloat(this.value) || 0;
                const price = parseFloat(this.dataset.price) || 0;
                const total = qty * price;
                tr.querySelector('.return-item-total').textContent = 'Rs. ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                let grand = 0;
                document.querySelectorAll('.return-qty').forEach(q => {
                    const qVal = parseFloat(q.value) || 0;
                    const pVal = parseFloat(q.dataset.price) || 0;
                    grand += (qVal * pVal);
                });
                document.getElementById('totalReturnAmount').textContent = 'Rs. ' + grand.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        });
    </script>
    @endpush
@endif
@endsection
