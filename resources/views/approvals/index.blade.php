@extends('layouts.app')

@section('title', 'Manager Approvals Queue')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-stamp text-primary me-2"></i>Manager Approvals Queue (منظوری کی درخواستیں)</h3>
        <p class="text-muted mb-0">Review pending bill voids, customer credit overrides, discount limits, and stock adjustments.</p>
    </div>
    <div class="col-md-6 text-end">
        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
            <i class="ti ti-lock me-1"></i>Manager / Super Admin Access
        </span>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Req #</th>
                        <th>Date & Time</th>
                        <th>Type of Exception</th>
                        <th>Requested By</th>
                        <th>Reference / Item</th>
                        <th>Reason / Request Note</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td><strong class="font-monospace text-primary">#{{ $req->id }}</strong></td>
                            <td>
                                <div>{{ $req->created_at->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $req->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $typeMap = [
                                        'void_invoice' => ['Invoice Void Request', 'bg-danger'],
                                        'credit_override' => ['Credit Limit Override', 'bg-warning text-dark'],
                                        'stock_adjustment' => ['Stock Adjustment Approval', 'bg-info'],
                                        'discount_override' => ['Discount Limit Override', 'bg-secondary'],
                                    ];
                                    $badge = $typeMap[$req->type] ?? [$req->type, 'bg-secondary'];
                                @endphp
                                <span class="badge {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </td>
                            <td>
                                <strong>{{ $req->requester->name ?? 'Staff' }}</strong><br>
                                <small class="text-muted">{{ $req->requester->role->name ?? '' }}</small>
                            </td>
                            <td>
                                <code>{{ $req->reference_type }}: #{{ $req->reference_id }}</code>
                            </td>
                            <td>{{ $req->reason ?? '-' }}</td>
                            <td class="text-center">
                                @if($req->status === 'approved')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Approved</span>
                                    @if($req->approver)<div class="small text-muted">{{ $req->approver->name }}</div>@endif
                                @elseif($req->status === 'rejected')
                                    <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Rejected</span>
                                    @if($req->rejection_reason)<div class="small text-danger">{{ $req->rejection_reason }}</div>@endif
                                @else
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending Review</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($req->status === 'pending')
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('approvals.decide', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Authorize and approve this exception?')">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-outline-success" title="Approve">
                                                <i class="ti ti-check"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Reject">
                                            <i class="ti ti-x"></i> Reject
                                        </button>
                                    </div>

                                    <!-- Reject Reason Modal -->
                                    <div class="modal fade text-start" id="rejectModal{{ $req->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('approvals.decide', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Reject Request #{{ $req->id }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label fw-bold">Rejection Reason</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Provide reason for rejecting this exception..." required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">Decided</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-stamp-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No pending approval requests in the queue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
