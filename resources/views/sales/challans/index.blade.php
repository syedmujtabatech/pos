@extends('layouts.app')

@section('title', 'Delivery Challans')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-truck-delivery text-primary me-2"></i>Delivery Challans (ڈلیوری چالان)</h3>
        <p class="text-muted mb-0">Warehouse dispatch notes, vehicle details, driver info and delivery confirmation (FR-94).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('sales.wholesale') }}" class="btn btn-primary">
            <i class="ti ti-receipt me-1"></i>View Invoices
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Challan #</th>
                        <th>Date</th>
                        <th>Order / Invoice #</th>
                        <th>Customer</th>
                        <th>Vehicle / Driver</th>
                        <th>Dispatch Location</th>
                        <th class="text-center">Delivery Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($challans as $challan)
                        <tr>
                            <td><strong class="text-primary">{{ $challan->challan_number }}</strong></td>
                            <td>{{ $challan->dispatch_date ? \Carbon\Carbon::parse($challan->dispatch_date)->format('d M, Y') : $challan->created_at->format('d M, Y') }}</td>
                            <td>
                                @if($challan->salesOrder)
                                    <code>{{ $challan->salesOrder->order_number }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $challan->customer->name ?? 'Direct Delivery' }}</strong><br>
                                <small class="text-muted">{{ $challan->customer->address ?? '' }}</small>
                            </td>
                            <td>
                                <div><i class="ti ti-truck text-muted me-1"></i>{{ $challan->vehicle_number ?? 'Suzuki Pick-up' }}</div>
                                <small class="text-muted"><i class="ti ti-user text-muted me-1"></i>Driver: {{ $challan->driver_name ?? 'Karachi Dispatch Team' }}</small>
                            </td>
                            <td>{{ $challan->location->name ?? 'Main Godown' }}</td>
                            <td class="text-center">
                                @if($challan->status === 'delivered')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>Delivered & Signed</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="ti ti-truck me-1"></i>In Transit</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-truck-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No delivery challans created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($challans->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $challans->links() }}
        </div>
    @endif
</div>
@endsection
