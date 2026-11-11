@extends('layouts.app')
@section('title', 'Receive Goods (GRN)')

@section('content')
<div class="mb-4">
  <a href="{{ route('purchases.grn') }}" class="text-decoration-none small text-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to GRN List
  </a>
  <h4 class="fw-bold mt-1">Receive Inward Goods / Post GRN (مال کی آمد کا اندراج)</h4>
  <p class="text-secondary small mb-0">Upon posting, inventory batches will be increased immediately and supplier invoice generated (FR-70, FR-71).</p>
</div>

<div class="card shadow-sm">
  <div class="card-body p-4">
    <form action="{{ route('purchases.grn.store') }}" method="POST" id="grnForm">
      @csrf

      @if($po)
        <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
        <div class="alert alert-info py-2 d-flex align-items-center gap-2 mb-3">
          <i class="ti ti-link fs-4"></i>
          <div>Receiving against <strong>Purchase Order #{{ $po->po_number }}</strong> (Supplier: {{ $po->supplier->name }})</div>
        </div>
      @endif

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Supplier Agency <span class="text-danger">*</span></label>
          <select name="supplier_id" class="form-select" required>
            <option value="">-- Choose Supplier --</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->id }}" {{ ($po && $po->supplier_id == $s->id) ? 'selected' : '' }}>
                {{ $s->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Receiving Godown / Location <span class="text-danger">*</span></label>
          <select name="location_id" class="form-select" required>
            @foreach($locations as $loc)
              <option value="{{ $loc->id }}" {{ ($po && $po->location_id == $loc->id) ? 'selected' : '' }}>
                {{ $loc->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Received Date <span class="text-danger">*</span></label>
          <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Freight / Carriage Charges (کرایہ مال) [FR-76]</label>
          <div class="input-group">
            <span class="input-group-text">Rs.</span>
            <input type="number" step="0.01" name="freight_charge" id="freightInput" class="form-control" value="0" min="0" oninput="calculateGrnTotals()">
          </div>
          <small class="text-muted" style="font-size: 0.7rem;">Allocated to product landed cost.</small>
        </div>
      </div>

      <!-- Inward Items Table -->
      <h6 class="fw-bold text-primary mb-2"><i class="ti ti-boxes me-1"></i> Inward Product Lots & Batches</h6>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle" id="grnItemsTable">
          <thead class="table-light">
            <tr>
              <th width="30%">Product <span class="text-danger">*</span></th>
              <th width="12%">Received Qty <span class="text-danger">*</span></th>
              <th width="14%">Unit Rate (Rs.) <span class="text-danger">*</span></th>
              <th width="15%">Batch Number</th>
              <th width="14%">Expiry Date</th>
              <th width="12%" class="text-end">Total Cost</th>
              <th width="3%"></th>
            </tr>
          </thead>
          <tbody id="grnTableBody">
            @if($po && $po->items->count() > 0)
              @foreach($po->items as $idx => $it)
                @php $pendingQty = max(1, $it->quantity - $it->received_quantity); @endphp
                <tr>
                  <td>
                    <input type="hidden" name="items[{{ $idx }}][po_item_id]" value="{{ $it->id }}">
                    <select name="items[{{ $idx }}][product_id]" class="form-select product-select" required>
                      <option value="{{ $it->product_id }}" selected>{{ $it->product->name }} ({{ $it->product->sku }})</option>
                    </select>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $idx }}][received_qty]" class="form-control qty-input" value="{{ $pendingQty }}" min="0.1" required oninput="calculateGrnTotals()">
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $idx }}][unit_cost]" class="form-control cost-input" value="{{ $it->unit_cost }}" required oninput="calculateGrnTotals()">
                  </td>
                  <td>
                    <input type="text" name="items[{{ $idx }}][batch_number]" class="form-control font-monospace" value="BATCH-{{ date('ymd') }}-{{ rand(10,99) }}">
                  </td>
                  <td>
                    <input type="date" name="items[{{ $idx }}][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                  </td>
                  <td class="text-end fw-bold line-total">Rs. 0.00</td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="removeGrnRow(this)"><i class="ti ti-trash"></i></button>
                  </td>
                </tr>
              @endforeach
            @else
              <tr>
                <td>
                  <select name="items[0][product_id]" class="form-select product-select" required onchange="setGrnCost(this, 0)">
                    <option value="">-- Select Product --</option>
                    @foreach($products as $p)
                      <option value="{{ $p->id }}" data-cost="{{ $p->purchase_cost }}">
                        {{ $p->name }} ({{ $p->sku }})
                      </option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <input type="number" step="0.01" name="items[0][received_qty]" class="form-control qty-input" value="100" min="0.1" required oninput="calculateGrnTotals()">
                </td>
                <td>
                  <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control cost-input" placeholder="0.00" required oninput="calculateGrnTotals()">
                </td>
                <td>
                  <input type="text" name="items[0][batch_number]" class="form-control font-monospace" value="BATCH-{{ date('ymd') }}-{{ rand(10,99) }}">
                </td>
                <td>
                  <input type="date" name="items[0][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                </td>
                <td class="text-end fw-bold line-total">Rs. 0.00</td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm text-danger p-0" onclick="removeGrnRow(this)"><i class="ti ti-trash"></i></button>
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addGrnRow()">
          <i class="ti ti-plus me-1"></i> Add Another Product Line
        </button>
        <div class="text-end">
          <div class="text-muted small">Total Landed Acquisition Cost (Goods + Freight):</div>
          <span class="fs-4 fw-bold text-success" id="grnTotalText">Rs. 0.00</span>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Receiving Inspection Remarks</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Vehicle number, driver details, packing conditions"></textarea>
      </div>

      <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <a href="{{ route('purchases.grn') }}" class="btn btn-light border px-4">Cancel</a>
        <button type="submit" class="btn btn-success px-4 fw-semibold">
          <i class="ti ti-check me-1"></i> Post GRN & Increase Stock
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let grnRowIndex = {{ $po ? $po->items->count() : 1 }};

  function setGrnCost(select) {
    const cost = select.options[select.selectedIndex].getAttribute('data-cost');
    const tr = select.closest('tr');
    tr.querySelector('.cost-input').value = cost || 0;
    calculateGrnTotals();
  }

  function addGrnRow() {
    const tbody = document.getElementById('grnTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="items[${grnRowIndex}][product_id]" class="form-select product-select" required onchange="setGrnCost(this)">
          <option value="">-- Select Product --</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" data-cost="{{ $p->purchase_cost }}">
              {{ $p->name }} ({{ $p->sku }})
            </option>
          @endforeach
        </select>
      </td>
      <td>
        <input type="number" step="0.01" name="items[${grnRowIndex}][received_qty]" class="form-control qty-input" value="50" min="0.1" required oninput="calculateGrnTotals()">
      </td>
      <td>
        <input type="number" step="0.01" name="items[${grnRowIndex}][unit_cost]" class="form-control cost-input" placeholder="0.00" required oninput="calculateGrnTotals()">
      </td>
      <td>
        <input type="text" name="items[${grnRowIndex}][batch_number]" class="form-control font-monospace" value="BATCH-${new Date().getTime().toString().slice(-6)}">
      </td>
      <td>
        <input type="date" name="items[${grnRowIndex}][expiry_date]" class="form-control" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
      </td>
      <td class="text-end fw-bold line-total">Rs. 0.00</td>
      <td class="text-center">
        <button type="button" class="btn btn-sm text-danger p-0" onclick="removeGrnRow(this)"><i class="ti ti-trash"></i></button>
      </td>`;
    tbody.appendChild(tr);
    grnRowIndex++;
  }

  function removeGrnRow(btn) {
    const tbody = document.getElementById('grnTableBody');
    if (tbody.rows.length > 1) {
      btn.closest('tr').remove();
      calculateGrnTotals();
    } else {
      alert("At least one product line must be received.");
    }
  }

  function calculateGrnTotals() {
    let subtotal = 0;
    document.querySelectorAll('#grnTableBody tr').forEach(tr => {
      const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
      const cost = parseFloat(tr.querySelector('.cost-input')?.value) || 0;
      const line = qty * cost;
      const lineCell = tr.querySelector('.line-total');
      if (lineCell) lineCell.innerText = "Rs. " + line.toLocaleString();
      subtotal += line;
    });

    const freight = parseFloat(document.getElementById('freightInput')?.value) || 0;
    const landedTotal = subtotal + freight;

    document.getElementById('grnTotalText').innerText = "Rs. " + landedTotal.toLocaleString();
  }

  document.addEventListener('DOMContentLoaded', calculateGrnTotals);
</script>
@endpush
