<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice A4 - {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; font-size: 13px; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
        body {
            font-family: Arial, sans-serif;
            color: #222;
            background: #fff;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
            font-size: 13px;
        }
        .header-table { width: 100%; border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #0d6efd; margin-bottom: 3px; }
        .company-urdu { font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', Arial, sans-serif; font-size: 20px; color: #198754; }
        .invoice-title { font-size: 22px; font-weight: bold; text-align: right; color: #333; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px 10px; font-weight: bold; text-align: left; }
        .items-table td { border: 1px solid #cbd5e1; padding: 8px 10px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .totals-table { width: 350px; margin-left: auto; border-collapse: collapse; margin-bottom: 30px; }
        .totals-table td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        .signatures { width: 100%; margin-top: 60px; }
        .signature-box { border-top: 1px dashed #333; text-align: center; padding-top: 5px; width: 200px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: #d1e7dd; color: #0f5132; }
        .badge-unpaid { background: #f8d7da; color: #842029; }
        .badge-partial { background: #fff3cd; color: #664d03; }
        .btn-print {
            background: #0d6efd; color: #fff; border: none; padding: 10px 20px; border-radius: 6px;
            font-size: 15px; cursor: pointer; font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 25px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <button class="btn-print" onclick="window.print()">🖨️ Print Invoice (A4)</button>
        <button onclick="window.close()" style="padding: 10px 20px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; cursor: pointer; margin-left: 10px;">Close Window</button>
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">AL-NOOR TRADERS</div>
                <div class="company-urdu">النور ٹریڈرز - ہول سیل ڈسٹری بیوٹرز</div>
                <div style="color: #555; margin-top: 5px;">
                    Jodia Bazaar, Main Market, Karachi, Pakistan<br>
                    <strong>Phone:</strong> 021-32415678, 0300-1234567 | <strong>Email:</strong> sales@alnoortraders.pk<br>
                    <strong>NTN:</strong> 1425367-8 | <strong>STRN:</strong> 17-00-1425-367-11
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="invoice-title">WHOLESALE TAX INVOICE</div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 5px; color: #0d6efd;">{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 6px;">
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('d M, Y h:i A') }}<br>
                    <strong>Payment Terms:</strong> {{ ucfirst($invoice->payment_type) }}<br>
                    <strong>Status:</strong> 
                    <span class="badge {{ $invoice->payment_status === 'paid' ? 'badge-paid' : ($invoice->payment_status === 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                        {{ strtoupper($invoice->payment_status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                <strong style="color: #0d6efd; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Billed To (Customer Details):</strong>
                <div style="font-size: 15px; font-weight: bold; margin-top: 5px;">{{ $invoice->customer->name ?? 'Walk-in Retail Customer' }}</div>
                @if($invoice->customer)
                    <div><strong>Shop / Business:</strong> {{ $invoice->customer->shop_name ?? 'N/A' }}</div>
                    <div><strong>Area / Route:</strong> {{ $invoice->customer->area->name ?? 'Karachi' }}</div>
                    <div><strong>Address:</strong> {{ $invoice->customer->address ?? 'Karachi' }}</div>
                    <div><strong>Phone:</strong> {{ $invoice->customer->phone ?? 'N/A' }} | <strong>CNIC/NTN:</strong> {{ $invoice->customer->ntn ?? 'N/A' }}</div>
                @endif
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 46%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                <strong style="color: #0d6efd; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">Dispatch & Order Details:</strong>
                <div style="margin-top: 5px;"><strong>Dispatch Location:</strong> {{ $invoice->location->name ?? 'Main Godown' }}</div>
                <div><strong>Salesman:</strong> {{ $invoice->salesman->name ?? 'Direct Counter' }}</div>
                <div><strong>Delivery Challan:</strong> {{ $invoice->delivery_challan_id ? 'DC-'.$invoice->delivery_challan_id : 'Spot Delivery' }}</div>
                <div><strong>Invoice Creator:</strong> {{ $invoice->creator->name ?? 'System' }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">SKU / Code</th>
                <th style="width: 35%;">Product Description</th>
                <th style="width: 12%;" class="text-center">Batch / Exp</th>
                <th style="width: 10%;" class="text-center">Quantity</th>
                <th style="width: 12%;" class="text-end">Unit Rate (PKR)</th>
                <th style="width: 14%;" class="text-end">Total Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><code>{{ $item->product->sku ?? 'N/A' }}</code></td>
                    <td>
                        <strong>{{ $item->product->name ?? 'Item' }}</strong>
                        @if($item->product && $item->product->urdu_name)
                            <span style="font-family: 'Jameel Noori Nastaleeq', Arial; color: #555; margin-left: 5px;">({{ $item->product->urdu_name }})</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <small>{{ $item->batch_number ?? '-' }}</small><br>
                        <small style="color: #666;">{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('m/y') : '-' }}</small>
                    </td>
                    <td class="text-center">
                        <strong>{{ number_format($item->quantity, 0) }}</strong> {{ $item->unit_name ?? 'pcs' }}
                    </td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end"><strong>{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td><strong>Gross Subtotal:</strong></td>
            <td class="text-end">Rs. {{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
            <tr>
                <td>Discount:</td>
                <td class="text-end text-danger">- Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if($invoice->tax_amount > 0)
            <tr>
                <td>Sales Tax (GST):</td>
                <td class="text-end">+ Rs. {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
        @endif
        @if($invoice->delivery_charges > 0)
            <tr>
                <td>Delivery / Freight Charges:</td>
                <td class="text-end">+ Rs. {{ number_format($invoice->delivery_charges, 2) }}</td>
            </tr>
        @endif
        <tr style="background: #f1f5f9; font-size: 15px; font-weight: bold;">
            <td>Grand Total (Net):</td>
            <td class="text-end" style="color: #0d6efd;">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
        </tr>
        <tr>
            <td>Amount Received:</td>
            <td class="text-end text-success">Rs. {{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; background: #fff3cd;">
            <td>Current Bill Due:</td>
            <td class="text-end text-danger">Rs. {{ number_format($invoice->due_amount, 2) }}</td>
        </tr>
        @if($invoice->customer)
            <tr style="font-size: 12px; color: #444;">
                <td>Total Customer Ledger Balance:</td>
                <td class="text-end"><strong>Rs. {{ number_format($invoice->customer->current_balance, 2) }}</strong></td>
            </tr>
        @endif
    </table>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 6px; font-size: 11px; margin-top: 10px;">
        <strong>Terms & Conditions (شرائط و ضوابط):</strong><br>
        1. Claims regarding shortages or damages must be reported within 24 hours of delivery.<br>
        2. Payment for credit sales must be cleared within agreed credit days (Strictly 30 days max).<br>
        3. Goods once sold cannot be returned without original invoice and valid manager approval.
    </div>

    <table class="signatures">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div class="signature-box" style="margin: 0 auto;">Customer Received Signature</div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div class="signature-box" style="margin: 0 auto;">Dispatched By (Godown)</div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div class="signature-box" style="margin: 0 auto;">Authorized Signatory (Al-Noor)</div>
            </td>
        </tr>
    </table>

</body>
</html>
