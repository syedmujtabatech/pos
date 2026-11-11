@extends('layouts.app')
@section('title', 'POS Sales Invoices History')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold text-dark mb-1"><i class="ti ti-receipt text-primary me-2"></i> POS Sales &amp; Invoices Log</h4>
    <p class="text-muted small mb-0">Complete historical sales register with 1-click thermal receipt reprinting</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('pos.index') }}" class="btn btn-success fw-bold shadow-sm">
      <i class="ti ti-device-tablet me-1"></i> Open POS Counter
    </a>
  </div>
</div>

<!-- Quick Stats Summary -->
<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-6">
    <div class="card p-3 border-0 shadow-sm border-start border-4 border-success bg-white">
      <span class="small text-muted fw-bold text-uppercase">Today's Total Sales</span>
      <div class="h3 fw-bold text-dark my-1">Rs. {{ number_format($todayAmount) }}</div>
      <small class="text-muted"><i class="ti ti-calendar me-1"></i> Date: {{ now()->format('d M Y') }}</small>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="card p-3 border-0 shadow-sm border-start border-4 border-primary bg-white">
      <span class="small text-muted fw-bold text-uppercase">All-Time POS Turnover</span>
      <div class="h3 fw-bold text-dark my-1">Rs. {{ number_format($totalAmount) }}</div>
      <small class="text-muted"><i class="ti ti-receipt me-1"></i> Total Register Volume</small>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="card p-3 border-0 shadow-sm border-start border-4 border-info bg-white">
      <span class="small text-muted fw-bold text-uppercase">Total Invoices Logged</span>
      <div class="h3 fw-bold text-dark my-1">{{ $invoices->total() }}</div>
      <small class="text-muted"><i class="ti ti-check me-1"></i> Completed Transactions</small>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4 bg-white">
  <div class="card-body p-3">
    <form action="{{ route('pos.invoices') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light"><i class="ti ti-search text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by Invoice #, Customer name or phone..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-3">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
      </div>
      <div class="col-md-3">
        <select name="payment_type" class="form-select form-select-sm">
          <option value="">All Payment Methods</option>
          <option value="cash" {{ request('payment_type') == 'cash' ? 'selected' : '' }}>Cash</option>
          <option value="card" {{ request('payment_type') == 'card' ? 'selected' : '' }}>Card</option>
          <option value="online" {{ request('payment_type') == 'online' ? 'selected' : '' }}>Online / JazzCash</option>
          <option value="credit" {{ request('payment_type') == 'credit' ? 'selected' : '' }}>Udhar (Khata)</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold">Filter</button>
        @if(request()->hasAny(['search', 'date', 'payment_type']))
          <a href="{{ route('pos.invoices') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        @endif
      </div>
    </form>
  </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm bg-white">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light small text-uppercase text-secondary">
        <tr>
          <th>Invoice #</th>
          <th>Date &amp; Time</th>
          <th>Customer</th>
          <th>Items Count</th>
          <th>Subtotal</th>
          <th>Discount</th>
          <th>Grand Total</th>
          <th>Payment</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td>
              <span class="fw-bold text-primary">{{ $inv->invoice_number }}</span>
            </td>
            <td>
              <div class="small fw-semibold text-dark">{{ $inv->created_at->format('d M Y') }}</div>
              <div class="small text-muted" style="font-size: 11px;">{{ $inv->created_at->format('h:i A') }}</div>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $inv->customer?->name ?: 'Walk-in Customer' }}</div>
              <div class="small text-muted" style="font-size: 11px;">{{ $inv->customer?->phone }}</div>
            </td>
            <td>
              <span class="badge bg-light text-dark border">{{ $inv->items->count() }} items</span>
            </td>
            <td class="text-muted small">
              Rs. {{ number_format($inv->subtotal) }}
            </td>
            <td class="text-muted small">
              @if($inv->invoice_discount > 0)
                <span class="text-danger">- Rs. {{ number_format($inv->invoice_discount) }}</span>
              @else
                -
              @endif
            </td>
            <td class="fw-bold text-success fs-6">
              Rs. {{ number_format($inv->grand_total) }}
            </td>
            <td>
              @php
                $badgeColor = match(strtolower($inv->payment_type)) {
                  'cash' => 'success',
                  'card' => 'primary',
                  'online' => 'info',
                  'credit' => 'danger',
                  default => 'secondary'
                };
              @endphp
              <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }}-subtle text-uppercase">
                {{ $inv->payment_type }}
              </span>
            </td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="reprintReceipt({{ $inv->id }})" title="Reprint 80mm Slip">
                <i class="ti ti-printer me-1"></i> Print Receipt
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="ti ti-receipt-off fs-1 text-black-50 mb-2 d-block"></i>
              <h6>No invoices recorded yet.</h6>
              <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success mt-2">Go to POS Counter</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($invoices->hasPages())
    <div class="card-footer bg-white border-0 py-3">
      {{ $invoices->links() }}
    </div>
  @endif
