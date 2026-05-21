<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            padding: 20px;
        }

        .payslip {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .section {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            font-size: 16px;
        }

        .btn-print {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="payslip">

    <button onclick="window.print()" class="btn-print">🖨 Print</button>

    <h2>Wizmaster Corporation</h2>
    <p>Payslip</p>

    <div class="section">
        <table>
            <tr>
                <td><strong>Employee:</strong></td>
                <td>
                    {{ $item->employeeProfile->user->first_name }}
                    {{ $item->employeeProfile->user->last_name }}
                </td>

                <td><strong>Period:</strong></td>
                <td>
                    {{ $payroll->period_from->format('M d') }} -
                    {{ $payroll->period_to->format('M d, Y') }}
                </td>
            </tr>

            <tr>
                <td><strong>Position:</strong></td>
                <td>{{ $item->employeeProfile->position->name ?? '-' }}</td>

                <td><strong>Department:</strong></td>
                <td>{{ $item->employeeProfile->user->department->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h4>Earnings</h4>
        <table>
            <tr>
                <td>Basic Pay</td>
                <td class="right">₱{{ number_format($item->basic_pay, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime Pay</td>
                <td class="right">₱{{ number_format($item->overtime_pay, 2) }}</td>
            </tr>
            <tr>
                <td>Allowance</td>
                <td class="right">₱{{ number_format($item->allowance, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h4>Deductions</h4>
        <table>
            <tr>
                <td>Late Deduction</td>
                <td class="right">₱{{ number_format($item->late_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Undertime Deduction</td>
                <td class="right">₱{{ number_format($item->undertime_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Absence Deduction</td>
                <td class="right">₱{{ number_format($item->absence_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>SSS</td>
                <td class="right">₱{{ number_format($item->sss, 2) }}</td>
            </tr>
            <tr>
                <td>PhilHealth</td>
                <td class="right">₱{{ number_format($item->philhealth, 2) }}</td>
            </tr>
            <tr>
                <td>Pag-IBIG</td>
                <td class="right">₱{{ number_format($item->pagibig, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="right">₱{{ number_format($item->tax, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr class="total">
                <td>Net Pay</td>
                <td class="right">₱{{ number_format($item->net_pay, 2) }}</td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>