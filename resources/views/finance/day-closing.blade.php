@extends('layouts.app')

@section('title', 'Day-End Closing & Counter Reconciliation')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-lock text-primary me-2"></i>Day-End Closing (یومیہ حساب بندی)</h3>
        <p class="text-muted mb-0">Reconcile cash drawer, lock daily business books, and archive financial summary (FR-149, FR-150).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('finance.cash-book') }}" class="btn btn-outline-secondary me-2">
            <i class="ti ti-wallet me-1"></i>Cash Book
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="ti ti-printer me-1"></i>Print Closing Sheet
        </button>
    </div>
</div>

@if($isClosedToday)
    <div class="alert alert-success d-flex align-items-center mb-4">
        <i class="ti ti-circle-check fs-2 me-3"></i>
        <div>
            <h5 class="alert-heading mb-1 fw-bold">Day Already Closed for Today ({{ \Carbon\Carbon::parse($today)->format('d M, Y') }})</h5>
            <span>Today's financial transactions have been balanced and securely locked. No further modifications are allowed without Manager authorization.</span>
        </div>
    </div>
@endif

<!-- Daily Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Sales Today</small>
                <div class="fs-4 fw-bold text-primary mt-1">Rs. {{ number_format($todaySales, 2) }}</div>
                <small class="text-muted">Wholesale + POS Bills</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Cash & Recoveries</small>
                <div class="fs-4 fw-bold text-success mt-1">Rs. {{ number_format($todayReceipts, 2) }}</div>
                <small class="text-muted">Udhaar Recovery Collected</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Expenses</small>
                <div class="fs-4 fw-bold text-danger mt-1">Rs. {{ number_format($todayExpenses, 2) }}</div>
                <small class="text-muted">Operating overhead paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 border-start border-warning border-4 bg-warning-subtle">
            <div class="card-body">
                <small class="text-dark fw-bold text-uppercase">Supplier Payments</small>
                <div class="fs-4 fw-bold text-dark mt-1">Rs. {{ number_format($todayPayments, 2) }}</div>
                <small class="text-muted">Paid to Distributors</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Active Closing Form -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="ti ti-cash-banknote text-success me-2"></i>Drawer Cash Verification: {{ \Carbon\Carbon::parse($today)->format('d M, Y') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-4">
                    <tr>
                        <th class="bg-light" style="width: 50%;">Location / Counter:</th>
                        <td><strong>{{ $shop->name ?? 'Retail Shop' }}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">System Calculated Cash:</th>
                        <td class="fs-5 fw-bold text-primary">Rs. {{ number_format($cashInHand, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Status:</th>
                        <td>
                            @if($isClosedToday)
                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>Day Closed & Locked</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Open for Business</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if(!$isClosedToday)
                    <form action="{{ route('finance.day-closing.store') }}" method="POST" onsubmit="return confirm('Are you sure you want to close the day? This will record the closing snapshot.')">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Closing Notes / Physical Cash Count Observation</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Physical drawer counted Rs. {{ number_format($cashInHand) }}, exactly matched system. Deposited in safe."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="ti ti-lock me-1"></i>Confirm & Close Day End (حساب بند کریں)
                        </button>
                    </form>
                @else
                    <div class="text-muted text-center py-3">
                        <i class="ti ti-shield-check text-success fs-1 d-block mb-2"></i>
                        The books are securely locked for today. Next closing will be available tomorrow.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Denominations Helper -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="ti ti-calculator text-primary me-2"></i>Currency Denominations Counter (کرنسی نوٹ گنتی)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="denominationTable">
                        <thead class="table-light">
                            <tr>
                                <th>Note (PKR)</th>
                                <th style="width: 120px;" class="text-center">Note Count</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([5000, 1000, 500, 100, 50, 20, 10] as $note)
                                <tr>
                                    <td><strong>Rs. {{ $note }}</strong></td>
                                    <td class="text-center">
                                        <input type="number" min="0" value="0" class="form-control form-control-sm text-center note-count" data-val="{{ $note }}" oninput="calcDenominations()">
                                    </td>
                                    <td class="text-end fw-bold note-total">Rs. 0</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total Cash Counted:</th>
                                <th colspan="2" class="text-end fs-5 fw-bold text-success" id="grandDenomTotal">Rs. 0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Past Day Closings Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="ti ti-history me-2"></i>Recent Day Closings History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Closing Date</th>
                        <th>Location</th>
                        <th class="text-end">Sales</th>
                        <th class="text-end">Receipts</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-end">Cash in Hand</th>
                        <th>Closed By</th>
                        <th>Closed Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($previousClosings as $pc)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($pc->closing_date)->format('d M, Y') }}</strong></td>
                            <td>{{ $pc->location->name ?? 'Retail Shop' }}</td>
                            <td class="text-end">Rs. {{ number_format($pc->total_sales, 2) }}</td>
                            <td class="text-end text-success">Rs. {{ number_format($pc->total_receipts, 2) }}</td>
                            <td class="text-end text-danger">Rs. {{ number_format($pc->total_expenses, 2) }}</td>
                            <td class="text-end fw-bold text-primary">Rs. {{ number_format($pc->cash_in_hand, 2) }}</td>
                            <td>{{ $pc->closedBy->name ?? 'Manager' }}</td>
                            <td><small class="text-muted">{{ $pc->closed_at ? \Carbon\Carbon::parse($pc->closed_at)->format('h:i A') : '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No past closings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function calcDenominations() {
        let total = 0;
        document.querySelectorAll('.note-count').forEach(input => {
            const count = parseInt(input.value) || 0;
            const noteVal = parseInt(input.dataset.val) || 0;
            const sub = count * noteVal;
            total += sub;
            input.closest('tr').querySelector('.note-total').textContent = 'Rs. ' + sub.toLocaleString();
        });
        document.getElementById('grandDenomTotal').textContent = 'Rs. ' + total.toLocaleString();
    }
</script>
@endpush
@endsection
