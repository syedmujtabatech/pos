<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - {{ $invoice->invoice_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 8px 12px;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .double-divider { border-top: 2px solid #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; font-size: 11px; }
        .logo-title { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .urdu-title { font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Arial', sans-serif; font-size: 15px; }
        .btn-print {
            background: #0d6efd; color: #fff; border: none; padding: 8px 16px; border-radius: 4px;
            font-family: sans-serif; font-size: 14px; cursor: pointer; margin-bottom: 12px;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa;">
        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt (80mm)</button>
        <button onclick="window.close()" style="padding: 8px 16px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;">Close</button>
    </div>

    <div class="text-center">
        <div class="logo-title">AL-NOOR TRADERS</div>
        <div class="urdu-title">النور ٹریڈرز - ہول سیل اینڈ ریٹیل</div>
        <div>Jodia Bazaar, Karachi, Pakistan</div>
        <div>Tel: 021-32415678 | Cell: 0300-1234567</div>
        <div>NTN: 1425367-8 | STRN: 17-00-1425-367-11</div>
    </div>

    <div class="double-divider"></div>

    <div>
        <div><strong>Bill No:</strong> {{ $invoice->invoice_number }}</div>
        <div><strong>Date:</strong> {{ $invoice->invoice_date->format('d-m-Y h:i A') }}</div>
        <div><strong>Counter:</strong> {{ $invoice->location->name ?? 'Retail Shop' }}</div>
        <div><strong>Cashier:</strong> {{ $invoice->creator->name ?? 'Staff' }}</div>
        @if($invoice->customer)
            <div><strong>Customer:</strong> {{ $invoice->customer->name }} ({{ $invoice->customer->phone ?? 'N/A' }})</div>
        @else
            <div><strong>Customer:</strong> Walk-in Retail Customer</div>
        @endif
        @if($invoice->salesman)
            <div><strong>Salesman:</strong> {{ $invoice->salesman->name }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Rate</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td colspan="4" style="padding-top: 4px;">
                        <strong>{{ $item->product->name ?? 'Product' }}</strong>
                        @if($item->batch_number)<br><small style="color: #444;">B: {{ $item->batch_number }}</small>@endif
                    </td>
                </tr>
                <tr style="border-bottom: 1px dotted #ccc;">
                    <td></td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }} {{ $item->unit_name ?? 'pcs' }}</td>
                    <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">Rs.{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal:</td>
            <td class="text-end">Rs. {{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
            <tr>
                <td>Discount:</td>
                <td class="text-end">- Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if($invoice->delivery_charges > 0)
            <tr>
                <td>Delivery / Freight:</td>
                <td class="text-end">+ Rs. {{ number_format($invoice->delivery_charges, 2) }}</td>
            </tr>
        @endif
        <tr style="font-weight: bold; font-size: 13px;">
            <td>NET PAYABLE:</td>
            <td class="text-end">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
        </tr>
        <tr style="border-top: 1px dashed #000;">
            <td>Paid Amount ({{ ucfirst($invoice->payment_type) }}):</td>
            <td class="text-end">Rs. {{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        @if($invoice->due_amount > 0)
            <tr style="font-weight: bold; color: #b00;">
                <td>Balance Due (Udhaar):</td>
                <td class="text-end">Rs. {{ number_format($invoice->due_amount, 2) }}</td>
            </tr>
            @if($invoice->customer)
                <tr>
                    <td>Total Customer Balance:</td>
                    <td class="text-end">Rs. {{ number_format($invoice->customer->current_balance, 2) }}</td>
                </tr>
            @endif
        @endif
    </table>

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 11px;">
        <div>*** THANK YOU FOR SHOPPING! ***</div>
        <div class="urdu-title" style="margin-top: 3px;">خریداری کا شکریہ - مال واپس یا تبدیل 3 دن میں ہوگا</div>
        <div style="font-size: 9px; margin-top: 5px; color: #555;">Software by Al-Noor Traders ERP System</div>
    </div>

</body>
</html>
