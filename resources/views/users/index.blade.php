@extends('layouts.app')

@section('title', 'Users & Role-Based Access Control (RBAC)')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-user-shield text-primary me-2"></i>Staff Users & Role Permissions (صارفین اور اختیارات)</h3>
        <p class="text-muted mb-0">Create staff accounts, define custom roles, and assign granular permissions matrix (FR-01 to FR-11).</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#newRoleModal">
            <i class="ti ti-shield-plus me-1"></i>Add New Role (نیا رول بنائیں)
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newUserModal">
            <i class="ti ti-user-plus me-1"></i>Add Staff User (نیا صارف)
        </button>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="rbacTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 py-2" id="users-tab" data-bs-toggle="pill" data-bs-target="#users-pane" type="button" role="tab">
            <i class="ti ti-users me-1"></i>Staff Users List ({{ $users->total() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2" id="roles-tab" data-bs-toggle="pill" data-bs-target="#roles-pane" type="button" role="tab">
            <i class="ti ti-shield-check me-1"></i>Roles & Permissions Matrix ({{ $roles->count() }} Roles)
        </button>
    </li>
</ul>

<div class="tab-content" id="rbacTabsContent">
    <!-- TAB 1: USERS LIST -->
    <div class="tab-pane fade show active" id="users-pane" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Staff Member</th>
                                <th>Login Username</th>
                                <th>Login Email</th>
                                <th>Assigned Role</th>
                                <th>Phone Number</th>
                                <th>Last Login</th>
                                <th class="text-center">Account Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if($user->id === auth()->id())
                                                    <span class="badge bg-success-subtle text-success ms-1 small">You</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><code>{{ $user->username }}</code></td>
                                    <td><span class="text-muted">{{ $user->email ?? 'No email' }}</span></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            {{ $user->role->name ?? 'Staff' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        <small class="text-muted">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M, h:i A') : 'Never' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($user->is_active)
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>Active</span>
                                        @else
                                            <span class="badge bg-danger">Deactivated</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#resetPassModal{{ $user->id }}" title="Reset Password">
                                                <i class="ti ti-key"></i>
                                            </button>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Toggle status for this user?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'danger' : 'success' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti ti-power"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Reset Password Modal -->
                                        <div class="modal fade text-start" id="resetPassModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('users.reset-password', $user->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Reset Password for {{ $user->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-info small mb-3">
                                                                <i class="ti ti-info-circle me-1"></i>
                                                                User can sign in with their email (<code>{{ $user->email }}</code>) or username (<code>{{ $user->username }}</code>).
                                                            </div>
                                                            <label class="form-label fw-bold">New Password</label>
                                                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update Password</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: ROLES & PERMISSIONS MATRIX -->
    <div class="tab-pane fade" id="roles-pane" role="tabpanel">
        <div class="row g-4">
            @foreach($roles as $role)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $role->name }}</h5>
                                <code class="small text-muted">{{ $role->slug }}</code>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                {{ $role->users->count() }} Users
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">{{ $role->description ?? 'Standard system role for staff operations.' }}</p>
                            
                            <h6 class="fw-bold small text-uppercase text-muted mb-2">
                                <i class="ti ti-check-circle text-success me-1"></i>
                                Assigned Permissions ({{ $role->permissions->count() }}):
                            </h6>
                            <div class="d-flex flex-wrap gap-1 mb-3" style="max-height: 150px; overflow-y: auto;">
                                @forelse($role->permissions->take(12) as $perm)
                                    <span class="badge bg-light text-dark border small" style="font-size: 11px;">
                                        {{ $perm->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small">No permissions assigned.</span>
                                @endforelse
                                @if($role->permissions->count() > 12)
                                    <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 11px;">
                                        +{{ $role->permissions->count() - 12 }} more
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                <i class="ti ti-edit me-1"></i>Configure Permissions
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal: Edit Role & Permissions -->
                <div class="modal fade text-start" id="editRoleModal{{ $role->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold"><i class="ti ti-shield-check text-primary me-2"></i>Configure Permissions: {{ $role->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Role Display Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Description</label>
                                            <input type="text" name="description" class="form-control" value="{{ $role->description }}">
                                        </div>
                                    </div>

                                    <h6 class="fw-bold border-bottom pb-2 text-primary">Granular Permissions Matrix (اختیارات منتخب کریں):</h6>

                                    @php
                                        $rolePermIds = $role->permissions->pluck('id')->toArray();
                                    @endphp

                                    @foreach($permissions as $module => $modulePerms)
                                        <div class="card mb-3 border">
                                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                <strong class="text-secondary small text-uppercase">{{ $module }}</strong>
                                                <small class="text-muted">{{ count($modulePerms) }} permissions</small>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row g-2">
                                                    @foreach($modulePerms as $perm)
                                                        <div class="col-md-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="role_{{ $role->id }}_perm_{{ $perm->id }}" {{ in_array($perm->id, $rolePermIds) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="role_{{ $role->id }}_perm_{{ $perm->id }}">
                                                                    {{ $perm->name }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="ti ti-check me-1"></i>Save Role Permissions
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: New Staff User -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="ti ti-user-plus me-2"></i>Create New Staff User (نیا صارف)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="ti ti-info-circle me-1"></i>
                        <strong>Login Tip:</strong> The user can log in to the software using either their <strong>Email Address</strong> or <strong>Username</strong> along with their password.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name (پورا نام) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Muhammad Aslam">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Login Username (یوزر نیم) <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required placeholder="e.g. aslam_cashier">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address (ای میل - لاگ ان کیلئے) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="aslam@alnoortraders.pk">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Login Password (پاس ورڈ) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min 6 characters (e.g. password)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned Role (عہدہ / کردار) <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select role...</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="0300-1234567">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-check me-1"></i>Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: New Role -->
<div class="modal fade" id="newRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="ti ti-shield-plus text-primary me-2"></i>Create New Role & Assign Permissions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role Name (رول کا نام) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Senior Cashier, Godown Assistant" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role Slug (Optional identifier)</label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. senior_cashier">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Allowed to create retail bills and view stock balances">
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 text-primary">Assign Permissions to this Role:</h6>

                    @foreach($permissions as $module => $modulePerms)
                        <div class="card mb-3 border">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <strong class="text-secondary small text-uppercase">{{ $module }}</strong>
                                <small class="text-muted">{{ count($modulePerms) }} permissions</small>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    @foreach($modulePerms as $perm)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="new_role_perm_{{ $perm->id }}">
                                                <label class="form-check-label small" for="new_role_perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-check me-1"></i>Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
