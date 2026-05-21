<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Bill - {{ $bill->bill_no }}</title>
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
    $po = $bill->purchaseOrder;
    $supplierName = $bill->supplier?->supplier_name ?? $bill->supplier?->name ?? '-';
    $contactName = $bill->supplier?->contact_person ?? '-';
    $statusText = $bill->status === 'voided' ? 'Voided' : ucwords(str_replace('_', ' ', $bill->payment_status ?? 'unpaid'));
    $statusClass = match(strtolower(str_replace(' ', '_', $statusText))) {
        'paid' => 'badge-success',
        'partially_paid' => 'badge-primary',
        'unpaid' => 'badge-warning',
        'voided' => 'badge-danger',
        default => 'badge-secondary',
    };
@endphp

<div class="print-toolbar">
    <button class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn" href="{{ route('purchasing.bills.show', $bill) }}">Back</a>
</div>

<div class="sheet">
    <div class="header">
        <div class="brand">
            <h1>WIZMASTER CORPORATION</h1>
            <p>Procurement Department</p>
            <p>Accounts Payable Voucher</p>
        </div>
        <div class="doc-title">
            <h2>PURCHASE BILL / AP VOUCHER</h2>
            <p><strong>{{ $bill->bill_no }}</strong></p>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
        </div>
    </div>

    <div class="grid">
        <div class="box"><div class="label">Supplier</div><div class="value">{{ $supplierName }}</div><div>{{ $contactName }}</div></div>
        <div class="box"><div class="label">Purchase Order</div><div class="value">{{ $po?->po_no ?? '-' }}</div></div>
        <div class="box"><div class="label">Bill Date</div><div class="value">{{ optional($bill->bill_date)->format('M d, Y') }}</div></div>
        <div class="box"><div class="label">Due Date</div><div class="value">{{ optional($bill->due_date)->format('M d, Y') ?? '-' }}</div></div>
        <div class="box"><div class="label">Reference No.</div><div class="value">{{ $bill->reference_no ?? '-' }}</div></div>
        <div class="box"><div class="label">Journal Entry</div><div class="value">{{ $bill->journalEntry?->entry_no ?? '-' }}</div></div>
    </div>

    <div class="section">
        <h3>Bill Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-end">{{ number_format((float) $bill->total_amount, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $bill->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $bill->balance, 2) }}</td>
                    <td>{{ $statusText }}</td>
                    <td>{{ $bill->description ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Payment History</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment No.</th>
                    <th>Date</th>
                    <th>Paid Through</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bill->postedPayments as $payment)
                    <tr>
                        <td>{{ $payment->payment_no }}</td>
                        <td>{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                        <td>{{ $payment->bankAccount?->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ ucwords($payment->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;">No posted payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Journal Entry Lines</h3>
        <table>
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bill->journalEntry?->lines ?? [] as $line)
                    <tr>
                        <td>{{ $line->account?->code ?? '-' }}</td>
                        <td>{{ $line->account?->name ?? '-' }}</td>
                        <td>{{ $line->description ?? '-' }}</td>
                        <td class="text-end">{{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}</td>
                        <td class="text-end">{{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;">No journal lines found.</td></tr>
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
