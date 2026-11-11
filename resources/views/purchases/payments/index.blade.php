@extends('layouts.app')
@section('title', 'Supplier Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-cash-banknote text-success me-1"></i> Supplier Payments (سپلائر کی ادائیگی)</h4>
    <p class="text-secondary small mb-0">Record cash, bank transfer, and cheque payments with automatic invoice allocations (FR-74, FR-75)</p>
  </div>
  <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
    <i class="ti ti-plus me-1"></i> Make Payment
  </button>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Payment #</th>
          <th>Supplier Agency</th>
          <th>Payment Date</th>
          <th>Method</th>
          <th>Ref / Cheque #</th>
          <th class="text-end">Amount Paid</th>
          <th>Recorded By</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $pay)
          <tr>
            <td class="fw-bold font-monospace">{{ $pay->payment_number }}</td>
            <td>
              <div class="fw-semibold">{{ $pay->supplier->name }}</div>
              <small class="text-muted">{{ $pay->notes }}</small>
            </td>
            <td>{{ $pay->payment_date->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $pay->payment_method === 'cash' ? 'success' : ($pay->payment_method === 'bank' ? 'primary' : 'warning') }}-subtle text-{{ $pay->payment_method === 'cash' ? 'success' : ($pay->payment_method === 'bank' ? 'primary' : 'warning') }} text-uppercase">
                {{ $pay->payment_method }}
              </span>
            </td>
            <td><code>{{ $pay->reference_no ?? ($pay->cheque_no ?? '-') }}</code></td>
            <td class="text-end fw-bold text-success fs-6">Rs. {{ number_format($pay->amount, 2) }}</td>
            <td><small class="text-muted">{{ $pay->createdBy->name }}</small></td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-5">No supplier payments recorded yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($payments->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $payments->links() }}
    </div>
  @endif
</div>

<!-- Make Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('purchases.payments.store') }}" method="POST">
        @csrf
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="ti ti-cash me-1"></i> Record Supplier Payment</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Supplier Agency <span class="text-danger">*</span></label>
            <select name="supplier_id" class="form-select" required>
              <option value="">-- Choose Supplier --</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }} (Payable: Rs. {{ number_format($s->current_balance) }})</option>
              @endforeach
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select" required>
                <option value="cash" selected>Cash (روکڑ)</option>
                <option value="bank">Bank Online / Transfer</option>
                <option value="cheque">Cheque (بینک چیک)</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Amount Paid (ادا کی گئی رقم) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rs.</span>
              <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold" placeholder="0.00" required>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Bank Ref / Online Slip</label>
              <input type="text" name="reference_no" class="form-control" placeholder="e.g. HBL-9921">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Cheque # (If applicable)</label>
              <input type="text" name="cheque_no" class="form-control" placeholder="e.g. CHQ-1002">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Remarks / Payment Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Part payment against monthly supply bill"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success fw-semibold">Save & Deduct Payable</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
