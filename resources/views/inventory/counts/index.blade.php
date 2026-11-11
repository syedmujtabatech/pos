@extends('layouts.app')

@section('title', 'Stock Taking & Physical Audits')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-clipboard-check text-primary me-2"></i>Stock Taking & Audits (اسٹاک پڑتال)</h3>
        <p class="text-muted mb-0">Physical stock count comparison against system stock, variance calculation, and discrepancy reconciliation (FR-122 - FR-125).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.counts.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Start Physical Count
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Audit #</th>
                        <th>Date</th>
                        <th>Location Audited</th>
                        <th>Auditor / Counted By</th>
                        <th>Items Counted</th>
                        <th class="text-center">Total Variance</th>
                        <th class="text-center">Audit Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($counts as $c)
                        @php
                            $totalVariance = $c->items->sum('variance');
                        @endphp
                        <tr>
                            <td><strong class="text-primary font-monospace">{{ $c->count_number }}</strong></td>
                            <td>{{ $c->count_date ? \Carbon\Carbon::parse($c->count_date)->format('d M, Y') : $c->created_at->format('d M, Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $c->location->name ?? 'Main Godown' }}</span></td>
                            <td>{{ $c->conductedBy->name ?? 'Storekeeper' }}</td>
                            <td><strong>{{ $c->items->count() }} Products</strong></td>
                            <td class="text-center">
                                @if($totalVariance == 0)
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Zero Variance (Matched)</span>
                                @elseif($totalVariance < 0)
                                    <span class="badge bg-danger">{{ number_format($totalVariance, 0) }} Pcs Shortage</span>
                                @else
                                    <span class="badge bg-info">+{{ number_format($totalVariance, 0) }} Pcs Surplus</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($c->status === 'completed' || $c->status === 'approved')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Reconciled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft / In Progress</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-checklist fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No physical stock audits conducted yet. Start a new stock take to verify inventory accuracy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($counts->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $counts->links() }}
        </div>
    @endif
</div>
@endsection
