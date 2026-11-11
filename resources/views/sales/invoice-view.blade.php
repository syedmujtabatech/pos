@extends('layouts.app')
@section('title', 'Invoice Details - ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
  <div>
    <a href="{{ route('sales.wholesale') }}" class="text-decoration-none small text-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to Invoices
    </a>
    <h4 class="fw-bold mt-1 mb-0">Invoice: {{ $invoice->invoice_number }}</h4>
    <small class="text-secondary">Type: {{ ucfirst($invoice->invoice_type) }} • Date: {{ $invoice->invoice_date->format('d M Y') }}</small>
  </div>
  <div class="d-flex gap-2">
    <!-- WhatsApp Share Link (FR-91) -->
    @php
      $msg = urlencode("Al-Noor Traders Invoice {$invoice->invoice_number}\nCustomer: {$invoice->customer->shop_name}\nTotal: Rs. " . number_format($invoice->grand_total) . "\nPaid: Rs. " . number_format($invoice->paid_amount) . "\nDue Balance: Rs. " . number_format($invoice->due_amount) . "\nThank you for your business!");
      $waUrl = "https://wa.me/92" . ltrim(preg_replace('/[^0-9]/', '', $invoice->customer->phone), '0') . "?text={$msg}";
    @endphp
    <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-outline-success">
      <i class="ti ti-brand-whatsapp me-1"></i> Share on WhatsApp
    </a>

    <a href="{{ route('sales.print-thermal', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-dark">
      <i class="ti ti-receipt me-1"></i> 80mm Thermal
    </a>
    <a href="{{ route('sales.print-a4', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
      <i class="ti ti-printer me-1"></i> Print A4 Invoice
    </a>

    @if($invoice->status === 'posted')
      <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidInvoiceModal">
        <i class="ti ti-ban me-1"></i> Void Bill
      </button>
    @endif
  </div>
</div>

@if($invoice->status === 'voided')
  <div class="alert alert-danger shadow-sm mb-4">
    <div class="fw-bold"><i class="ti ti-alert-triangle me-1"></i> THIS INVOICE IS VOIDED</div>
    <div>Reason: {{ $invoice->void_reason }}</div>
    <small class="text-muted">Void approved and inventory restored to warehouse.</small>
  </div>
@endif

<!-- Invoice Details Card -->
<div class="card shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="row g-4">
      <div class="col-md-4">
        <span class="text-muted small d-block">Customer / Store</span>
        <h5 class="fw-bold mb-1">{{ $invoice->customer->shop_name }}</h5>
        <div class="text-secondary small">{{ $invoice->customer->name }} • {{ $invoice->customer->phone }}</div>
        <small class="text-muted d-block">{{ $invoice->customer->address }}</small>
      </div>
      <div class="col-md-4">
        <span class="text-muted small d-block">Salesman & Dispatch Point</span>
        <div class="fw-semibold">{{ $invoice->salesman ? $invoice->salesman->name : 'Counter Direct' }}</div>
        <small class="text-secondary">Location: {{ $invoice->location->name }}</small>
        <div class="mt-2">
          <span class="badge bg-light text-dark border">Payment: {{ strtoupper($invoice->payment_type) }}</span>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <span class="text-muted small d-block">Grand Total</span>
        <h3 class="fw-bold text-primary mb-1">Rs. {{ number_format($invoice->grand_total, 2) }}</h3>
        <div>
          <span class="text-success fw-semibold">Paid: Rs. {{ number_format($invoice->paid_amount, 2) }}</span> •
          <span class="text-danger fw-bold">Due: Rs. {{ number_format($invoice->due_amount, 2) }}</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Items Table -->
<div class="card shadow-sm">
  <div class="card-header bg-white py-3">
    <h6 class="mb-0 fw-bold">Billed Line Items</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>SKU</th>
          <th class="text-end">Quantity</th>
          <th class="text-end">Unit Price</th>
          <th class="text-end">Discount</th>
          <th class="text-end">Total Price</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $idx => $it)
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td class="fw-bold">{{ $it->product->name }}</td>
            <td><code>{{ $it->product->sku }}</code></td>
            <td class="text-end fw-semibold">{{ number_format($it->quantity) }}</td>
            <td class="text-end">Rs. {{ number_format($it->unit_price, 2) }}</td>
            <td class="text-end text-muted">{{ $it->discount > 0 ? 'Rs. ' . number_format($it->discount, 2) : '-' }}</td>
            <td class="text-end fw-bold text-dark">Rs. {{ number_format($it->total_price, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="6" class="text-end fw-semibold">Subtotal:</td>
          <td class="text-end fw-bold">Rs. {{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->invoice_discount > 0)
        <tr>
          <td colspan="6" class="text-end text-muted">Invoice Discount:</td>
          <td class="text-end text-danger">- Rs. {{ number_format($invoice->invoice_discount, 2) }}</td>
        </tr>
        @endif
        @if($invoice->delivery_charges > 0)
        <tr>
          <td colspan="6" class="text-end text-muted">Carriage / Delivery Freight:</td>
          <td class="text-end text-primary">+ Rs. {{ number_format($invoice->delivery_charges, 2) }}</td>
        </tr>
        @endif
        <tr class="fw-bold fs-6">
          <td colspan="6" class="text-end">Grand Total:</td>
          <td class="text-end text-primary">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Void Invoice Modal (FR-98) -->
<div class="modal fade" id="voidInvoiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('sales.void-invoice', $invoice->id) }}" method="POST">
        @csrf
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold"><i class="ti ti-ban me-1"></i> Void Bill Cancellation (FR-98)</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning small">
            <strong>Important:</strong> Posted bills cannot be deleted. Voiding will permanently cancel this bill, restore inventory to the warehouse, and reverse any customer receivables.
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mandatory Reason for Void (وجہ منسوخی) <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" placeholder="e.g. Order cancelled by customer before vehicle loading..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger fw-semibold">Confirm Bill Void</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
