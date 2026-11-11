@extends('layouts.app')
@section('title', 'Suppliers Directory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Suppliers & Manufacturers (سپلائرز اور کمپنیاں)</h4>
    <p class="text-secondary small mb-0">Manage 40+ wholesale suppliers, distributor agencies and payable ledgers (FR-48 to FR-54)</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('purchases.payments') }}" class="btn btn-sm btn-outline-success">
      <i class="ti ti-cash-banknote me-1"></i> Make Payment
    </a>
    <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary">
      <i class="ti ti-plus me-1"></i> Add Supplier
    </a>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-body p-3">
    <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search supplier by name, contact, phone..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-3 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Search</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Supplier Agency</th>
          <th>Contact Person</th>
          <th>Phone / Email</th>
          <th>NTN</th>
          <th>Payment Terms</th>
          <th class="text-end">Credit Limit</th>
          <th class="text-end">Current Payable (واجب الادا)</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $s)
          <tr>
            <td>
              <div class="fw-bold">{{ $s->name }}</div>
              <small class="text-muted">{{ Str::limit($s->address, 35) }}</small>
            </td>
            <td>{{ $s->contact_person ?? '-' }}</td>
            <td>
              <div>{{ $s->phone }}</div>
              <small class="text-muted">{{ $s->email }}</small>
            </td>
            <td><code>{{ $s->ntn ?? 'N/A' }}</code></td>
            <td><span class="badge bg-light text-dark border">{{ $s->payment_terms ?? 'Net 30' }}</span></td>
            <td class="text-end">Rs. {{ number_format($s->credit_limit) }}</td>
            <td class="text-end fw-bold {{ $s->current_balance > 0 ? 'text-danger' : 'text-success' }}">
              Rs. {{ number_format($s->current_balance, 2) }}
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="{{ route('suppliers.ledger', $s->id) }}" class="btn btn-outline-primary" title="View Supplier Ledger">
                  <i class="ti ti-book me-1"></i> Ledger
                </a>
                <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-light border" title="Edit">
                  <i class="ti ti-edit"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">No suppliers registered.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($suppliers->hasPages())
    <div class="card-footer bg-white py-2">
      {{ $suppliers->links() }}
    </div>
  @endif
</div>
@endsection
