<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receiving Report - {{ $receiving->receiving_no }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f5f7fb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.35;
        }

        .print-toolbar {
            max-width: 920px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 64px;
            height: 34px;
            border-radius: 7px;
            border: 1px solid #3f5cff;
            background: #fff;
            color: #3f5cff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-primary {
            background: #3f5cff;
            color: #fff;
        }

        .sheet {
            max-width: 920px;
            margin: 0 auto;
            background: #fff;
            padding: 26px 30px 34px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }

        .header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            align-items: start;
            padding-bottom: 18px;
            margin-bottom: 16px;
            border-bottom: 2px solid #111827;
        }

        .company-title {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .company-sub {
            margin: 0;
            color: #374151;
            font-size: 11px;
            line-height: 1.25;
        }

        .document-title {
            text-align: right;
        }

        .document-title h1 {
            margin: 0 0 8px;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .document-no {
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-warning {
            background: #ffedd5;
            color: #c2410c;
        }

        .badge-muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 14px;
            margin-bottom: 14px;
        }

        .info-box {
            min-height: 54px;
            padding: 10px 12px;
            border: 1px solid #d7dce5;
            border-radius: 8px;
        }

        .label {
            display: block;
            margin-bottom: 4px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .value {
            color: #111827;
            font-size: 12px;
            font-weight: 800;
            word-break: break-word;
        }

        .section-title {
            margin: 15px 0 8px;
            font-size: 13px;
            font-weight: 900;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th {
            padding: 8px;
            border: 1px solid #d1d5db;
            background: #e5e7eb;
            color: #111827;
            font-size: 10px;
            font-weight: 900;
            text-align: left;
            text-transform: uppercase;
        }

        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: 900;
        }

        .text-muted {
            color: #6b7280;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 12px;
        }

        .summary-box {
            border: 1px solid #d7dce5;
            border-radius: 8px;
            padding: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            border-bottom: 1px solid #edf0f5;
        }

        .summary-row:last-child {
            border-bottom: 0;
        }

        .summary-total {
            margin-top: 6px;
            padding: 9px 10px;
            border-radius: 8px;
            background: #eef4ff;
            color: #315cf6;
            font-size: 13px;
            font-weight: 900;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 26px;
            margin-top: 38px;
        }

        .signature-line {
            padding-top: 9px;
            border-top: 1.5px solid #111827;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .sheet {
                max-width: 100%;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            a {
                color: #111827;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()" class="btn btn-primary">Print</button>
        <a href="{{ route('purchasing.receiving.show', $receiving->id) }}" class="btn">Back</a>
    </div>

    @php
        $status = strtolower((string) $receiving->status);

        $statusClass = match ($status) {
            'received', 'posted' => 'badge badge-success',
            'draft' => 'badge badge-warning',
            'cancelled', 'void', 'voided' => 'badge badge-muted',
            default => 'badge badge-muted',
        };

        $locationName = $receiving->location_name
            ?? $receiving->location_alt_name
            ?? '-';

        $receivedDate = $receiving->received_date
            ? \Carbon\Carbon::parse($receiving->received_date)->format('M d, Y')
            : '-';

        $postedBy = $receiving->received_by_name
            ?: ($receiving->received_by_email ?? '-');

        $totalQty = (float) $items->sum('quantity');
        $totalCost = (float) ($subtotal ?? $items->sum('total_cost'));
    @endphp

    <main class="sheet">
        <div class="header">
            <div>
                <h2 class="company-title">Wizmaster Corporation</h2>
                <p class="company-sub">Procurement Department</p>
                <p class="company-sub">Warehouse Receiving Report</p>
            </div>

            <div class="document-title">
                <h1>Receiving Report</h1>
                <div class="document-no">{{ $receiving->receiving_no }}</div>
                <span class="{{ $statusClass }}">
                    {{ ucwords(str_replace('_', ' ', $receiving->status)) }}
                </span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <span class="label">Supplier</span>
                <div class="value">{{ $receiving->supplier_name ?? '-' }}</div>
                <div class="text-muted">{{ $receiving->contact_person ?? '-' }}</div>
            </div>

            <div class="info-box">
                <span class="label">Warehouse / Location</span>
                <div class="value">{{ $locationName }}</div>
            </div>

            <div class="info-box">
                <span class="label">Receiving No.</span>
                <div class="value">{{ $receiving->receiving_no }}</div>
            </div>

            <div class="info-box">
                <span class="label">Received Date</span>
                <div class="value">{{ $receivedDate }}</div>
            </div>

            <div class="info-box">
                <span class="label">Reference No.</span>
                <div class="value">{{ $receiving->reference_no ?: '-' }}</div>
            </div>

            <div class="info-box">
                <span class="label">Posted By</span>
                <div class="value">{{ $postedBy }}</div>
            </div>
        </div>

        <div class="section-title">Supplier Details</div>
        <table>
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $receiving->contact_person ?? '-' }}</td>
                    <td>{{ $receiving->phone ?? '-' }}</td>
                    <td>{{ $receiving->email ?? '-' }}</td>
                    <td>{{ $receiving->supplier_address ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Received Items</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 42px;">#</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th class="text-end" style="width: 80px;">Qty</th>
                    <th style="width: 70px;">U/M</th>
                    <th class="text-end" style="width: 110px;">Unit Cost</th>
                    <th class="text-end" style="width: 120px;">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->item_name ?? $item->name ?? '-' }}</div>
                            <div class="text-muted">{{ $item->item_code ?? $item->code ?? '-' }}</div>
                        </td>
                        <td>{{ $item->remarks ?: '-' }}</td>
                        <td class="text-end fw-bold">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit_abbreviation ?? $item->unit_name ?? '-' }}</td>
                        <td class="text-end">{{ number_format((float) $item->unit_cost, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format((float) $item->total_cost, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No receiving items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary-grid">
            <div class="summary-box">
                <div class="section-title" style="margin-top: 0;">Receiving Notes</div>
                <div class="text-muted">
                    {{ $receiving->remarks ?: 'No remarks added for this receiving record.' }}
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Received Items</span>
                    <strong>{{ $items->count() }}</strong>
                </div>

                <div class="summary-row">
                    <span>Total Quantity</span>
                    <strong>{{ number_format($totalQty, 2) }}</strong>
                </div>

                <div class="summary-row summary-total">
                    <span>Total Cost</span>
                    <strong>{{ number_format($totalCost, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="signature-line">Prepared By</div>
            <div class="signature-line">Checked By</div>
            <div class="signature-line">Approved By</div>
        </div>
    </main>
</body>
</html>