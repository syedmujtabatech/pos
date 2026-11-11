@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-clipboard-list text-primary me-2"></i>Sales Orders (سیلز آرڈرز)</h3>
        <p class="text-muted mb-0">Track field salesman orders, stock reservations, and challan dispatches (FR-92 to FR-95).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('sales.wholesale.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>New Order / Invoice
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Salesman (Order Booker)</th>
                        <th>Location</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-center">Delivery Status</th>
                        <th class="text-center">Invoice Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong class="text-primary">{{ $order->order_number }}</strong></td>
                            <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M, Y') : $order->created_at->format('d M, Y') }}</td>
                            <td>
                                <strong>{{ $order->customer->name ?? 'Walk-in' }}</strong><br>
                                <small class="text-muted">{{ $order->customer->area->name ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <i class="ti ti-user me-1"></i>{{ $order->salesman->name ?? 'Direct' }}
                                </span>
                            </td>
                            <td>{{ $order->location->name ?? 'Main Godown' }}</td>
                            <td class="text-end fw-bold">Rs. {{ number_format($order->grand_total, 2) }}</td>
                            <td class="text-center">
                                @if($order->delivery_status === 'delivered')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Delivered</span>
                                @elseif($order->delivery_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial Dispatch</span>
                                @else
                                    <span class="badge bg-secondary">Pending Dispatch</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($order->status === 'invoiced')
                                    <span class="badge bg-primary">Invoiced</span>
                                @else
                                    <span class="badge bg-outline-secondary">Open</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No sales orders found. Field salesman orders will be listed here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
