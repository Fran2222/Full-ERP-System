<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Travel Order</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            font-size: 14px;
            line-height: 1.6;
        }

        .page {
            max-width: 820px;
            margin: 30px auto;
            padding: 40px;
        }

        h2 {
            letter-spacing: 1px;
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
        }

        @media print {
            .no-print {
                display: none;
            }

            .page {
                margin: 0;
                padding: 20px 40px;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: right; margin: 20px;">
        <button onclick="window.print()">Print</button>
    </div>
        <div class="page">
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