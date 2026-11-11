@extends('layouts.app')
@section('title', 'Supplier Ledger - ' . $supplier->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <a href="{{ route('suppliers.index') }}" class="text-decoration-none small text-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to Suppliers
    </a>
    <h4 class="fw-bold mt-1 mb-0"><i class="ti ti-book text-primary me-1"></i> Supplier Payable Ledger (سپلائر کھاتہ)</h4>
    <small class="text-secondary">{{ $supplier->name }} • {{ $supplier->phone }}</small>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
      <i class="ti ti-printer me-1"></i> Print Statement
    </button>
    <a href="{{ route('purchases.payments', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-success">
      <i class="ti ti-cash-banknote me-1"></i> Make Payment
    </a>
  </div>
</div>

<!-- Supplier Profile Card -->
<div class="card mb-4 shadow-sm">
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-md-3">
        <span class="text-muted small d-block">Agency & Contact</span>
        <h5 class="fw-bold mb-0">{{ $supplier->name }}</h5>
        <small class="text-secondary">{{ $supplier->contact_person }}</small>
      </div>
      <div class="col-md-3">
        <span class="text-muted small d-block">Phone & NTN</span>
        <div class="fw-semibold">{{ $supplier->phone }}</div>
        <small class="text-secondary">NTN: {{ $supplier->ntn ?? 'N/A' }}</small>
      </div>
      <div class="col-md-3">
        <span class="text-muted small d-block">Credit Terms</span>
        <div class="fw-semibold">{{ $supplier->payment_terms ?? 'Net 30' }}</div>
        <small class="text-secondary">Limit: Rs. {{ number_format($supplier->credit_limit) }}</small>
      </div>
      <div class="col-md-3 text-md-end">
        <span class="text-muted small d-block">Current Total Payable</span>
        <h4 class="fw-bold text-danger mb-0">Rs. {{ number_format($supplier->current_balance, 2) }}</h4>
        <small class="text-muted">Opening: Rs. {{ number_format($supplier->opening_balance, 2) }}</small>
      </div>
    </div>
  </div>
</div>

<!-- Date Filter -->
<div class="card mb-4 no-print shadow-sm">
  <div class="card-body p-3">
    <form action="{{ route('suppliers.ledger', $supplier->id) }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <label class="form-label small fw-semibold mb-0">From Date</label>
        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold mb-0">To Date</label>
        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
      </div>
      <div class="col-md-4 d-flex align-items-end gap-1 mt-md-4">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter Ledger</button>
        <a href="{{ route('suppliers.ledger', $supplier->id) }}" class="btn btn-sm btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Ledger Transactions Table -->
<div class="card shadow-sm">
  <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-bold">Chronological Payable Ledger (خریداری اور ادائیگیوں کی تفصیل)</h6>
    <span class="text-muted small">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Particulars / Document</th>
          <th>Reference #</th>
          <th class="text-end">Credit (خریداری / اضافہ)</th>
          <th class="text-end">Debit (ادائیگی / کمی)</th>
          <th class="text-end">Running Payable Balance (بقایا واجب الادا)</th>
        </tr>
      </thead>
      <tbody>
        <!-- Opening Balance -->
        <tr class="table-light fw-semibold">
          <td colspan="3">Opening Balance Brought Forward (ابتدائی بقایا)</td>
          <td class="text-end">-</td>
          <td class="text-end">-</td>
          <td class="text-end text-danger">Rs. {{ number_format($supplier->opening_balance, 2) }}</td>
        </tr>

        @php
          $totCredit = 0;
          $totDebit = 0;
        @endphp

        @forelse($ledgerRows as $row)
          @php
            $totCredit += $row['credit'];
            $totDebit += $row['debit'];
          @endphp
          <tr>
            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
            <td>
              <div class="fw-semibold">{{ $row['notes'] }}</div>
              <small class="text-muted">{{ $row['type'] }}</small>
            </td>
            <td><code>{{ $row['ref'] }}</code></td>
            <td class="text-end text-primary fw-semibold">
              {{ $row['credit'] > 0 ? 'Rs. ' . number_format($row['credit'], 2) : '-' }}
            </td>
            <td class="text-end text-success fw-semibold">
              {{ $row['debit'] > 0 ? 'Rs. ' . number_format($row['debit'], 2) : '-' }}
            </td>
            <td class="text-end fw-bold {{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }}">
              Rs. {{ number_format($row['balance'], 2) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">No transactions found in this period.</td>
          </tr>
        @endforelse
      </tbody>
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="3" class="text-end">Total Activity:</td>
          <td class="text-end text-primary">Rs. {{ number_format($totCredit, 2) }}</td>
          <td class="text-end text-success">Rs. {{ number_format($totDebit, 2) }}</td>
          <td class="text-end text-danger fs-6">Rs. {{ number_format($supplier->current_balance, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
