<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Travel Order</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            color: #111827;
            font-size: 14px;
            line-height: 1.6;
            background: #f3f4f6;
        }

        .no-print {
            text-align: right;
            max-width: 820px;
            margin: 20px auto 10px auto;
        }

        .page {
            width: 820px;
            min-height: 1120px;
            margin: 0 auto 30px auto;
            padding: 70px 80px 135px 80px;
            position: relative;
            background: #ffffff;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
            overflow: hidden;
        }

        h2 {
            letter-spacing: 1px;
        }

        .record-box {
            position: absolute;
            left: 80px;
            bottom: 58px;
            width: 178px;
            padding: 0;
            border: none;
            border-radius: 0;
            background: transparent;
            line-height: 1.15;
        }

        .record-box div {
            font-size: 8px;
            display: flex;
            justify-content: space-between;
            gap: 4px;
        }

        .record-box span:first-child {
            font-weight: 700;
            white-space: nowrap;
        }

        .record-box span:last-child {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }

        .header {
            text-align: center;
            margin-bottom: 34px;
        }

        .header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .6px;
        }

        .header p {
            margin: 6px 0 20px 0;
            font-size: 17px;
            font-weight: 700;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 1px;
        }

        .date-row {
            margin-bottom: 22px;
        }

        .signature {
            margin-top: 70px;
            margin-bottom: 110px;
        }

        @media print {
            html,
            body {
                width: 210mm;
                min-height: 297mm;
                background: #ffffff !important;
            }

            body {
                font-size: 12px;
                line-height: 1.45;
            }

            .no-print {
                display: none !important;
            }

            .page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 22mm 25mm 32mm 25mm;
                max-width: none;
                position: relative;
                background: #ffffff;
                border: none !important;
                box-shadow: none !important;
                overflow: hidden;
            }

            .record-box {
                position: absolute;
                left: 25mm;
                bottom: 15mm;
            }

            .signature {
                margin-bottom: 95px;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: right; margin: 20px;">
        <button onclick="window.print()">Print</button>
    </div>
        <div class="page">
            <div class="record-box">
                <div>
                    <span>Document No.:</span>
                    <span>WMC-HR-TO-001</span>
                </div>
                <div>
                    <span>Revision No.:</span>
                    <span>00</span>
                </div>
                <div>
                    <span>Effective Date:</span>
                    <span>{{ $travelOrder->document_effective_date }}</span>
                </div>
                <div>
                    <span>Travel Order No.:</span>
                    <span>{{ $travelOrder->travel_order_number }}</span>
                </div>
            </div>

            <div class="header">
            <h3>WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</h3>
            <p>Iligan City</p>
            <h2>TRAVEL ORDER</h2>
        </div>

        <div class="date-row">
            <strong>Date:</strong>
            {{ $travelOrder->order_date?->format('F d, Y') ?? '____________________' }}
        </div>

        <p><strong>Employees Authorized to Travel:</strong></p>

        <ol>
            @foreach(($travelOrder->employees_authorized ?? []) as $employee)
                <li>{{ $employee }}</li>
            @endforeach
        </ol>

        <p>
            You are hereby authorized to travel to
            <strong>{{ $travelOrder->destination }}</strong>
            for the following purpose/s:
        </p>

        <ol type="a">
            <li>{{ $travelOrder->purpose_a }}</li>
        </ol>

        <p>
            <strong>Travel Date:</strong>
            {{ $travelOrder->travel_start_date?->format('F d, Y') }}
            -
            {{ $travelOrder->travel_end_date?->format('F d, Y') }}
        </p>

        <p>
            A brief Report of Accomplishment shall be submitted to this Office immediately upon your return.
        </p>

        <p>
            Travelling expenses and per diem incurred in connection with your travel shall be chargeable against
            the Wizmaster funds, subject to its availability and the usual accounting and auditing rules and regulations.
            Please attach receipts upon liquidation.
        </p>

        <p>Please be guided accordingly.</p>

        <div class="signature">
            <p>Prepared/Approved by:</p>

            <br><br>

            <strong>Manager</strong>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            // Optional auto print:
            // window.print();
        });
    </script>
</body>
</html>