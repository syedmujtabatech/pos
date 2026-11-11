@extends('layouts.app')

@section('title', 'Company Settings & System Configurations')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="ti ti-settings text-primary me-2"></i>Company & System Settings (کمپنی ترتیبات)</h3>
        <p class="text-muted mb-0">Manage business profile, NTN/STRN tax numbers, currency, operational constraints, and database backups (FR-12 - FR-20).</p>
    </div>
    <div class="col-md-6 text-end">
        <form action="{{ route('settings.backup') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="ti ti-database-export me-1"></i>Create Instant Backup (ڈیٹا بیس بیک اپ)
            </button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Company Profile Form -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="ti ti-building me-2 text-primary"></i>Company Profile & Tax Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Name (English)</label>
                            <input type="text" name="settings[company_name]" class="form-control" value="{{ $settings['company_name'] ?? 'Al-Noor Traders' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Name (Urdu)</label>
                            <input type="text" name="settings[company_name_urdu]" class="form-control" value="{{ $settings['company_name_urdu'] ?? 'النور ٹریڈرز' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number(s)</label>
                            <input type="text" name="settings[company_phone]" class="form-control" value="{{ $settings['company_phone'] ?? '021-32415678, 0300-1234567' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Official Email</label>
                            <input type="email" name="settings[company_email]" class="form-control" value="{{ $settings['company_email'] ?? 'sales@alnoortraders.pk' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Business Physical Address</label>
                            <input type="text" name="settings[company_address]" class="form-control" value="{{ $settings['company_address'] ?? 'Shop # 14-16, Wholesale Market, Jodia Bazaar, Karachi, Pakistan' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">National Tax Number (NTN)</label>
                            <input type="text" name="settings[company_ntn]" class="form-control font-monospace" value="{{ $settings['company_ntn'] ?? '1425367-8' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sales Tax Registration (STRN)</label>
                            <input type="text" name="settings[company_strn]" class="form-control font-monospace" value="{{ $settings['company_strn'] ?? '17-00-1425-367-11' }}">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2 text-primary">System Operational Rules</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Default Currency</label>
                            <input type="text" name="settings[default_currency]" class="form-control" value="{{ $settings['default_currency'] ?? 'PKR' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cashier Max Discount (%)</label>
                            <input type="number" step="0.1" name="settings[cashier_max_discount]" class="form-control" value="{{ $settings['cashier_max_discount'] ?? '5.0' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Manager Max Discount (%)</label>
                            <input type="number" step="0.1" name="settings[manager_max_discount]" class="form-control" value="{{ $settings['manager_max_discount'] ?? '15.0' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Allow Negative Stock</label>
                            <select name="settings[allow_negative_stock]" class="form-select">
                                <option value="0" {{ ($settings['allow_negative_stock'] ?? '0') == '0' ? 'selected' : '' }}>Disabled (Strict Real Inventory Enforcement)</option>
                                <option value="1" {{ ($settings['allow_negative_stock'] ?? '0') == '1' ? 'selected' : '' }}>Enabled (Allow Overdrafts)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Overdue Credit Block Threshold</label>
                            <select name="settings[credit_block_days]" class="form-select">
                                <option value="60" selected>60+ Days Overdue (SRS Mandatory Rule)</option>
                                <option value="90">90+ Days</option>
                            </select>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ti ti-device-floppy me-1"></i>Save All Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Backup & Restore Sidebar -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="ti ti-database me-2 text-success"></i>Automated Backups (FR-20)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">The database snapshot contains all products, batches, customer ledgers, and transactions.</p>
                
                <h6 class="fw-bold mt-3 mb-2 small text-uppercase text-muted">Recent Backup Archives:</h6>
                <div class="list-group list-group-flush border rounded">
                    @forelse($backupLogs as $backup)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <small class="fw-bold font-monospace d-block text-truncate" style="max-width: 170px;">
                                    {{ $backup->description }}
                                </small>
                                <small class="text-muted">{{ $backup->created_at->format('d M, Y h:i A') }}</small>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="ti ti-check"></i> Safe
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-center py-3 text-muted small">
                            No backup snapshots logged yet. Click the green backup button above to generate one now.
                        </div>
                    @endforelse
                </div>

                <div class="alert alert-info mt-3 small mb-0">
                    <i class="ti ti-shield-check me-1"></i> Backups are stored in <code>storage/app/backups/</code> with automatic SHA256 integrity checks.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
