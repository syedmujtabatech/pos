@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-adjustments text-warning me-2"></i>Stock Adjustments (اسٹاک درستگی)</h3>
        <p class="text-muted mb-0">Record damage write-offs, physical count reconciliations, and shortage adjustments with manager authorization (FR-118 - FR-121).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-warning text-dark">
            <i class="ti ti-plus me-1"></i>New Stock Adjustment
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Adj #</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Items Count</th>
                        <th>Requested By</th>
                        <th class="text-center">Approval Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr>
                            <td><strong class="text-warning font-monospace">{{ $adj->adjustment_number }}</strong></td>
                            <td>{{ $adj->created_at->format('d M, Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $adj->location->name ?? 'Main Godown' }}</span></td>
                            <td>
                                @if($adj->type === 'damage')
                                    <span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>Damaged / Expired</span>
                                @elseif($adj->type === 'increase')
                                    <span class="badge bg-success"><i class="ti ti-plus me-1"></i>Found Surplus (+)</span>
                                @else
                                    <span class="badge bg-secondary"><i class="ti ti-minus me-1"></i>Shortage (-)</span>
                                @endif
                            </td>
                            <td>{{ $adj->reason }}</td>
                            <td>
                                <div><strong>{{ $adj->items->count() }} items</strong></div>
                                <small class="text-muted">
                                    {{ $adj->items->take(2)->map(fn($i) => ($i->product->name ?? 'Item') . ' (' . ($i->difference_quantity > 0 ? '+'.$i->difference_quantity : $i->difference_quantity) . ')')->implode(', ') }}
                                </small>
                            </td>
                            <td>{{ $adj->requestedBy->name ?? 'Staff' }}</td>
                            <td class="text-center">
                                @if($adj->status === 'approved')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Approved & Applied</span>
                                    @if($adj->approvedBy)<div class="small text-muted">{{ $adj->approvedBy->name }}</div>@endif
                                @elseif($adj->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending Manager Approval</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-adjustments-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No stock adjustments recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($adjustments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $adjustments->links() }}
        </div>
    @endif
</div>
@endsection
