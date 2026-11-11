@extends('layouts.app')
@section('title', 'Barcode Label Generator')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <h4 class="fw-bold mb-1"><i class="ti ti-barcode text-primary me-1"></i> Barcode Label Generator (بارکوڈ پرنٹنگ)</h4>
    <p class="text-secondary small mb-0">Generate and print retail shelf & carton labels (FR-30)</p>
  </div>
  <button class="btn btn-primary" onclick="window.print()">
    <i class="ti ti-printer me-1"></i> Print Labels Sheet
  </button>
</div>

<!-- Selection Form -->
<div class="card mb-4 no-print shadow-sm">
  <div class="card-body p-3">
    <form action="{{ route('products.barcodes') }}" method="GET" class="row g-3 align-items-center">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Select Product to Print</label>
        <select name="product_id" class="form-select" onchange="this.form.submit()">
          <option value="">-- Choose Product --</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}" {{ (optional($selectedProduct)->id == $p->id) ? 'selected' : '' }}>
              {{ $p->name }} ({{ $p->sku }} - Rs. {{ $p->retail_price }})
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Number of Labels to Print</label>
        <input type="number" id="labelCountInput" class="form-control" value="12" min="1" max="100" onchange="renderLabels()">
      </div>
    </form>
  </div>
</div>

<!-- Printable Labels Grid -->
@if($selectedProduct)
  <div class="row g-3" id="labelsContainer">
    <!-- Rendered via JS -->
  </div>
@else
  <div class="text-center py-5 text-muted">
    <i class="ti ti-barcode fs-1 d-block mb-2 text-secondary opacity-50"></i>
    Please choose a product above to generate printable barcode stickers.
  </div>
@endif

@push('styles')
<style>
  .barcode-sticker {
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 10px;
    text-align: center;
    background: #fff;
    page-break-inside: avoid;
  }
  @media print {
    .barcode-sticker {
      border: 1px solid #000 !important;
      margin-bottom: 10px;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  function renderLabels() {
    @if($selectedProduct)
    const count = parseInt(document.getElementById('labelCountInput').value) || 12;
    const container = document.getElementById('labelsContainer');
    if (!container) return;

    let html = '';
    for (let i = 0; i < count; i++) {
      html += `
        <div class="col-6 col-md-4 col-lg-3">
          <div class="barcode-sticker shadow-sm">
            <small class="fw-bold d-block text-uppercase" style="font-size: 0.65rem;">Al-Noor Traders</small>
            <div class="fw-bold small text-truncate">{{ $selectedProduct->name }}</div>
            <div class="my-2 p-1 bg-light border d-inline-block rounded">
              <svg id="barcodeSvg_${i}" style="height: 35px; width: 120px;">
                <!-- Simulated barcode lines -->
                <rect x="0" y="0" width="2" height="35" fill="#000"/>
                <rect x="4" y="0" width="3" height="35" fill="#000"/>
                <rect x="9" y="0" width="1" height="35" fill="#000"/>
                <rect x="12" y="0" width="4" height="35" fill="#000"/>
                <rect x="18" y="0" width="2" height="35" fill="#000"/>
                <rect x="22" y="0" width="3" height="35" fill="#000"/>
                <rect x="27" y="0" width="1" height="35" fill="#000"/>
                <rect x="30" y="0" width="3" height="35" fill="#000"/>
                <rect x="35" y="0" width="2" height="35" fill="#000"/>
                <rect x="40" y="0" width="4" height="35" fill="#000"/>
                <rect x="46" y="0" width="1" height="35" fill="#000"/>
                <rect x="50" y="0" width="3" height="35" fill="#000"/>
                <rect x="55" y="0" width="2" height="35" fill="#000"/>
                <rect x="60" y="0" width="3" height="35" fill="#000"/>
                <rect x="65" y="0" width="4" height="35" fill="#000"/>
                <rect x="71" y="0" width="1" height="35" fill="#000"/>
                <rect x="75" y="0" width="3" height="35" fill="#000"/>
                <rect x="80" y="0" width="2" height="35" fill="#000"/>
                <rect x="85" y="0" width="3" height="35" fill="#000"/>
                <rect x="90" y="0" width="1" height="35" fill="#000"/>
                <rect x="93" y="0" width="4" height="35" fill="#000"/>
                <rect x="99" y="0" width="2" height="35" fill="#000"/>
                <rect x="103" y="0" width="3" height="35" fill="#000"/>
                <rect x="108" y="0" width="2" height="35" fill="#000"/>
                <rect x="112" y="0" width="3" height="35" fill="#000"/>
                <rect x="117" y="0" width="2" height="35" fill="#000"/>
              </svg>
              <div class="font-monospace text-center" style="font-size: 0.65rem;">{{ $selectedProduct->barcode }}</div>
            </div>
            <div class="fw-bold fs-6 text-primary">Rs. {{ number_format($selectedProduct->retail_price, 2) }}</div>
          </div>
        </div>`;
    }
    container.innerHTML = html;
    @endif
  }

  document.addEventListener('DOMContentLoaded', renderLabels);
</script>
@endpush
@endsection
