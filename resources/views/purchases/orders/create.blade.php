@extends('layouts.app')
@section('title', 'Create Purchase Order')

@section('content')
<div class="mb-4">
  <a href="{{ route('purchases.orders') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Orders
  </a>
  <h4 class="fw-bold mt-1">Create Purchase Order (خریداری کا نیا آرڈر)</h4>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form action="{{ route('purchases.orders.store') }}" method="POST" id="poForm">
      @csrf

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Supplier Agency <span class="text-danger">*</span></label>
          <select name="supplier_id" class="form-select" required>
            <option value="">-- Choose Supplier --</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->id }}">{{ $s->name }} (Payable: Rs. {{ number_format($s->current_balance) }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Destination Location <span class="text-danger">*</span></label>
          <select name="location_id" class="form-select" required>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}">{{ $loc->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
          <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Expected Delivery Date</label>
          <input type="date" name="expected_date" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
        </div>
      </div>

      <!-- Items Table -->
      <h6 class="fw-bold text-primary mb-2"><i class="ti ti-list me-1"></i> Order Items (سامان کی تفصیل)</h6>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle" id="poItemsTable">
          <thead class="table-light">
            <tr>
              <th width="45%">Product <span class="text-danger">*</span></th>
              <th width="20%">Order Quantity <span class="text-danger">*</span></th>
              <th width="20%">Unit Purchase Cost (Rs.) <span class="text-danger">*</span></th>
              <th width="15%" class="text-end">Subtotal</th>
              <th width="5%"></th>
            </tr>
          </thead>
          <tbody id="poTableBody">
            <tr>
              <td>
                <select name="items[0][product_id]" class="form-select product-select" required onchange="setProductCost(this, 0)">
                  <option value="">-- Select Product --</option>
                  @foreach($products as $p)
                    <option value="{{ $p->id }}" data-cost="{{ $p->purchase_cost }}">
                      {{ $p->name }} ({{ $p->sku }})
                    </option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input" value="100" min="1" required oninput="calculatePoTotal()">
              </td>
              <td>
                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control cost-input" placeholder="0.00" required oninput="calculatePoTotal()">
              </td>
              <td class="text-end fw-bold line-total">Rs. 0.00</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)"><i class="ti ti-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
          <i class="ti ti-plus me-1"></i> Add Another Item
        </button>
        <div class="text-end">
          <span class="fs-6 text-muted me-2">Grand Total:</span>
          <span class="fs-4 fw-bold text-primary" id="poGrandTotalText">Rs. 0.00</span>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Order Notes / Supplier Instructions</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Delivery vehicle, payment terms, or batch preferences"></textarea>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('purchases.orders') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Submit Purchase Order
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let rowIndex = 1;

  function setProductCost(select, idx) {
    const cost = select.options[select.selectedIndex].getAttribute('data-cost');
    const tr = select.closest('tr');
    tr.querySelector('.cost-input').value = cost || 0;
    calculatePoTotal();
  }

  function addRow() {
    const tbody = document.getElementById('poTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="setProductCost(this, ${rowIndex})">
          <option value="">-- Select Product --</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" data-cost="{{ $p->purchase_cost }}">
              {{ $p->name }} ({{ $p->sku }})
            </option>
          @endforeach
        </select>
      </td>
      <td>
        <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="50" min="1" required oninput="calculatePoTotal()">
      </td>
      <td>
        <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-control cost-input" placeholder="0.00" required oninput="calculatePoTotal()">
      </td>
      <td class="text-end fw-bold line-total">Rs. 0.00</td>
      <td class="text-center">
        <button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)"><i class="ti ti-trash"></i></button>
      </td>`;
    tbody.appendChild(tr);
    rowIndex++;
  }

  function removeRow(btn) {
    const tbody = document.getElementById('poTableBody');
    if (tbody.rows.length > 1) {
      btn.closest('tr').remove();
      calculatePoTotal();
    } else {
      alert("Order must have at least one product row.");
    }
  }

  function calculatePoTotal() {
    let grand = 0;
    document.querySelectorAll('#poTableBody tr').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
      const cost = parseFloat(tr.querySelector('.cost-input')?.value) || 0;
      const line = qty * cost;
      const lineCell = tr.querySelector('.line-total');
      if (lineCell) lineCell.innerText = "Rs. " + line.toLocaleString();
      grand += line;
    });

    document.getElementById('poGrandTotalText').innerText = "Rs. " + grand.toLocaleString();
  }

  document.addEventListener('DOMContentLoaded', calculatePoTotal);
</script>
@endpush
