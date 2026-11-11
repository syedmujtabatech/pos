@extends('layouts.app')
@section('title', 'New Wholesale Invoice')

@section('content')
<div class="mb-4">
  <a href="{{ route('sales.wholesale') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Wholesale Sales
  </a>
  <h4 class="fw-bold mt-1">Create Wholesale Invoice (تھوک بل بنائیں)</h4>
  <p class="text-secondary small mb-0">Automatic wholesale pricing, credit limit validation & inventory deduction (FR-80 to FR-89)</p>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form action="{{ route('sales.wholesale.store') }}" method="POST" id="wholesaleForm">
      @csrf

      <!-- Customer & Route Selection -->
      <div class="row g-3 mb-4">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Customer / Shop <span class="text-danger">*</span></label>
          <select name="customer_id" id="customerSelect" class="form-select form-select-lg" required onchange="onCustomerChange(this)">
            <option value="">-- Choose Wholesale Customer --</option>
            @foreach($customers as $c)
              <option value="{{ $c->id }}"
                      data-limit="{{ $c->credit_limit }}"
                      data-balance="{{ $c->current_balance }}"
                      data-overdue="{{ $c->hasOverdueInvoices(60) ? '1' : '0' }}"
                      data-salesman="{{ $c->assigned_salesman_id }}">
                {{ $c->shop_name }} ({{ $c->name }} - {{ $c->area ? $c->area->name : 'Karachi' }})
              </option>
            @endforeach
          </select>

          <!-- Customer Credit Status Alert Banner -->
          <div id="customerCreditAlert" class="mt-2 d-none"></div>
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Dispatch Location <span class="text-danger">*</span></label>
          <select name="location_id" id="locationSelect" class="form-select form-select-lg" required>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->code }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
          <input type="date" name="invoice_date" class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="col-md-2">
          <label class="form-label fw-semibold">Salesman</label>
          <select name="salesman_id" id="salesmanSelect" class="form-select form-select-lg">
            <option value="">Counter Direct</option>
            @foreach($salesmen as $sm)
              <option value="{{ $sm->id }}">{{ $sm->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Invoice Items Table -->
      <h6 class="fw-bold text-primary mb-2"><i class="ti ti-boxes me-1"></i> Bill Items (سامان کی فہرست)</h6>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle" id="itemsTable">
          <thead class="table-light">
            <tr>
              <th width="35%">Product (سامان) <span class="text-danger">*</span></th>
              <th width="15%">Quantity <span class="text-danger">*</span></th>
              <th width="15%">Wholesale Price (Rs.) <span class="text-danger">*</span></th>
              <th width="12%">Discount (Rs.)</th>
              <th width="18%" class="text-end">Line Total (میزان)</th>
              <th width="5%"></th>
            </tr>
          </thead>
          <tbody id="itemsTableBody">
            <tr>
              <td>
                <select name="items[0][product_id]" class="form-select product-select" required onchange="setWholesalePrice(this, 0)">
                  <option value="">-- Select Product --</option>
                  @foreach($products as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->wholesale_price }}" data-stock="{{ $p->total_stock }}">
                      {{ $p->name }} (Stock: {{ number_format($p->total_stock) }})
                    </option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input" value="10" min="1" required oninput="calculateInvoiceTotals()">
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control price-input" placeholder="0.00" required oninput="calculateInvoiceTotals()">
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][discount]" class="form-control disc-input" value="0" min="0" oninput="calculateInvoiceTotals()">
              </td>
              <td class="text-end fw-bold line-total">Rs. 0.00</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm text-danger p-0" onclick="removeInvRow(this)"><i class="ti ti-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInvRow()">
          <i class="ti ti-plus me-1"></i> Add Another Product Line
        </button>
      </div>

      <!-- Financial Calculation Summary Box -->
      <div class="row g-3 mb-4 justify-content-end">
        <div class="col-md-5">
          <div class="card bg-light border p-3">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Subtotal (اشیاء کی رقم):</span>
              <span class="fw-bold" id="subtotalText">Rs. 0.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Invoice Discount (رعایت):</span>
              <div class="input-group input-group-sm" style="max-width: 140px;">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="invoice_discount" id="invDiscountInput" class="form-control text-end" value="0" min="0" oninput="calculateInvoiceTotals()">
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Delivery Freight (کرایہ / ٹرانسپورٹ):</span>
              <div class="input-group input-group-sm" style="max-width: 140px;">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="delivery_charges" id="freightInput" class="form-control text-end" value="0" min="0" oninput="calculateInvoiceTotals()">
              </div>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-bold fs-6">Grand Total (کل رقم):</span>
              <span class="fw-bold fs-5 text-primary" id="grandTotalText">Rs. 0.00</span>
            </div>

            <!-- Payment Type -->
            <div class="mb-2">
              <label class="form-label small fw-semibold">Payment Terms (طریقہ ادائیگی)</label>
              <select name="payment_type" id="paymentTypeSelect" class="form-select form-select-sm" onchange="onPaymentTypeChange()">
                <option value="credit" selected>Credit Sale (ادھار کھاتہ)</option>
                <option value="cash">Full Cash (مکمل نقد)</option>
                <option value="mixed">Partial Cash + Credit (کچھ نقد + کچھ ادھار)</option>
                <option value="bank">Bank Transfer</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>

            <!-- Paid amount -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Paid on Spot (وصول شدہ رقم):</span>
              <div class="input-group input-group-sm" style="max-width: 140px;">
                <span class="input-group-text">Rs.</span>
                <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control text-end" value="0" min="0" oninput="calculateInvoiceTotals()">
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="fw-bold text-danger">Balance Due (گاہک کے کھاتے میں):</span>
              <span class="fw-bold fs-6 text-danger" id="dueAmountText">Rs. 0.00</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Credit Limit Override authorization checkbox if manager -->
      @if(auth()->user()->isManager())
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="credit_override_approved" id="overrideCheck" value="1">
          <label class="form-check-label small fw-semibold text-warning" for="overrideCheck">
            Manager Override: Authorize credit limit / overdue exception if required.
          </label>
        </div>
      @endif

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('sales.wholesale') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-bold">
          <i class="ti ti-check me-1"></i> Save & Post Wholesale Invoice
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let itemRowIndex = 1;

  function onCustomerChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;

    const limit = parseFloat(opt.getAttribute('data-limit')) || 0;
    const balance = parseFloat(opt.getAttribute('data-balance')) || 0;
    const isOverdue = opt.getAttribute('data-overdue') === '1';
    const salesmanId = opt.getAttribute('data-salesman');

    if (salesmanId) {
      document.getElementById('salesmanSelect').value = salesmanId;
    }

    const alertBox = document.getElementById('customerCreditAlert');
    alertBox.classList.remove('d-none');

    let msg = `<div class="p-2 rounded border small ${isOverdue || (limit > 0 && balance >= limit) ? 'bg-danger-subtle text-danger border-danger' : 'bg-light text-dark'}">
      <div><strong>Current Udhaar:</strong> Rs. ${balance.toLocaleString()} | <strong>Credit Limit:</strong> Rs. ${limit.toLocaleString()}</div>`;

    if (isOverdue) {
      msg += `<div class="fw-bold mt-1 text-danger"><i class="ti ti-alert-triangle"></i> Notice: Customer has 60+ days overdue bills!</div>`;
    }
    if (limit > 0 && balance >= limit) {
      msg += `<div class="fw-bold mt-1 text-danger"><i class="ti ti-alert-circle"></i> Warning: Credit limit already exhausted!</div>`;
    }

    msg += `</div>`;
    alertBox.innerHTML = msg;
  }

  function setWholesalePrice(select, idx) {
    const price = select.options[select.selectedIndex].getAttribute('data-price');
    const tr = select.closest('tr');
    tr.querySelector('.price-input').value = price || 0;
    calculateInvoiceTotals();
  }

  function addInvRow() {
    const tbody = document.getElementById('itemsTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="items[${itemRowIndex}][product_id]" class="form-select product-select" required onchange="setWholesalePrice(this, ${itemRowIndex})">
          <option value="">-- Select Product --</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" data-price="{{ $p->wholesale_price }}" data-stock="{{ $p->total_stock }}">
              {{ $p->name }} (Stock: {{ number_format($p->total_stock) }})
            </option>
          @endforeach
        </select>
      </td>
      <td>
        <input type="number" step="0.01" name="items[${itemRowIndex}][quantity]" class="form-control qty-input" value="10" min="1" required oninput="calculateInvoiceTotals()">
      </td>
      <td>
        <input type="number" step="0.01" name="items[${itemRowIndex}][unit_price]" class="form-control price-input" placeholder="0.00" required oninput="calculateInvoiceTotals()">
      </td>
      <td>
        <input type="number" step="0.01" name="items[${itemRowIndex}][discount]" class="form-control disc-input" value="0" min="0" oninput="calculateInvoiceTotals()">
      </td>
      <td class="text-end fw-bold line-total">Rs. 0.00</td>
      <td class="text-center">
        <button type="button" class="btn btn-sm text-danger p-0" onclick="removeInvRow(this)"><i class="ti ti-trash"></i></button>
      </td>`;
    tbody.appendChild(tr);
    itemRowIndex++;
  }

  function removeInvRow(btn) {
    const tbody = document.getElementById('itemsTableBody');
    if (tbody.rows.length > 1) {
      btn.closest('tr').remove();
      calculateInvoiceTotals();
    } else {
      alert("At least one product line is required.");
    }
  }

  function onPaymentTypeChange() {
    const type = document.getElementById('paymentTypeSelect').value;
    const grand = parseFloat(document.getElementById('grandTotalText').innerText.replace(/[^0-9.-]+/g,"")) || 0;
    const paidInput = document.getElementById('paidAmountInput');

    if (type === 'cash') {
      paidInput.value = grand;
    } else if (type === 'credit') {
      paidInput.value = 0;
    }
    calculateInvoiceTotals();
  }

  function calculateInvoiceTotals() {
    let subtotal = 0;
    document.querySelectorAll('#itemsTableBody tr').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
      const price = parseFloat(tr.querySelector('.price-input')?.value) || 0;
      const disc = parseFloat(tr.querySelector('.disc-input')?.value) || 0;
      const line = (qty * price) - disc;
      const lineCell = tr.querySelector('.line-total');
      if (lineCell) lineCell.innerText = "Rs. " + line.toLocaleString();
      subtotal += line;
    });

    const invDisc = parseFloat(document.getElementById('invDiscountInput')?.value) || 0;
    const freight = parseFloat(document.getElementById('freightInput')?.value) || 0;
    const grand = Math.max(0, subtotal - invDisc + freight);

    const paid = parseFloat(document.getElementById('paidAmountInput')?.value) || 0;
    const due = Math.max(0, grand - paid);

    document.getElementById('subtotalText').innerText = "Rs. " + subtotal.toLocaleString();
    document.getElementById('grandTotalText').innerText = "Rs. " + grand.toLocaleString();
    document.getElementById('dueAmountText').innerText = "Rs. " + due.toLocaleString();
  }

  document.addEventListener('DOMContentLoaded', calculateInvoiceTotals);
</script>
@endpush
