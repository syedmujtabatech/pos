@extends('layouts.app')

@section('title', 'Daily Cash Book')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-wallet text-success me-2"></i>Daily Cash Book (روزنامچہ کیش بک)</h3>
        <p class="text-muted mb-0">Real-time ledger of all physical cash inflows (POS, wholesale, recovery) and outflows (expenses, supplier payments) (FR-146).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('finance.day-closing') }}" class="btn btn-primary me-2">
            <i class="ti ti-lock me-1"></i>Day-End Closing
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="ti ti-printer me-1"></i>Print Cash Book
        </button>
    </div>
</div>

<!-- Summary KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Cash Inflow (+)</small>
                <div class="fs-4 fw-bold text-success mt-1">Rs. {{ number_format($totalInflow, 2) }}</div>
                <small class="text-muted">Sales receipts, POS cash, udhaar recoveries</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <small class="text-muted fw-bold text-uppercase">Total Cash Outflow (-)</small>
                <div class="fs-4 fw-bold text-danger mt-1">Rs. {{ number_format($totalOutflow, 2) }}</div>
                <small class="text-muted">Shop expenses, supplier cash payouts</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4 bg-primary-subtle">
            <div class="card-body">
                <small class="text-primary fw-bold text-uppercase">Current Cash in Hand</small>
                <div class="fs-4 fw-bold text-primary mt-1">Rs. {{ number_format($currentBalance, 2) }}</div>
                <small class="text-muted">Physical drawer cash balance</small>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.cash-book') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold">From Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold">To Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Cash Book Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference / Source</th>
                        <th>Description / Narration</th>
                        <th class="text-center">Cash In (+ Debit)</th>
                        <th class="text-center">Cash Out (- Credit)</th>
                        <th class="text-end">Balance After</th>
                        <th>Entered By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td>
                                <div>{{ $entry->entry_date ? \Carbon\Carbon::parse($entry->entry_date)->format('d M, Y') : $entry->created_at->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $entry->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong class="text-primary font-monospace">{{ $entry->reference_type }}: #{{ $entry->reference_id }}</strong>
                            </td>
                            <td>{{ $entry->description ?? 'Cash Transaction' }}</td>
                            <td class="text-center fw-bold text-success">
                                {{ $entry->inflow > 0 ? '+Rs. ' . number_format($entry->inflow, 2) : '-' }}
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ $entry->outflow > 0 ? '-Rs. ' . number_format($entry->outflow, 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold fs-6">
                                Rs. {{ number_format($entry->balance_after, 2) }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $entry->creator->name ?? 'System' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-wallet-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No cash transactions found in selected date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($entries, 'hasPages') && $entries->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $entries->links() }}
        </div>
    @endif
</div>
@endsection
