@extends('layouts.app')

@section('title', 'Login History & Security')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-shield-lock text-primary me-2"></i>Staff Login History (لاگ ان سیکیورٹی ہسٹری)</h3>
        <p class="text-muted mb-0">Audit log of all authentication attempts, successful logins, failed attempts, and account locks (FR-08).</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-primary">
            <i class="ti ti-history-toggle me-1"></i>View Activity Logs
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti ti-login fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Logins Today</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($todayLogins) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti ti-check fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Successful Logins</span>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($successfulToday) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti ti-x fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Failed Attempts</span>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($failedToday) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti ti-lock-square-rounded fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Locked Accounts</span>
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($lockedAccounts) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.login-history') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Filter by User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">-- All Users --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->username }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success (کامیاب)</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed (ناکام)</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked (لاک)</option>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Username, IP..." class="form-control form-control-sm">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter">
                    <i class="ti ti-filter"></i>
                </button>
                <a href="{{ route('admin.login-history') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Login History Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 170px;">Date & Time</th>
                        <th>User Account</th>
                        <th>Attempted Username</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Browser / Device Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $item->created_at->format('d M Y, h:i A') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $item->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($item->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 11px;">
                                            {{ strtoupper(substr($item->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $item->user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <span class="badge bg-secondary-subtle text-secondary py-0 px-1">{{ $item->user->role->name ?? 'Staff' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">Unregistered Account</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace small fw-semibold text-dark">{{ $item->username }}</span>
                            </td>
                            <td>
                                @if($item->status === 'success')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="ti ti-check me-1"></i>SUCCESS
                                    </span>
                                @elseif($item->status === 'failed')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="ti ti-x me-1"></i>FAILED
                                    </span>
                                @elseif($item->status === 'locked')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="ti ti-lock me-1"></i>LOCKED
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ strtoupper($item->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $item->ip_address ?? '127.0.0.1' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate text-muted small" style="max-width: 320px;" title="{{ $item->user_agent }}">
                                    <i class="ti ti-device-desktop me-1 text-secondary"></i>
                                    {{ Str::limit($item->user_agent, 45) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-shield-off fs-1 d-block mb-2 text-muted"></i>
                                No login attempts recorded for the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $history->firstItem() ?? 0 }} to {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} entries</span>
            <div>{{ $history->links() }}</div>
        </div>
    </div>
</div>
@endsection
