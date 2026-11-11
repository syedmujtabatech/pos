@extends('layouts.app')

@section('title', 'Sales Returns & Credit Notes')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-rotate-clockwise-2 text-danger me-2"></i>Sales Returns & Credit Notes (سیلز واپسی)</h3>
        <p class="text-muted mb-0">Record customer product returns, damaged stock segregation, and automatic Credit Note balance adjustments (FR-96, FR-97).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('sales.returns.create') }}" class="btn btn-danger">
            <i class="ti ti-plus me-1"></i>New Sales Return
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th>Original Invoice</th>
                        <th>Customer</th>
                        <th>Items Returned</th>
                        <th class="text-end">Return Value</th>
                        <th class="text-center">Credit Note #</th>
                        <th class="text-center">Stock Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                        <tr>
                            <td><strong class="text-danger">{{ $ret->return_number }}</strong></td>
                            <td>{{ $ret->return_date ? \Carbon\Carbon::parse($ret->return_date)->format('d M, Y') : $ret->created_at->format('d M, Y') }}</td>
                            <td>
                                @if($ret->invoice)
                                    <a href="{{ route('sales.show-invoice', $ret->invoice_id) }}" class="fw-semibold text-primary">
                                        {{ $ret->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">Direct Return</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $ret->customer->name ?? 'Walk-in' }}</strong><br>
                                <small class="text-muted">{{ $ret->customer->area->name ?? '' }}</small>
                            </td>
                            <td>
                                @foreach($ret->items as $item)
                                    <div><small>{{ $item->product->name ?? 'Item' }} ({{ $item->quantity }} {{ $item->unit_name }})</small></div>
                                @endforeach
                            </td>
                            <td class="text-end fw-bold text-danger">Rs. {{ number_format($ret->total_amount, 2) }}</td>
                            <td class="text-center">
                                @if($ret->creditNote)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                                        {{ $ret->creditNote->credit_note_number }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Cash Refunded</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $hasDamaged = $ret->items->contains('condition', 'damaged');
                                @endphp
                                @if($hasDamaged)
                                    <span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle me-1"></i>Damaged / Segregated</span>
                                @else
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Restocked (Saleable)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-archive-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No sales returns recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($returns->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $returns->links() }}
        </div>
    @endif
</div>
@endsection
