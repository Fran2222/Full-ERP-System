<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_no }}</title>
    
<style>
    * { box-sizing: border-box; }

    @page {
        size: A4;
        margin: 14mm;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #111827;
        background: #f5f7fb;
        margin: 0;
        padding: 24px;
        font-size: 12px;
        line-height: 1.35;
    }

    .print-toolbar {
        max-width: 980px;
        margin: 0 auto 16px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
    }

    .btn {
        min-width: 68px;
        border: 1px solid #3f5cff;
        color: #3f5cff;
        background: #ffffff;
        padding: 9px 15px;
        border-radius: 8px;
        text-decoration: none;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        transition: all .18s ease-in-out;
    }

    .btn:hover {
        background: #eef2ff;
    }

    .btn-primary {
        background: #3f5cff;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(63, 92, 255, .22);
    }

    .btn-primary:hover {
        background: #3048d8;
        color: #ffffff;
    }

    .sheet {
        max-width: 980px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 28px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        border-bottom: 2px solid #111827;
        padding-bottom: 14px;
        margin-bottom: 18px;
    }

    .brand h1 {
        font-size: 20px;
        margin: 0 0 4px;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .brand p,
    .meta p {
        margin: 2px 0;
        color: #4b5563;
    }

    .doc-title {
        text-align: right;
    }

    .doc-title h2 {
        font-size: 22px;
        margin: 0 0 6px;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .badge {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        line-height: 1;
        white-space: nowrap;
    }

    .badge-success { background: #dcfce7; color: #166534; }
    .badge-primary { background: #dbeafe; color: #1d4ed8; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-secondary { background: #e5e7eb; color: #374151; }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
        margin-bottom: 18px;
    }

    .box {
        border: 1px solid #d7dce5;
        border-radius: 9px;
        padding: 10px 12px;
        min-height: 52px;
    }

    .label {
        color: #6b7280;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 4px;
    }

    .value {
        font-weight: 700;
        font-size: 13px;
        color: #111827;
    }

    .section {
        margin-top: 18px;
    }

    .section h3 {
        font-size: 14px;
        margin: 0 0 8px;
        font-weight: 800;
        color: #111827;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    th {
        background: #eef1f5;
        color: #111827;
        text-align: left;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 8px;
        border: 1px solid #d7dce5;
        white-space: nowrap;
    }

    td {
        padding: 8px;
        border: 1px solid #e5e7eb;
        vertical-align: top;
    }

    tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .text-end {
        text-align: right;
    }

    .totals {
        margin-left: auto;
        width: 320px;
        margin-top: 12px;
    }

    .totals td {
        border: none;
        padding: 5px 0;
        background: transparent;
    }

    .signature-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
        margin-top: 44px;
    }

    .signature {
        text-align: center;
        padding-top: 32px;
        border-top: 1px solid #111827;
        font-weight: 700;
    }

    @media print {
        body {
            background: #ffffff;
            padding: 0;
        }

        .print-toolbar {
            display: none !important;
        }

        .sheet {
            max-width: none;
            border: 0;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
        }

        a {
            color: #111827;
            text-decoration: none;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>

</head>
<body>
@php
    $statusText = ucwords(str_replace('_', ' ', $purchaseOrder->status));
    $paymentStatusText = ucwords(str_replace('_', ' ', $paymentSummary['payment_status'] ?? 'unpaid'));
    $paymentStatusClass = match(strtolower(str_replace(' ', '_', $paymentStatusText))) {
        'paid' => 'badge-success',
        'partially_paid' => 'badge-primary',
        'unpaid' => 'badge-warning',
        default => 'badge-secondary',
    };
    $supplierName = $purchaseOrder->supplier?->supplier_name ?? $purchaseOrder->supplier?->name ?? '-';
    $contactName = $purchaseOrder->supplier?->contact_person ?? '-';
    $locationName = $purchaseOrder->location?->location_name ?? $purchaseOrder->location?->name ?? '-';
@endphp
<div class="print-toolbar">
    <button class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn" href="{{ route('purchasing.purchase-orders.show', $purchaseOrder) }}">Back</a>
</div>

<div class="sheet">
    <div class="header">
        <div class="brand">
            <h1>WIZMASTER CORPORATION</h1>
            <p>Procurement Department</p>
            <p>Purchase Order Voucher</p>
        </div>
        <div class="doc-title">
            <h2>PURCHASE ORDER</h2>
            <p><strong>{{ $purchaseOrder->po_no }}</strong></p>
            <span class="badge badge-success">{{ $statusText }}</span>
        </div>
    </div>

    <div class="grid">
        <div class="box"><div class="label">Supplier</div><div class="value">{{ $supplierName }}</div><div>{{ $contactName }}</div></div>
        <div class="box"><div class="label">Warehouse / Location</div><div class="value">{{ $locationName }}</div></div>
        <div class="box"><div class="label">PO Date</div><div class="value">{{ optional($purchaseOrder->po_date)->format('M d, Y') }}</div></div>
        <div class="box"><div class="label">Expected Date</div><div class="value">{{ optional($purchaseOrder->expected_date)->format('M d, Y') ?? '-' }}</div></div>
        <div class="box"><div class="label">Reference No.</div><div class="value">{{ $purchaseOrder->reference_no ?? '-' }}</div></div>
        <div class="box"><div class="label">Payment Terms</div><div class="value">{{ $purchaseOrder->payment_terms ?? '-' }}</div></div>
    </div>

    <div class="section">
        <h3>Items</h3>
        <table>
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th>U/M</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrder->items as $line)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $line->item?->item_name ?? $line->item?->name ?? '-' }}</td>
                        <td>{{ $line->description ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $line->quantity, 2) }}</td>
                        <td>{{ $line->item?->unit?->name ?? $line->item?->unit?->unit_name ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $line->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $line->tax_amount, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $line->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;">No items found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td class="text-end">{{ number_format((float) $purchaseOrder->subtotal, 2) }}</td></tr>
            <tr><td>Tax</td><td class="text-end">{{ number_format((float) $purchaseOrder->tax_amount, 2) }}</td></tr>
            <tr><td><strong>Total</strong></td><td class="text-end"><strong>{{ number_format((float) $purchaseOrder->total_amount, 2) }}</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Procurement Summary</h3>
        <div class="grid">
            <div class="box"><div class="label">Received Amount</div><div class="value">{{ number_format((float) $paymentSummary['received_amount'], 2) }}</div></div>
            <div class="box"><div class="label">Billed Amount</div><div class="value">{{ number_format((float) $paymentSummary['billed_amount'], 2) }}</div></div>
            <div class="box"><div class="label">Paid Amount</div><div class="value">{{ number_format((float) $paymentSummary['paid_amount'], 2) }}</div></div>
            <div class="box"><div class="label">Balance</div><div class="value">{{ number_format((float) $paymentSummary['payable_balance'], 2) }} <span class="badge {{ $paymentStatusClass }}">{{ $paymentStatusText }}</span></div></div>
        </div>
    </div>

    <div class="section">
        <h3>Receiving History</h3>
        <table>
            <thead><tr><th>Receiving No.</th><th>Date</th><th>Location</th><th class="text-end">Total Cost</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($receivings as $receiving)
                    <tr>
                        <td>{{ $receiving->receiving_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($receiving->received_date)->format('M d, Y') }}</td>
                        <td>{{ $receiving->location_name ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $receiving->total_cost, 2) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $receiving->status)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;">No receiving records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Bills / AP Vouchers</h3>
        <table>
            <thead><tr><th>Bill No.</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($purchaseOrder->bills as $bill)
                    <tr>
                        <td>{{ $bill->bill_no }}</td>
                        <td>{{ optional($bill->bill_date)->format('M d, Y') }}</td>
                        <td class="text-end">{{ number_format((float) $bill->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $bill->paid_amount, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $bill->balance, 2) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $bill->payment_status)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;">No bills found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-row">
        <div class="signature">Prepared By</div>
        <div class="signature">Checked By</div>
        <div class="signature">Approved By</div>
    </div>
</div>
</body>
</html>