</div>

<!-- Thermal Receipt Modal for History -->
<div class="modal fade" id="historyReceiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header py-2 bg-dark text-white">
        <h6 class="modal-title fw-bold"><i class="ti ti-receipt me-1"></i> Invoice Receipt</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">
        <div id="historyReceiptContent" class="p-3 bg-white border shadow-sm mx-auto font-monospace" style="font-size: 12px; color: #000; line-height: 1.35;">
          <div class="text-center mb-2">
            <h5 class="fw-bold mb-0 text-dark">ApexPOS Store</h5>
            <div class="small">Smart Retail &amp; Point of Sale</div>
            <div style="font-size: 11px;">Main Commercial Market, Counter #1</div>
            <div style="font-size: 11px;">Tel: +92 300 1234567</div>
          </div>
          <div class="border-top border-dashed my-2"></div>
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Inv #: <strong id="hRcptInvNum">-</strong></span>
            <span id="hRcptDateTime">-</span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Cashier: <strong id="hRcptCashier">-</strong></span>
            <span>Pay: <strong id="hRcptPayType">-</strong></span>
          </div>
          <div style="font-size: 11px;">Customer: <strong id="hRcptCustomer">-</strong></div>
          <div class="border-top border-dashed my-2"></div>
          <table class="w-100 mb-2" style="font-size: 11px;">
            <thead>
              <tr class="border-bottom">
                <th class="text-start pb-1">Item</th>
                <th class="text-center pb-1">Qty</th>
                <th class="text-end pb-1">Rate</th>
                <th class="text-end pb-1">Total</th>
              </tr>
            </thead>
            <tbody id="hRcptItemsBody"></tbody>
          </table>
          <div class="border-top border-dashed my-2"></div>
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Subtotal:</span>
            <span>Rs. <span id="hRcptSubtotal">0</span></span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;" id="hRcptDiscRow">
            <span>Discount:</span>
            <span>- Rs. <span id="hRcptDiscount">0</span></span>
          </div>
          <div class="d-flex justify-content-between fw-bold fs-6 border-top border-bottom py-1 my-1">
            <span>GRAND TOTAL:</span>
            <span>Rs. <span id="hRcptGrandTotal">0</span></span>
          </div>
          <div class="text-center mt-3" style="font-size: 10px;">
            <div>Thank you for shopping with us!</div>
            <div class="mt-1 text-muted">Powered by ApexPOS Smart Retail</div>
          </div>
        </div>
      </div>
      <div class="modal-footer p-2 bg-white d-flex justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="window.print()">
          <i class="ti ti-printer me-1"></i> Print Receipt
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  async function reprintReceipt(invoiceId) {
    try {
      const res = await fetch(`/pos/receipt/${invoiceId}`);
      const data = await res.json();
      if (data.success) {
        const inv = data.invoice;
        document.getElementById('hRcptInvNum').textContent = inv.invoice_number;
        document.getElementById('hRcptDateTime').textContent = inv.date_time;
        document.getElementById('hRcptCustomer').textContent = inv.customer_name;
        document.getElementById('hRcptCashier').textContent = inv.cashier_name;
        document.getElementById('hRcptPayType').textContent = inv.payment_type;

        const tbody = document.getElementById('hRcptItemsBody');
        tbody.innerHTML = '';
        inv.items.forEach(it => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="text-start">${it.name}</td>
            <td class="text-center">${it.quantity}</td>
            <td class="text-end">${it.price.toLocaleString()}</td>
            <td class="text-end fw-bold">${it.total.toLocaleString()}</td>
          `;
          tbody.appendChild(tr);
        });

        document.getElementById('hRcptSubtotal').textContent = inv.subtotal.toLocaleString();
        document.getElementById('hRcptDiscount').textContent = inv.discount.toLocaleString();
        document.getElementById('hRcptGrandTotal').textContent = inv.grand_total.toLocaleString();

        if (inv.discount > 0) {
          document.getElementById('hRcptDiscRow').classList.remove('d-none');
        } else {
          document.getElementById('hRcptDiscRow').classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('historyReceiptModal'));
        modal.show();
      } else {
        alert('Could not fetch receipt details.');
      }
    } catch (err) {
      alert('Error fetching receipt: ' + err.message);
    }
  }
</script>
@endpush
