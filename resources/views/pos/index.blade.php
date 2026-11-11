@extends('layouts.app')
@section('title', 'POS Counter Terminal')

@section('content')
<div class="pos-container">
  <!-- Top Bar: Counter Status & Quick Actions -->
  <div class="card border-0 shadow-sm mb-3 bg-white">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-success p-2 rounded-circle"><i class="ti ti-check fs-6"></i></span>
          <div>
            <h6 class="mb-0 fw-bold text-dark">Retail POS Counter</h6>
            <small class="text-muted"><i class="ti ti-user me-1"></i>Cashier: <strong>{{ auth()->user()->name }}</strong></small>
          </div>
        </div>
        <span class="vr d-none d-sm-block"></span>
        <div class="d-none d-md-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">
            <i class="ti ti-calendar me-1"></i>Today: <strong>{{ now()->format('d M Y') }}</strong>
          </span>
          <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
            <i class="ti ti-receipt me-1"></i>Today's Sales: <strong>Rs. {{ number_format($todayRevenue) }}</strong> ({{ $todaySalesCount }} bills)
          </span>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <!-- Parked Sales Dropdown -->
        <div class="dropdown">
          <button class="btn btn-sm btn-outline-warning fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown" id="parkedBtn">
            <i class="ti ti-player-pause me-1"></i> Parked Sales (<span id="parkedCount">0</span>)
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="parkedList" style="min-width: 250px;">
            <li class="dropdown-header">Held / Parked Carts</li>
            <li id="noParkedMsg" class="px-3 py-2 text-muted small text-center">No parked sales.</li>
          </ul>
        </div>

        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="parkCurrentSale()" title="Hold current customer's cart to serve another customer">
          <i class="ti ti-hand-stop me-1"></i> Hold Sale
        </button>

        <a href="{{ route('pos.invoices') }}" class="btn btn-sm btn-outline-primary fw-semibold">
          <i class="ti ti-history me-1"></i> Invoices Log
        </a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- LEFT COLUMN: Product Catalog & Fast Tap Grid -->
    <div class="col-lg-7 col-xl-7">
      <div class="card border-0 shadow-sm h-100 d-flex flex-column bg-white">
        <!-- Search & Filter Header -->
        <div class="card-header bg-white border-bottom p-3">
          <div class="input-group mb-2">
            <span class="input-group-text bg-light border-end-0 text-primary"><i class="ti ti-scan fs-4"></i></span>
            <input type="text" id="barcodeInput" class="form-control form-control-lg border-start-0" placeholder="Scan Barcode or Search Product by Name (F2)..." autocomplete="off" autofocus>
            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()"><i class="ti ti-x"></i></button>
          </div>

          <!-- Category Pills Filter -->
          <div class="category-pills d-flex gap-1 overflow-auto pb-1" style="white-space: nowrap;">
            <button type="button" class="btn btn-sm btn-primary category-btn active" data-cat="all" onclick="filterCategory('all', this)">
              All Items ({{ count($products) }})
            </button>
            @foreach($categories as $cat)
              @php $count = collect($products)->where('category_id', $cat->id)->count(); @endphp
              @if($count > 0)
                <button type="button" class="btn btn-sm btn-outline-secondary category-btn" data-cat="{{ $cat->id }}" onclick="filterCategory({{ $cat->id }}, this)">
                  {{ $cat->name }} ({{ $count }})
                </button>
              @endif
            @endforeach
          </div>
        </div>

        <!-- Products Grid Container -->
        <div class="card-body p-3 overflow-auto" style="max-height: calc(100vh - 280px); min-height: 480px;">
          <div class="row g-2" id="productsGrid">
            @forelse($products as $p)
              <div class="col-6 col-sm-4 col-md-3 product-card-col" data-cat="{{ $p['category_id'] }}" data-name="{{ strtolower($p['name']) }}" data-barcode="{{ $p['barcode'] }}">
                <div class="card h-100 product-item-card border p-2 text-start position-relative shadow-sm" onclick="addToCart({{ $p['id'] }})">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="badge bg-light text-secondary text-truncate" style="max-width: 90px; font-size: 0.65rem;">
                      {{ $p['category_name'] }}
                    </span>
                    <span class="badge {{ $p['stock'] > 10 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}" style="font-size: 0.65rem;">
                      Stock: {{ (int)$p['stock'] }}
                    </span>
                  </div>

                  <div class="product-title fw-semibold text-dark mb-1" style="font-size: 0.85rem; min-height: 38px; line-height: 1.25;">
                    {{ $p['name'] }}
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-auto pt-1 border-top">
                    <span class="fw-bold text-success fs-6">Rs. {{ number_format($p['price']) }}</span>
                    <button type="button" class="btn btn-sm btn-primary p-1 rounded-circle" style="width: 26px; height: 26px;" title="Add to Bill">
                      <i class="ti ti-plus" style="font-size: 14px;"></i>
                    </button>
                  </div>
                </div>
              </div>
            @empty
              <div class="col-12 text-center py-5 text-muted">
                <i class="ti ti-package-off fs-1 mb-2 d-block"></i>
                <p>No products available in database.</p>
              </div>
            @endforelse
          </div>

          <div id="noProductMatch" class="text-center py-5 text-muted d-none">
            <i class="ti ti-search-off fs-1 mb-2 d-block"></i>
            <h6>No products match your search.</h6>
            <small>Try typing a different name or scanning barcode.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: Reactive Cart & Counter Checkout Terminal -->
    <div class="col-lg-5 col-xl-5">
      <div class="card border-0 shadow-sm h-100 d-flex flex-column bg-white">
        <!-- Customer Selection Bar -->
        <div class="card-header bg-white border-bottom p-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-bold text-secondary mb-0">
              <i class="ti ti-user me-1 text-primary"></i> Customer / Account:
            </label>
            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
              <i class="ti ti-plus me-1"></i> Add New Customer
            </button>
          </div>

          <div class="d-flex gap-2 align-items-center">
            <select id="customerSelect" class="form-select form-select-sm fw-semibold" onchange="onCustomerChange()">
              @foreach($customers as $c)
                <option value="{{ $c->id }}" data-phone="{{ $c->phone }}" data-balance="{{ (float)$c->current_balance }}" {{ $c->id == 6 || strtolower($c->name) == 'counter cash customer' ? 'selected' : '' }}>
                  {{ $c->name }} ({{ $c->phone }})
                </option>
              @endforeach
            </select>
          </div>

          <!-- Customer Info Badge -->
          <div id="customerInfoBadge" class="mt-2 p-2 bg-light rounded d-flex justify-content-between align-items-center small">
            <span class="text-muted"><i class="ti ti-phone me-1"></i> <span id="custPhoneDisplay">0300-0000000</span></span>
            <span>Khata Balance: <strong id="custBalanceDisplay" class="text-success">Rs. 0</strong></span>
          </div>
        </div>

        <!-- Cart Items Table -->
        <div class="card-body p-0 d-flex flex-column flex-grow-1 overflow-hidden">
          <div class="table-responsive flex-grow-1 overflow-auto" style="max-height: 250px; min-height: 180px;">
            <table class="table table-hover align-middle mb-0" id="cartTable">
              <thead class="table-light small text-uppercase text-secondary sticky-top">
                <tr>
                  <th style="width: 45%;">Product</th>
                  <th style="width: 25%;" class="text-center">Qty</th>
                  <th style="width: 20%;" class="text-end">Total</th>
                  <th style="width: 10%;" class="text-center"></th>
                </tr>
              </thead>
              <tbody id="cartItemsBody">
                <!-- Cart rows inserted dynamically via JS -->
              </tbody>
            </table>

            <div id="emptyCartNotice" class="text-center py-5 text-muted">
              <i class="ti ti-shopping-cart fs-1 text-black-50 mb-2 d-block"></i>
              <h6 class="fw-semibold">Counter Cart is Empty</h6>
              <small>Scan a barcode or tap products on the left to start billing.</small>
            </div>
          </div>

          <!-- Billing Calculation Breakdown -->
          <div class="p-3 border-top bg-light mt-auto">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span>Subtotal:</span>
              <span class="fw-semibold text-dark">Rs. <span id="subtotalDisplay">0</span></span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="small text-muted">Discount:</span>
              <div class="input-group input-group-sm" style="width: 130px;">
                <span class="input-group-text bg-white">Rs.</span>
                <input type="number" id="discountInput" class="form-control form-control-sm text-end" value="0" min="0" oninput="calculateTotals()">
              </div>
            </div>

            <hr class="my-2">

            <!-- Grand Total Display -->
            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-success text-white mb-2 shadow-sm">
              <span class="fs-6 fw-bold">Grand Total:</span>
              <span class="fs-4 fw-bold">Rs. <span id="grandTotalDisplay">0</span></span>
            </div>

            <!-- Payment Method Pills -->
            <div class="mb-2">
              <label class="small fw-bold text-secondary mb-1 d-block">Payment Method:</label>
              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="paymentType" id="payCash" value="cash" checked onchange="onPaymentTypeChange()">
                <label class="btn btn-outline-success btn-sm fw-bold" for="payCash">
                  <i class="ti ti-cash me-1"></i> Cash
                </label>

                <input type="radio" class="btn-check" name="paymentType" id="payCard" value="card" onchange="onPaymentTypeChange()">
                <label class="btn btn-outline-primary btn-sm fw-bold" for="payCard">
                  <i class="ti ti-credit-card me-1"></i> Card
                </label>

                <input type="radio" class="btn-check" name="paymentType" id="payOnline" value="online" onchange="onPaymentTypeChange()">
                <label class="btn btn-outline-info btn-sm fw-bold" for="payOnline">
                  <i class="ti ti-device-mobile me-1"></i> JazzCash/Bank
                </label>

                <input type="radio" class="btn-check" name="paymentType" id="payCredit" value="credit" onchange="onPaymentTypeChange()">
                <label class="btn btn-outline-danger btn-sm fw-bold" for="payCredit">
                  <i class="ti ti-book me-1"></i> Udhar (Khata)
                </label>
              </div>
            </div>

            <!-- Cash Tendered & Change Return Calculation (Visible when Cash selected) -->
            <div id="cashCalcArea">
              <div class="row g-2 align-items-center mb-2">
                <div class="col-6">
                  <label class="small text-muted fw-semibold">Cash Received:</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">Rs.</span>
                    <input type="number" id="cashReceivedInput" class="form-control text-end fw-bold fs-6" value="0" min="0" oninput="calculateChange()">
                  </div>
                </div>
                <div class="col-6 text-end">
                  <label class="small text-muted fw-semibold">Return Change:</label>
                  <div class="h5 fw-bold text-primary mb-0">Rs. <span id="changeDueDisplay">0</span></div>
                </div>
              </div>

              <!-- Quick Cash Tender Pills -->
              <div class="d-flex gap-1 mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill py-1 small" onclick="setExactCash()">Exact</button>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill py-1 small" onclick="addCashAmount(500)">500</button>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill py-1 small" onclick="addCashAmount(1000)">1000</button>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill py-1 small" onclick="addCashAmount(5000)">5000</button>
              </div>
            </div>

            <!-- Checkout Action Buttons -->
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-danger btn-sm px-3" onclick="clearCart()" title="Cancel / Clear Cart">
                <i class="ti ti-trash"></i>
              </button>
              <button type="button" id="checkoutBtn" class="btn btn-success btn-lg flex-grow-1 fw-bold shadow-sm" onclick="processCheckout()" disabled>
                <i class="ti ti-printer me-2"></i> COMPLETE SALE &amp; PRINT (F8)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 1. THERMAL RECEIPT MODAL (80mm Auto-Print) -->
