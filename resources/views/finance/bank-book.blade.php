@extends('layouts.app')

@section('title', 'Bank Book')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-building-bank text-info me-2"></i>Bank Book (بینک روزنامچہ)</h3>
        <p class="text-muted mb-0">Record and track commercial bank accounts, deposits, cleared cheques, and online IBFT payments (FR-147).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('finance.cheques') }}" class="btn btn-outline-warning me-2">
            <i class="ti ti-credit-card me-1"></i>Cheque Tracker
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="ti ti-printer me-1"></i>Print Bank Book
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.bank-book') }}" class="row g-3 align-items-end">
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

<!-- Bank Book Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Bank / Account</th>
                        <th>Reference / Cheque #</th>
                        <th>Description</th>
                        <th class="text-center text-success">Deposits / Credits (+)</th>
                        <th class="text-center text-danger">Withdrawals / Debits (-)</th>
                        <th class="text-end">Balance After</th>
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
                                <strong>{{ $entry->bank_name ?? 'Meezan Bank - Main Branch' }}</strong><br>
                                <small class="text-muted">A/C: {{ $entry->account_number ?? '0101-0102938475' }}</small>
                            </td>
                            <td>
                                <strong class="text-primary font-monospace">{{ $entry->reference_number ?? ($entry->reference_type.': #'.$entry->reference_id) }}</strong>
                            </td>
                            <td>{{ $entry->description ?? 'Bank Transaction' }}</td>
                            <td class="text-center fw-bold text-success">
                                {{ $entry->deposit > 0 ? '+Rs. ' . number_format($entry->deposit, 2) : '-' }}
                            </td>
                            <td class="text-center fw-bold text-danger">
                                {{ $entry->withdrawal > 0 ? '-Rs. ' . number_format($entry->withdrawal, 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold fs-6">
                                Rs. {{ number_format($entry->balance_after, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-building-bank fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No bank transactions recorded in this period.
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
