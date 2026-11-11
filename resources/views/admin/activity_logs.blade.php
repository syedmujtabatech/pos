@extends('layouts.app')

@section('title', 'Activity Audit Logs')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-history-toggle text-primary me-2"></i>System Activity Logs (سسٹم آڈٹ لاگز)</h3>
        <p class="text-muted mb-0">Immutable tracking of all staff actions, records created, updated, voided, and financial operations.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.login-history') }}" class="btn btn-outline-primary">
            <i class="ti ti-shield-lock me-1"></i>View Login History
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-activity fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Today's Operations</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($todayCount) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-alert-triangle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Sensitive Actions Today</span>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($sensitiveCount) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-users fs-3"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Active Staff Today</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($activeUsersToday) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.activity-logs') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Filter by Staff User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- All Staff Users --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->username }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Action Type</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">-- All Actions --</option>
                    @foreach($actionTypes as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $act)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Notes, IP..." class="form-control form-control-sm">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter">
                    <i class="ti ti-filter"></i>
                </button>
                <a href="{{ route('admin.activity-logs') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 170px;">Date & Time</th>
                        <th>Staff Member</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Description / Details</th>
                        <th>IP Address</th>
                        <th class="text-end" style="width: 90px;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $log->created_at->format('d M Y, h:i A') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 11px;">
                                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $log->user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1">{{ $log->user->role->name ?? 'Staff' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">System / Anonymous</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionColor = match($log->action) {
                                        'login' => 'info',
                                        'logout' => 'secondary',
                                        'create', 'create_user', 'create_role' => 'success',
                                        'update', 'update_role', 'update_settings' => 'primary',
                                        'void', 'delete' => 'danger',
                                        'bounce_cheque' => 'danger',
                                        'toggle_user_status', 'reset_password' => 'warning',
                                        'day_closing' => 'dark',
                                        default => 'primary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $actionColor }}-subtle text-{{ $actionColor }} border border-{{ $actionColor }}-subtle">
                                    {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->model_type)
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                                        {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $log->notes ?? 'Action executed' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($log->new_values || $log->old_values)
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}" title="View Snapshot">
                                        <i class="ti ti-code"></i> Data
                                    </button>

                                    <!-- Details Modal -->
                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">
                                                        <i class="ti ti-file-analytics me-1 text-primary"></i>
                                                        Data Snapshot - {{ strtoupper(str_replace('_', ' ', $log->action)) }} (Log #{{ $log->id }})
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        @if($log->old_values)
                                                            <div class="col-md-6">
                                                                <label class="fw-semibold small text-danger mb-1">Previous Values (Old):</label>
                                                                <pre class="bg-light p-3 rounded border small text-start font-monospace" style="max-height: 250px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                            </div>
                                                        @endif
                                                        <div class="{{ $log->old_values ? 'col-md-6' : 'col-md-12' }}">
                                                            <label class="fw-semibold small text-success mb-1">Recorded Values (New):</label>
                                                            <pre class="bg-light p-3 rounded border small text-start font-monospace" style="max-height: 250px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-file-search fs-1 d-block mb-2 text-muted"></i>
                                No activity logs match the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</span>
            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</div>
@endsection
