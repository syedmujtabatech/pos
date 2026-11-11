@extends('layouts.app')

@section('title', 'Customer Receipts & Udhaar Recovery')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-cash text-success me-2"></i>Customer Receipts & Recovery (ادھار وصولی)</h3>
        <p class="text-muted mb-0">Record cash, cheque, and bank collections from customers to clear outstanding ledger balances (FR-132 - FR-134).</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newReceiptModal">
            <i class="ti ti-plus me-1"></i>Collect Udhaar Payment
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Salesman (Collector)</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Amount Collected</th>
                        <th>Notes / Cheque Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $rcpt)
                        <tr>
                            <td><strong class="text-success font-monospace">{{ $rcpt->receipt_number }}</strong></td>
                            <td>{{ $rcpt->receipt_date ? \Carbon\Carbon::parse($rcpt->receipt_date)->format('d M, Y') : $rcpt->created_at->format('d M, Y') }}</td>
                            <td>
                                <strong>{{ $rcpt->customer->name ?? 'Customer' }}</strong><br>
                                <small class="text-muted">{{ $rcpt->customer->shop_name ?? '' }} ({{ $rcpt->customer->area->name ?? '' }})</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="ti ti-user me-1"></i>{{ $rcpt->salesman->name ?? 'Direct' }}
                                </span>
                            </td>
                            <td>
                                @if($rcpt->payment_method === 'cash')
                                    <span class="badge bg-success"><i class="ti ti-cash me-1"></i>Cash</span>
                                @elseif($rcpt->payment_method === 'cheque')
                                    <span class="badge bg-warning text-dark"><i class="ti ti-credit-card me-1"></i>Cheque</span>
                                @else
                                    <span class="badge bg-info"><i class="ti ti-building-bank me-1"></i>Online Bank</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                Rs. {{ number_format($rcpt->amount, 2) }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $rcpt->notes ?? '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-cash-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No customer receipts found. Collect an udhaar payment above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($receipts->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $receipts->links() }}
        </div>
    @endif
</div>

<!-- Modal: New Customer Receipt -->
<div class="modal fade" id="newReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('finance.receipts.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-cash me-2"></i>Collect Udhaar Payment (وصولی اندراج)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Receipt Date (تاریخ وصولی) <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Customer (گاہک) <span class="text-danger">*</span></label>
                            <select name="customer_id" id="rcptCustomer" class="form-select" required onchange="updateCustomerBalance(this)">
                                <option value="">Select customer...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-balance="{{ $c->current_balance }}" data-salesman="{{ $c->assigned_salesman_id }}" {{ (old('customer_id', request('customer_id')) == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->shop_name }}) - Udhaar: Rs. {{ number_format($c->current_balance, 0) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Salesman / Recovery Officer</label>
                            <select name="salesman_id" id="rcptSalesman" class="form-select">
                                <option value="">Direct Office / Counter</option>
                                @foreach($salesmen as $sm)
                                    <option value="{{ $sm->id }}" {{ old('salesman_id') == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount to Collect (وصول رقم PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="any" min="1" name="amount" id="rcptAmount" value="{{ old('amount') }}" class="form-control fw-bold fs-5 text-success" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payMethod" class="form-select" required onchange="togglePayFields(this.value)">
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash (کیش / روکڑ)</option>
                                <option value="bank" {{ old('payment_method') === 'bank' || old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Online Bank Transfer (آن لائن ٹرانسفر)</option>
                                <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque (بینک چیک)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="bankRefField" style="display: none;">
                            <label class="form-label fw-bold">Bank Name / Trx Ref #</label>
                            <input type="text" name="reference_no" id="rcptBankRef" value="{{ old('reference_no') }}" class="form-control" placeholder="e.g. Meezan Bank / Trx-10928">
                        </div>

                        <!-- Cheque Details (Conditional) -->
                        <div id="chequeFields" class="row g-3 mt-1 d-none">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Cheque Number (چیک نمبر)</label>
                                <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}" placeholder="e.g. CHQ-981245">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Bank Name (بینک کا نام)</label>
                                <input type="text" name="cheque_bank" class="form-control" value="{{ old('cheque_bank') }}" placeholder="e.g. Meezan Bank, HBL">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Cheque Due Date (تاریخ ادائیگی)</label>
                                <input type="date" name="cheque_date" class="form-control" value="{{ old('cheque_date', date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Notes / Description</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="e.g. Partial recovery for previous week bills">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="ti ti-check me-1"></i>Save Receipt & Update Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateCustomerBalance(sel) {
        if (!sel || !sel.options) return;
        const opt = sel.options[sel.selectedIndex];
        if (!opt) return;
        const smId = opt.getAttribute('data-salesman');
        if (smId) {
            const smField = document.getElementById('rcptSalesman');
            if (smField) smField.value = smId;
        }
        const bal = opt.getAttribute('data-balance');
        const amtField = document.getElementById('rcptAmount');
        if (bal && amtField && (!amtField.value || amtField.value == '0' || amtField.value == '')) {
            amtField.value = bal;
        }
    }

    function togglePayFields(method) {
        const chqBox = document.getElementById('chequeFields');
        const bnkBox = document.getElementById('bankRefField');
        if (method === 'cheque') {
            if (chqBox) chqBox.classList.remove('d-none');
            if (bnkBox) bnkBox.style.display = 'none';
        } else if (method === 'bank' || method === 'bank_transfer') {
            if (chqBox) chqBox.classList.add('d-none');
            if (bnkBox) bnkBox.style.display = 'block';
        } else {
            if (chqBox) chqBox.classList.add('d-none');
            if (bnkBox) bnkBox.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const paySel = document.getElementById('payMethod');
        if (paySel) togglePayFields(paySel.value);

        const custSel = document.getElementById('rcptCustomer');
        if (custSel && custSel.value) {
            updateCustomerBalance(custSel);
        }

        @if($errors->any() || request('customer_id'))
        var modalEl = document.getElementById('newReceiptModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
        @endif
    });
</script>
@endpush
@endsection
