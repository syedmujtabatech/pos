@extends('layouts.app')

@section('title', 'Sales Quotations')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-file-certificate text-primary me-2"></i>Sales Quotations (قیمت کا تخمینہ)</h3>
        <p class="text-muted mb-0">Prepare and track official customer wholesale quotations with validity terms.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('sales.wholesale.create') }}" class="btn btn-outline-primary me-2">
            <i class="ti ti-plus me-1"></i>New Quotation
        </a>
        <a href="{{ route('sales.wholesale') }}" class="btn btn-primary">
            <i class="ti ti-receipt me-1"></i>Wholesale Invoices
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Quote #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Salesman</th>
                        <th>Valid Until</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quote)
                        <tr>
                            <td><strong class="text-primary">{{ $quote->quotation_number }}</strong></td>
                            <td>{{ $quote->created_at->format('d M, Y') }}</td>
                            <td>
                                <strong>{{ $quote->customer->name ?? 'Walk-in' }}</strong><br>
                                <small class="text-muted">{{ $quote->customer->shop_name ?? '' }}</small>
                            </td>
                            <td>{{ $quote->salesman->name ?? 'Direct' }}</td>
                            <td>
                                @if($quote->valid_until)
                                    <span class="badge bg-secondary-subtle text-secondary">{{ \Carbon\Carbon::parse($quote->valid_until)->format('d M, Y') }}</span>
                                @else
                                    <span class="text-muted">7 Days</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">Rs. {{ number_format($quote->grand_total, 2) }}</td>
                            <td class="text-center">
                                @if($quote->status === 'converted')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Converted to Bill</span>
                                @elseif($quote->status === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft / Active</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('sales.wholesale.create') }}" class="btn btn-sm btn-outline-success" title="Convert to Wholesale Invoice">
                                    <i class="ti ti-arrow-right"></i> Bill
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-file-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No sales quotations created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($quotations->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $quotations->links() }}
        </div>
    @endif
</div>
@endsection