<!-- ========================================== -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header py-2 bg-dark text-white">
        <h6 class="modal-title fw-bold"><i class="ti ti-receipt me-1"></i> Customer Receipt</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">
        <!-- Thermal Slip Paper Style Container -->
        <div id="thermalReceiptContent" class="receipt-paper p-3 bg-white border shadow-sm mx-auto font-monospace" style="font-size: 12px; color: #000; line-height: 1.35;">
          <!-- Header -->
          <div class="text-center mb-2">
            <h5 class="fw-bold mb-0 text-dark">{{ $settings['store_name'] }}</h5>
            <div class="small">{{ $settings['tagline'] }}</div>
            <div style="font-size: 11px;">{{ $settings['address'] }}</div>
            <div style="font-size: 11px;">Tel: {{ $settings['phone'] }}</div>
          </div>

          <div class="receipt-divider border-top border-dashed my-2"></div>

          <!-- Invoice Details -->
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Inv #: <strong id="rcptInvNum">POS-0000</strong></span>
            <span id="rcptDateTime">11/11/2026, 04:00 PM</span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Cashier: <strong id="rcptCashier">{{ auth()->user()->name }}</strong></span>
            <span>Pay: <strong id="rcptPayType">CASH</strong></span>
          </div>
          <div style="font-size: 11px;">
            Customer: <strong id="rcptCustomer">Walk-in Customer</strong>
          </div>

          <div class="receipt-divider border-top border-dashed my-2"></div>

          <!-- Items Table -->
          <table class="w-100 mb-2" style="font-size: 11px;">
            <thead>
              <tr class="border-bottom">
                <th class="text-start pb-1">Item</th>
                <th class="text-center pb-1">Qty</th>
                <th class="text-end pb-1">Price</th>
                <th class="text-end pb-1">Total</th>
              </tr>
            </thead>
            <tbody id="rcptItemsBody">
              <!-- Dynamically populated -->
            </tbody>
          </table>

          <div class="receipt-divider border-top border-dashed my-2"></div>

          <!-- Totals -->
          <div class="d-flex justify-content-between" style="font-size: 11px;">
            <span>Subtotal:</span>
            <span>Rs. <span id="rcptSubtotal">0</span></span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;" id="rcptDiscountRow">
            <span>Discount:</span>
            <span>- Rs. <span id="rcptDiscount">0</span></span>
          </div>
          <div class="d-flex justify-content-between fw-bold fs-6 border-top border-bottom py-1 my-1">
            <span>TOTAL AMOUNT:</span>
            <span>Rs. <span id="rcptGrandTotal">0</span></span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;" id="rcptTenderRow">
            <span>Cash Tendered:</span>
            <span>Rs. <span id="rcptCashReceived">0</span></span>
          </div>
          <div class="d-flex justify-content-between" style="font-size: 11px;" id="rcptChangeRow">
            <span>Change Returned:</span>
            <span>Rs. <span id="rcptChangeDue">0</span></span>
          </div>

          <div class="receipt-divider border-top border-dashed my-2"></div>

          <!-- Footer Message -->
          <div class="text-center" style="font-size: 10px;">
            <div>{{ $settings['footer_note'] }}</div>
            <div class="mt-1 text-muted">Powered by POS System</div>
            <div class="mt-2 fw-bold text-center" style="letter-spacing: 2px;">*** THANK YOU ***</div>
          </div>
        </div>
      </div>
      <div class="modal-footer p-2 bg-white d-flex justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="ti ti-plus me-1"></i> New Sale
        </button>
        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="printReceipt()">
          <i class="ti ti-printer me-1"></i> Print Receipt
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 2. QUICK ADD CUSTOMER MODAL                 -->
<!-- ========================================== -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header py-2 bg-primary text-white">
        <h6 class="modal-title fw-bold"><i class="ti ti-user-plus me-1"></i> Quick Add Customer</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <form id="quickCustomerForm" onsubmit="saveQuickCustomer(event)">
          <div class="mb-2">
            <label class="form-label small fw-semibold">Customer Full Name *</label>
            <input type="text" id="custNameInput" class="form-control form-control-sm" required placeholder="e.g. Tariq Mehmood">
          </div>
          <div class="mb-2">
            <label class="form-label small fw-semibold">Mobile Number *</label>
            <input type="text" id="custPhoneInput" class="form-control form-control-sm" required placeholder="0300-1234567">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Udhar / Credit Limit (Rs.)</label>
            <input type="number" id="custCreditLimitInput" class="form-control form-control-sm" value="50000" min="0">
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-sm fw-bold">
              <i class="ti ti-check me-1"></i> Save &amp; Select Customer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* Modern POS Styles */
  .product-item-card {
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    background: #fff;
    border-radius: 8px;
  }
  .product-item-card:hover {
    transform: translateY(-2px);
    border-color: #0d6efd !important;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15) !important;
  }
  .product-item-card:active {
    transform: scale(0.98);
  }
  .category-btn {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
  }
  .receipt-paper {
    font-family: 'Courier New', Courier, monospace;
    border: 1px dashed #ccc;
  }
  @media print {
    body * {
      visibility: hidden;
    }
    #thermalReceiptContent, #thermalReceiptContent * {
      visibility: visible;
    }
    #thermalReceiptContent {
      position: absolute;
      left: 0;
      top: 0;
      width: 80mm;
      margin: 0;
      padding: 5mm;
      border: none !important;
      box-shadow: none !important;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  // Products Data Loaded from Server
  const ALL_PRODUCTS = @json($products);
  let CART = [];
  let PARKED_SALES = JSON.parse(localStorage.getItem('pos_parked_sales') || '[]');

  document.addEventListener('DOMContentLoaded', () => {
    updateParkedBadge();
    onCustomerChange();

    // Fast Barcode Scanner Listener (F2 to focus, Enter to add)
    const barcodeInput = document.getElementById('barcodeInput');
    barcodeInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleBarcodeSubmit(barcodeInput.value.trim());
      }
    });

    // Keyboard Shortcuts: F2 for Search, F8 for Checkout
    document.addEventListener('keydown', (e) => {
      if (e.key === 'F2') {
        e.preventDefault();
        barcodeInput.focus();
        barcodeInput.select();
      } else if (e.key === 'F8') {
        e.preventDefault();
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (!checkoutBtn.disabled) {
          processCheckout();
        }
      }
    });

    // Live search filter
    barcodeInput.addEventListener('input', () => {
      filterProductsList();
    });
  });

  // 1. ADD TO CART
  function addToCart(productId) {
    const prod = ALL_PRODUCTS.find(p => p.id === productId);
    if (!prod) return;

    const existing = CART.find(item => item.product_id === productId);
    if (existing) {
      existing.quantity += 1;
    } else {
      CART.push({
        product_id: prod.id,
        name: prod.name,
        price: prod.price,
        cost: prod.cost,
        quantity: 1
      });
    }

    renderCart();
  }

  // 2. RENDER CART
  function renderCart() {
    const tbody = document.getElementById('cartItemsBody');
    const emptyNotice = document.getElementById('emptyCartNotice');
    const checkoutBtn = document.getElementById('checkoutBtn');

    tbody.innerHTML = '';

    if (CART.length === 0) {
      emptyNotice.classList.remove('d-none');
      checkoutBtn.disabled = true;
      calculateTotals();
      return;
    }

    emptyNotice.classList.add('d-none');
    checkoutBtn.disabled = false;

    CART.forEach((item, index) => {
      const lineTotal = item.quantity * item.price;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;">${item.name}</div>
          <div class="small text-muted">Rs. ${item.price.toLocaleString()}</div>
        </td>
        <td class="text-center">
          <div class="input-group input-group-sm d-inline-flex" style="width: 95px;">
            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${index}, -1)">-</button>
            <input type="number" class="form-control form-control-sm text-center p-0" value="${item.quantity}" min="1" onchange="setQty(${index}, this.value)">
            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${index}, 1)">+</button>
          </div>
        </td>
        <td class="text-end fw-bold text-dark">
          Rs. ${lineTotal.toLocaleString()}
        </td>
        <td class="text-center">
          <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${index})" title="Remove item">
            <i class="ti ti-x"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    calculateTotals();
  }

  function updateQty(index, delta) {
    if (CART[index]) {
      CART[index].quantity = Math.max(1, CART[index].quantity + delta);
      renderCart();
    }
  }

  function setQty(index, val) {
    const q = parseFloat(val);
    if (CART[index]) {
      CART[index].quantity = isNaN(q) || q < 1 ? 1 : q;
      renderCart();
    }
  }

  function removeFromCart(index) {
    CART.splice(index, 1);
    renderCart();
  }

  function clearCart() {
    if (CART.length > 0 && confirm('Are you sure you want to clear current cart?')) {
      CART = [];
      document.getElementById('discountInput').value = 0;
      renderCart();
    }
  }

  // 3. TOTALS & CHANGE CALCULATION
  function calculateTotals() {
    const subtotal = CART.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount);

    document.getElementById('subtotalDisplay').textContent = subtotal.toLocaleString();
    document.getElementById('grandTotalDisplay').textContent = grandTotal.toLocaleString();

    // Default cash received to grandTotal if cash received is 0 or matches previous
    const cashInput = document.getElementById('cashReceivedInput');
    if (parseFloat(cashInput.value) <= 0 || cashInput.dataset.autoExact === 'true') {
      cashInput.value = grandTotal;
      cashInput.dataset.autoExact = 'true';
    }

    calculateChange();
  }

  function calculateChange() {
    const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').textContent.replace(/,/g, '')) || 0;
    const cashReceived = parseFloat(document.getElementById('cashReceivedInput').value) || 0;
    const change = Math.max(0, cashReceived - grandTotal);
    document.getElementById('changeDueDisplay').textContent = change.toLocaleString();
  }

  function setExactCash() {
    const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').textContent.replace(/,/g, '')) || 0;
    document.getElementById('cashReceivedInput').value = grandTotal;
    document.getElementById('cashReceivedInput').dataset.autoExact = 'true';
    calculateChange();
  }

  function addCashAmount(amount) {
    const input = document.getElementById('cashReceivedInput');
    input.value = amount;
    input.dataset.autoExact = 'false';
    calculateChange();
  }

  function onPaymentTypeChange() {
    const type = document.querySelector('input[name="paymentType"]:checked').value;
    const cashArea = document.getElementById('cashCalcArea');

    if (type === 'cash') {
      cashArea.classList.remove('d-none');
    } else {
      cashArea.classList.add('d-none');
    }
  }

  function onCustomerChange() {
    const sel = document.getElementById('customerSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt) return;

    const phone = opt.dataset.phone || 'N/A';
    const balance = parseFloat(opt.dataset.balance || 0);

    document.getElementById('custPhoneDisplay').textContent = phone;
    const balEl = document.getElementById('custBalanceDisplay');
    balEl.textContent = 'Rs. ' + balance.toLocaleString();

    if (balance > 0) {
      balEl.className = 'text-danger fw-bold';
    } else {
      balEl.className = 'text-success fw-bold';
    }
  }

  // 4. BARCODE & SEARCH FILTER
  function handleBarcodeSubmit(query) {
    if (!query) return;

    // Check exact barcode match first
    const exact = ALL_PRODUCTS.find(p => p.barcode === query || p.sku === query);
    if (exact) {
      addToCart(exact.id);
      document.getElementById('barcodeInput').value = '';
      filterProductsList();
      return;
    }

    // Check if 1 product matches search
    const matches = ALL_PRODUCTS.filter(p => p.name.toLowerCase().includes(query.toLowerCase()));
    if (matches.length === 1) {
      addToCart(matches[0].id);
      document.getElementById('barcodeInput').value = '';
      filterProductsList();
      return;
    }
  }

  function clearSearch() {
    document.getElementById('barcodeInput').value = '';
    filterProductsList();
    document.getElementById('barcodeInput').focus();
  }

  function filterCategory(catId, btn) {
    document.querySelectorAll('.category-btn').forEach(b => {
      b.classList.remove('btn-primary', 'active');
      b.classList.add('btn-outline-secondary');
    });
    btn.classList.add('btn-primary', 'active');
    btn.classList.remove('btn-outline-secondary');

    filterProductsList();
  }

  function filterProductsList() {
    const activeCatBtn = document.querySelector('.category-btn.active');
    const selectedCat = activeCatBtn ? activeCatBtn.dataset.cat : 'all';
    const query = document.getElementById('barcodeInput').value.toLowerCase().trim();

    const cols = document.querySelectorAll('.product-card-col');
    let visibleCount = 0;

    cols.forEach(col => {
      const colCat = col.dataset.cat;
      const colName = col.dataset.name;
      const colBarcode = col.dataset.barcode ? col.dataset.barcode.toLowerCase() : '';

      const matchCat = (selectedCat === 'all' || colCat === selectedCat);
      const matchSearch = (!query || colName.includes(query) || colBarcode.includes(query));

      if (matchCat && matchSearch) {
        col.classList.remove('d-none');
        visibleCount++;
      } else {
        col.classList.add('d-none');
      }
    });

    const noMatch = document.getElementById('noProductMatch');
    if (visibleCount === 0) {
      noMatch.classList.remove('d-none');
    } else {
      noMatch.classList.add('d-none');
    }
  }

  // 5. PARK / HOLD SALES (Multi-customer Queue)
  function parkCurrentSale() {
    if (CART.length === 0) {
      alert('Cart is empty. Nothing to park.');
      return;
    }

    const customerSel = document.getElementById('customerSelect');
    const custName = customerSel.options[customerSel.selectedIndex].text;

    const parkedItem = {
      id: Date.now(),
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      customer_id: customerSel.value,
      customer_name: custName,
      cart: [...CART],
      discount: document.getElementById('discountInput').value
    };

    PARKED_SALES.push(parkedItem);
    localStorage.setItem('pos_parked_sales', JSON.stringify(PARKED_SALES));

    CART = [];
    document.getElementById('discountInput').value = 0;
    renderCart();
    updateParkedBadge();

    alert(`Cart parked for "${custName}". You can resume it anytime!`);
  }

  function updateParkedBadge() {
    document.getElementById('parkedCount').textContent = PARKED_SALES.length;
    const list = document.getElementById('parkedList');

    // Remove old items
    list.querySelectorAll('.parked-entry').forEach(e => e.remove());

    const noMsg = document.getElementById('noParkedMsg');
    if (PARKED_SALES.length === 0) {
      noMsg.classList.remove('d-none');
    } else {
      noMsg.classList.add('d-none');
      PARKED_SALES.forEach((p, idx) => {
        const total = p.cart.reduce((s, it) => s + (it.quantity * it.price), 0);
        const li = document.createElement('li');
        li.className = 'parked-entry border-bottom px-3 py-2 d-flex justify-content-between align-items-center';
        li.innerHTML = `
          <div>
            <div class="fw-semibold text-dark small">${p.customer_name}</div>
            <div class="text-muted" style="font-size: 11px;">${p.cart.length} items &bull; Rs. ${total.toLocaleString()} (${p.time})</div>
          </div>
          <button class="btn btn-sm btn-primary py-0 px-2 small" onclick="resumeParkedSale(${idx})">Resume</button>
        `;
        list.appendChild(li);
      });
    }
  }

  function resumeParkedSale(index) {
    if (CART.length > 0 && !confirm('Current cart will be replaced. Proceed?')) {
      return;
    }

    const item = PARKED_SALES.splice(index, 1)[0];
    localStorage.setItem('pos_parked_sales', JSON.stringify(PARKED_SALES));

    CART = item.cart;
    document.getElementById('customerSelect').value = item.customer_id;
    document.getElementById('discountInput').value = item.discount || 0;

    onCustomerChange();
    renderCart();
    updateParkedBadge();
  }

  // 6. PROCESS CHECKOUT (AJAX TO BACKEND)
  async function processCheckout() {
    if (CART.length === 0) return;

    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Sale...';

    const customerId = document.getElementById('customerSelect').value;
    const paymentType = document.querySelector('input[name="paymentType"]:checked').value;
    const subtotal = CART.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount);
    const cashReceived = parseFloat(document.getElementById('cashReceivedInput').value) || grandTotal;

    const payload = {
      customer_id: customerId,
      payment_type: paymentType,
      items: CART.map(it => ({
        product_id: it.product_id,
        quantity: it.quantity,
        unit_price: it.price
      })),
      subtotal: subtotal,
      discount: discount,
      tax: 0,
      grand_total: grandTotal,
      cash_received: cashReceived
    };

    try {
      const res = await fetch("{{ route('pos.checkout') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (data.success) {
        showReceiptModal(data.invoice);

        // Reset cart for next sale
        CART = [];
        document.getElementById('discountInput').value = 0;
        document.getElementById('cashReceivedInput').value = 0;
        renderCart();
      } else {
        alert('Checkout error: ' + (data.message || 'Please check items and try again.'));
      }
    } catch (err) {
      alert('Network or server error during checkout: ' + err.message);
    } finally {
      checkoutBtn.disabled = false;
      checkoutBtn.innerHTML = '<i class="ti ti-printer me-2"></i> COMPLETE SALE &amp; PRINT (F8)';
    }
  }

  // 7. SHOW THERMAL RECEIPT & PRINT
  function showReceiptModal(inv) {
    document.getElementById('rcptInvNum').textContent = inv.invoice_number;
    document.getElementById('rcptDateTime').textContent = inv.date_time;
    document.getElementById('rcptCustomer').textContent = inv.customer_name;
    document.getElementById('rcptCashier').textContent = inv.cashier_name;
    document.getElementById('rcptPayType').textContent = inv.payment_type;

    const tbody = document.getElementById('rcptItemsBody');
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

    document.getElementById('rcptSubtotal').textContent = inv.subtotal.toLocaleString();
    document.getElementById('rcptDiscount').textContent = inv.discount.toLocaleString();
    document.getElementById('rcptGrandTotal').textContent = inv.grand_total.toLocaleString();
    document.getElementById('rcptCashReceived').textContent = inv.cash_received.toLocaleString();
    document.getElementById('rcptChangeDue').textContent = inv.change_due.toLocaleString();

    if (inv.discount > 0) {
      document.getElementById('rcptDiscountRow').classList.remove('d-none');
    } else {
      document.getElementById('rcptDiscountRow').classList.add('d-none');
    }

    if (inv.payment_type === 'CASH') {
      document.getElementById('rcptTenderRow').classList.remove('d-none');
      document.getElementById('rcptChangeRow').classList.remove('d-none');
    } else {
      document.getElementById('rcptTenderRow').classList.add('d-none');
      document.getElementById('rcptChangeRow').classList.add('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
  }

  function printReceipt() {
    window.print();
  }

  // 8. QUICK CUSTOMER CREATION
  async function saveQuickCustomer(e) {
    e.preventDefault();
    const name = document.getElementById('custNameInput').value.trim();
    const phone = document.getElementById('custPhoneInput').value.trim();
    const limit = document.getElementById('custCreditLimitInput').value;

    try {
      const res = await fetch("{{ route('pos.quick-customer') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name, phone, credit_limit: limit })
      });

      const data = await res.json();
      if (data.success) {
        // Add option to select
        const sel = document.getElementById('customerSelect');
        const opt = document.createElement('option');
        opt.value = data.customer.id;
        opt.textContent = `${data.customer.name} (${data.customer.phone})`;
        opt.dataset.phone = data.customer.phone;
        opt.dataset.balance = data.customer.current_balance;
        sel.appendChild(opt);
        sel.value = data.customer.id;

        onCustomerChange();

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
        document.getElementById('quickCustomerForm').reset();
      } else {
        alert('Error: ' + data.message);
      }
    } catch (err) {
      alert('Error adding customer: ' + err.message);
    }
  }
</script>
@endpush
