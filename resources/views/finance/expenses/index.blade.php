@extends('layouts.app')

@section('title', 'Daily Business Expenses')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-receipt-2 text-danger me-2"></i>Expenses & Overhead (کاروباری اخراجات)</h3>
        <p class="text-muted mb-0">Record shop rent, electricity, godown freight, staff tea, and daily operations overhead (FR-143 - FR-145).</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="ti ti-plus me-1"></i>Record New Expense
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Expense #</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Payee / Paid To</th>
                        <th>Description</th>
                        <th>Method</th>
                        <th class="text-end">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumExpense = 0; @endphp
                    @forelse($expenses as $exp)
                        @php $sumExpense += $exp->amount; @endphp
                        <tr>
                            <td><strong class="text-danger font-monospace">{{ $exp->expense_number }}</strong></td>
                            <td>{{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d M, Y') : $exp->created_at->format('d M, Y') }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary fw-semibold">{{ $exp->category->name ?? 'General' }}</span></td>
                            <td>{{ $exp->location->name ?? 'Retail Shop' }}</td>
                            <td><strong>{{ $exp->payee_name ?? '-' }}</strong></td>
                            <td>{{ $exp->description ?? '-' }}</td>
                            <td>
                                @if($exp->payment_method === 'cash')
                                    <span class="badge bg-success-subtle text-success"><i class="ti ti-cash me-1"></i>Cash</span>
                                @else
                                    <span class="badge bg-info-subtle text-info"><i class="ti ti-building-bank me-1"></i>Bank</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger fs-6">
                                Rs. {{ number_format($exp->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-receipt-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No expenses recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($expenses->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Modal: New Expense -->
<div class="modal fade" id="newExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('finance.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-receipt-2 me-2"></i>Record New Expense (خرچ کا اندراج)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Category <span class="text-danger">*</span></label>
                            <select name="expense_category_id" class="form-select" required>
                                <option value="">Select category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cost Center / Location <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select" required>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" step="any" min="1" name="amount" class="form-control fw-bold fs-5 text-danger" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash (کیش بک سے)</option>
                                <option value="bank_transfer">Bank Account (بینک اکاؤنٹ سے)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Paid To / Payee Name</label>
                            <input type="text" name="payee_name" class="form-control" placeholder="e.g. K-Electric, Chotu Tea Stall, Transport Guy">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Description / Purpose</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. October monthly electricity bill payment">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="ti ti-check me-1"></i>Save Expense & Deduct Cash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
