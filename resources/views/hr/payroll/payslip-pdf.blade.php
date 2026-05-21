<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip PDF</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th, td {
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            background: #e5e7eb;
        }

        .right {
            text-align: right;
        }

        .net-pay {
            font-size: 16px;
            font-weight: bold;
            background: #eef2ff;
        }

        .signature {
            margin-top: 50px;
            width: 100%;
        }

        .line {
            border-top: 1px solid #111827;
            text-align: center;
            padding-top: 6px;
            width: 220px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">Wizmaster Corporation</div>
        <div class="subtitle">Employee Payslip</div>
    </div>

    <table>
        <tr>
            <th>Employee</th>
            <td>
                {{ $item->employeeProfile->user->first_name ?? '' }}
                {{ $item->employeeProfile->user->last_name ?? '' }}
            </td>
            <th>Payroll Period</th>
            <td>
                {{ $payroll->period_from?->format('M d, Y') }}
                -
                {{ $payroll->period_to?->format('M d, Y') }}
            </td>
        </tr>
        <tr>
            <th>Position</th>
            <td>{{ $item->employeeProfile->position->name ?? '-' }}</td>
            <th>Department</th>
            <td>{{ $item->employeeProfile->user->department->name ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2" class="section-title">Attendance Summary</td>
        </tr>
        <tr>
            <td>Present Days</td>
            <td class="right">{{ $item->present_days }}</td>
        </tr>
        <tr>
            <td>Absent Days</td>
            <td class="right">{{ $item->absent_days }}</td>
        </tr>
        <tr>
            <td>Worked Hours</td>
            <td class="right">{{ number_format($item->worked_hours, 2) }}</td>
        </tr>
        <tr>
            <td>Late Minutes</td>
            <td class="right">{{ $item->late_minutes }}</td>
        </tr>
        <tr>
            <td>Undertime Minutes</td>
            <td class="right">{{ $item->undertime_minutes }}</td>
        </tr>
        <tr>
            <td>Overtime Hours</td>
            <td class="right">{{ number_format($item->overtime_hours, 2) }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2" class="section-title">Earnings</td>
        </tr>
        <tr>
            <td>Basic Pay</td>
            <td class="right">PHP {{ number_format($item->basic_pay, 2) }}</td>
        </tr>
        <tr>
            <td>Overtime Pay</td>
            <td class="right">PHP {{ number_format($item->overtime_pay, 2) }}</td>
        </tr>
        <tr>
            <td>Allowance</td>
            <td class="right">PHP {{ number_format($item->allowance, 2) }}</td>
        </tr>
        <tr>
            <th>Gross Pay</th>
            <th class="right">PHP {{ number_format($item->gross_pay, 2) }}</th>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2" class="section-title">Deductions</td>
        </tr>
        <tr>
            <td>Late Deduction</td>
            <td class="right">PHP {{ number_format($item->late_deduction, 2) }}</td>
        </tr>
        <tr>
            <td>Undertime Deduction</td>
            <td class="right">PHP {{ number_format($item->undertime_deduction, 2) }}</td>
        </tr>
        <tr>
            <td>Absence Deduction</td>
            <td class="right">PHP {{ number_format($item->absence_deduction, 2) }}</td>
        </tr>
        <tr>
            <td>SSS</td>
            <td class="right">PHP {{ number_format($item->sss, 2) }}</td>
        </tr>
        <tr>
            <td>PhilHealth</td>
            <td class="right">PHP {{ number_format($item->philhealth, 2) }}</td>
        </tr>
        <tr>
            <td>Pag-IBIG</td>
            <td class="right">PHP {{ number_format($item->pagibig, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="right">PHP {{ number_format($item->tax, 2) }}</td>
        </tr>
        <tr>
            <th>Total Deductions</th>
            <th class="right">PHP {{ number_format($item->total_deductions, 2) }}</th>
        </tr>
    </table>

    <table>
        <tr class="net-pay">
            <td>Net Pay</td>
            <td class="right">PHP {{ number_format($item->net_pay, 2) }}</td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td style="border: none;">
                <div class="line">Employee Signature</div>
            </td>
            <td style="border: none; text-align: right;">
                <div class="line" style="margin-left: auto;">Authorized Signature</div>
            </td>
        </tr>
    </table>
</body>
</html>