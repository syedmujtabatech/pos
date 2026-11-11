@extends('layouts.app')

@section('title', 'Godown to Shop Transfers')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-arrows-transfer-down text-primary me-2"></i>Stock Transfers (مال کی منتقلی)</h3>
        <p class="text-muted mb-0">Track inter-branch and godown-to-shop stock dispatches, transit status, and receipt confirmations (FR-115 to FR-117).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('inventory.transfers.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>New Stock Transfer
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Transfer #</th>
                        <th>Date</th>
                        <th>Source Location</th>
                        <th>Destination Location</th>
                        <th>Items Count</th>
                        <th>Dispatched By</th>
                        <th class="text-center">Transit Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                        <tr>
                            <td><strong class="text-primary font-monospace">{{ $t->transfer_number }}</strong></td>
                            <td>{{ $t->transfer_date ? \Carbon\Carbon::parse($t->transfer_date)->format('d M, Y') : $t->created_at->format('d M, Y') }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><i class="ti ti-building me-1"></i>{{ $t->fromLocation->name ?? 'Main Godown' }}</span></td>
                            <td><span class="badge bg-primary-subtle text-primary"><i class="ti ti-building-store me-1"></i>{{ $t->toLocation->name ?? 'Retail Shop' }}</span></td>
                            <td>
                                <div><strong>{{ $t->items->count() }} Items</strong></div>
                                <small class="text-muted">
                                    {{ $t->items->take(2)->map(fn($i) => ($i->product->name ?? 'Item') . ' (' . $i->quantity . ')')->implode(', ') }}
                                    @if($t->items->count() > 2)...@endif
                                </small>
                            </td>
                            <td>{{ $t->dispatchedBy->name ?? 'Staff' }}</td>
                            <td class="text-center">
                                @if($t->status === 'received')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Received by {{ $t->receivedBy->name ?? 'Shopkeeper' }}</span>
                                @elseif($t->status === 'in_transit')
                                    <span class="badge bg-warning text-dark"><i class="ti ti-truck me-1"></i>In Transit</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($t->status === 'in_transit')
                                    <form action="{{ route('inventory.transfers.receive', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm receipt of these items at destination?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ti ti-package-import me-1"></i>Receive Stock
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-arrows-transfer-down fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No stock transfers found. Create one from Main Godown to Retail Shop.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transfers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
@endsection
