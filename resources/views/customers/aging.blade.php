@extends('layouts.app')
@section('title', 'Customer Aging Analysis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-clock-pause text-danger me-1"></i> Customer Udhaar Aging Analysis (ادھار عمر کا تجزیہ)</h4>
    <p class="text-secondary small mb-0">Outstanding credit grouped by 0–30, 31–60, 61–90 and 90+ days (FR-136)</p>
  </div>
  <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
    <i class="ti ti-printer me-1"></i> Print Aging Report
  </button>
</div>

<!-- Summary Aging KPI Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card p-3 border-start border-4 border-success shadow-sm">
      <span class="text-muted small text-uppercase fw-semibold">0–30 Days (حالیہ ادھار)</span>
      <h4 class="fw-bold text-success my-1">Rs. {{ number_format($totals['0_30']) }}</h4>
      <small class="text-secondary">Normal credit period</small>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card p-3 border-start border-4 border-info shadow-sm">
      <span class="text-muted small text-uppercase fw-semibold">31–60 Days</span>
      <h4 class="fw-bold text-info my-1">Rs. {{ number_format($totals['31_60']) }}</h4>
      <small class="text-secondary">Follow-up reminder stage</small>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card p-3 border-start border-4 border-warning shadow-sm">
      <span class="text-muted small text-uppercase fw-semibold">61–90 Days (خطرہ)</span>
      <h4 class="fw-bold text-warning my-1">Rs. {{ number_format($totals['61_90']) }}</h4>
      <small class="text-danger fw-semibold">Credit sale blocked without Manager approval</small>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card p-3 border-start border-4 border-danger shadow-sm">
      <span class="text-muted small text-uppercase fw-semibold">90+ Days (پرانا پھنسا ادھار)</span>
      <h4 class="fw-bold text-danger my-1">Rs. {{ number_format($totals['90_plus']) }}</h4>
      <small class="text-danger fw-semibold">High risk recovery priority</small>
    </div>
  </div>
</div>

<!-- Aging Details Table -->
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Customer / Shop</th>
          <th>Salesman & Area</th>
          <th class="text-end">0–30 Days</th>
          <th class="text-end">31–60 Days</th>
          <th class="text-end">61–90 Days</th>
          <th class="text-end">90+ Days</th>
          <th class="text-end">Total Outstanding</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($agingData as $item)
          <tr class="{{ $item['is_overdue_blocked'] ? 'table-warning' : '' }}">
            <td>
              <div class="fw-bold">{{ $item['customer']->shop_name }}</div>
              <small class="text-muted">{{ $item['customer']->name }} • {{ $item['customer']->phone }}</small>
            </td>
            <td>
              <div>{{ $item['customer']->assignedSalesman ? $item['customer']->assignedSalesman->name : 'Direct' }}</div>
              <small class="text-muted">{{ $item['customer']->area ? $item['customer']->area->name : 'Karachi' }}</small>
            </td>
            <td class="text-end text-success fw-semibold">Rs. {{ number_format($item['0_30'], 2) }}</td>
            <td class="text-end text-info fw-semibold">Rs. {{ number_format($item['31_60'], 2) }}</td>
            <td class="text-end text-warning fw-bold">Rs. {{ number_format($item['61_90'], 2) }}</td>
            <td class="text-end text-danger fw-bold">Rs. {{ number_format($item['90_plus'], 2) }}</td>
            <td class="text-end fw-bold text-dark fs-6">Rs. {{ number_format($item['total'], 2) }}</td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="{{ route('customers.ledger', $item['customer']->id) }}" class="btn btn-outline-primary" title="View Ledger">
                  Ledger
                </a>
                <a href="{{ route('finance.receipts', ['customer_id' => $item['customer']->id]) }}" class="btn btn-outline-success" title="Recovery Receipt">
                  Recover
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">All customer balances are clear!</td>
          </tr>
        @endforelse
      </tbody>
      <tfoot class="table-dark fw-bold">
        <tr>
          <td colspan="2">Total Portfolio Outstanding:</td>
          <td class="text-end">Rs. {{ number_format($totals['0_30']) }}</td>
          <td class="text-end">Rs. {{ number_format($totals['31_60']) }}</td>
          <td class="text-end">Rs. {{ number_format($totals['61_90']) }}</td>
          <td class="text-end text-danger">Rs. {{ number_format($totals['90_plus']) }}</td>
          <td class="text-end text-warning fs-6">Rs. {{ number_format($totals['total']) }}</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
