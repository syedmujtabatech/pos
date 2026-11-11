@extends('layouts.app')

@section('title', 'Cheque Tracking & Management')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-credit-card text-warning me-2"></i>Cheque Tracking & Clearance (چیک مینجمنٹ)</h3>
        <p class="text-muted mb-0">Track received and issued cheques, maturity dates, bank clearance, and automatic ledger bounce reversals (FR-135 - FR-137).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('finance.receipts') }}" class="btn btn-outline-success">
            <i class="ti ti-cash me-1"></i>Customer Receipts
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cheque #</th>
                        <th>Type</th>
                        <th>Bank Name</th>
                        <th>Customer / Supplier</th>
                        <th>Cheque Date</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cheques as $chq)
                        <tr>
                            <td><strong class="font-monospace text-primary fs-6">{{ $chq->cheque_number }}</strong></td>
                            <td>
                                @if($chq->type === 'received')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ti ti-arrow-down-left me-1"></i>Received (Customer)</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-arrow-up-right me-1"></i>Issued (Supplier)</span>
                                @endif
                            </td>
                            <td>{{ $chq->bank_name }}</td>
                            <td>
                                @if($chq->customer)
                                    <strong>{{ $chq->customer->name }}</strong><br><small class="text-muted">{{ $chq->customer->shop_name }}</small>
                                @elseif($chq->supplier)
                                    <strong>{{ $chq->supplier->name }}</strong><br><small class="text-muted">{{ $chq->supplier->company_name }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $chq->cheque_date ? \Carbon\Carbon::parse($chq->cheque_date)->format('d M, Y') : '-' }}</td>
                            <td class="text-end fw-bold fs-6">
                                Rs. {{ number_format($chq->amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($chq->status === 'cleared')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Cleared</span>
                                @elseif($chq->status === 'bounced')
                                    <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Bounced / Dishonored</span>
                                    @if($chq->bounce_reason)<br><small class="text-danger">{{ $chq->bounce_reason }}</small>@endif
                                @else
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending Deposit</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($chq->status === 'pending')
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('finance.cheques.clear', $chq->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm cheque deposit and clearance in bank?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Mark Cleared">
                                                <i class="ti ti-check"></i> Clear
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bounceModal{{ $chq->id }}" title="Mark Bounced">
                                            <i class="ti ti-alert-triangle"></i> Bounce
                                        </button>
                                    </div>

                                    <!-- Bounce Modal -->
                                    <div class="modal fade text-start" id="bounceModal{{ $chq->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('finance.cheques.bounce', $chq->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="ti ti-alert-triangle me-2"></i>Bounce Cheque #{{ $chq->cheque_number }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning small">
                                                            <i class="ti ti-info-circle me-1"></i>
                                                            Bouncing this cheque will automatically restore customer's outstanding balance in the ledger.
                                                        </div>
                                                        <label class="form-label fw-bold">Bounce Reason (وجہ) <span class="text-danger">*</span></label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Insufficient funds, signature mismatch, post-dated memo" required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Dishonor & Restore Udhaar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">No action needed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-credit-card-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No cheques recorded in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cheques->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $cheques->links() }}
        </div>
    @endif
</div>
@endsection
