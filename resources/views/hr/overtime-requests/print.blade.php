<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overtime Request Form</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; padding: 24px; font-size: 13px; }
        .page { max-width: 850px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 22px; }
        .company { font-size: 18px; font-weight: 700; letter-spacing: .3px; }
        .title { font-size: 22px; font-weight: 800; margin-top: 14px; text-transform: uppercase; }
        .section-title { font-weight: 800; background: #f3f4f6; border: 1px solid #111827; padding: 8px; margin-top: 16px; }
        .row { display: flex; border-left: 1px solid #111827; border-right: 1px solid #111827; }
        .cell { flex: 1; padding: 8px; border-bottom: 1px solid #111827; min-height: 36px; }
        .label { font-weight: 700; }
        .full { border-left: 1px solid #111827; border-right: 1px solid #111827; border-bottom: 1px solid #111827; padding: 8px; min-height: 70px; }
        table { width: 100%; border-collapse: collapse; margin-top: 0; }
        th, td { border: 1px solid #111827; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .note { margin-top: 18px; font-size: 12px; line-height: 1.45; }
        .signature-area { margin-top: 28px; display: flex; gap: 40px; }
        .signature { flex: 1; text-align: center; }
        .line { border-bottom: 1px solid #111827; height: 36px; margin-bottom: 6px; }
        .print-btn { margin-bottom: 16px; }
        .pre-line { white-space: pre-line; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
@php
    $formatExactDecimal = function ($value, $maxDecimals = 4, $minDecimals = 2) {
        $formatted = number_format((float) $value, $maxDecimals, '.', ',');
        if ($maxDecimals > $minDecimals) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            $decimalPosition = strrpos($formatted, '.');
            $currentDecimals = $decimalPosition === false ? 0 : strlen($formatted) - $decimalPosition - 1;
            if ($currentDecimals < $minDecimals) {
                $formatted .= ($decimalPosition === false ? '.' : '').str_repeat('0', $minDecimals - $currentDecimals);
            }
        }
        return $formatted;
    };

    $formatExactMoney = function ($value) use ($formatExactDecimal) {
        return '₱'.$formatExactDecimal($value, 4, 2);
    };
@endphp
    <div class="page">
        <button onclick="window.print()" class="print-btn">Print</button>

        <div class="header">
            <div class="company">WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</div>
            <div class="title">Overtime Request-and-Approval Form</div>
        </div>

        <div class="section-title">Part 1: Employee Overtime Duty</div>
        <div class="row">
            <div class="cell"><span class="label">Name:</span> {{ $overtimeRequest->requester?->full_name ?? $overtimeRequest->requester?->name ?? 'N/A' }}</div>
            <div class="cell"><span class="label">Date Filed:</span> {{ $overtimeRequest->date_filed?->format('M d, Y') ?? 'N/A' }}</div>
        </div>
        <div class="full">
            <div class="label">Purpose:</div>
            <div>{{ $overtimeRequest->reason }}</div>
        </div>

        <div class="section-title">Part 2: Overtime Rendered</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time Started</th>
                    <th>Time Ended</th>
                    @if($canViewOvertimeComputation)
                        <th>Total Hours</th>
                        <th>Night Differential Hours</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $overtimeRequest->overtime_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($overtimeRequest->time_started)->format('h:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($overtimeRequest->time_ended)->format('h:i A') }}</td>
                    @if($canViewOvertimeComputation)
                        <td>{{ $overtimeRequest->total_hours ? $formatExactDecimal($overtimeRequest->total_hours) : 'To be filled up by HR' }}</td>
                        <td>{{ $overtimeRequest->night_differential_hours ? $formatExactDecimal($overtimeRequest->night_differential_hours) : '0.00' }}</td>
                    @endif
                </tr>
            </tbody>
        </table>

        @if($canViewOvertimeComputation)
        <div class="row">
            <div class="cell"><span class="label">Overtime Type:</span> {{ $overtimeRequest->overtime_type_label }}</div>
            <div class="cell"><span class="label">Multiplier:</span> {{ $overtimeRequest->overtime_multiplier ? $formatExactDecimal($overtimeRequest->overtime_multiplier).'x' : 'To be filled up by HR' }}</div>
        </div>
        <div class="row">
            <div class="cell"><span class="label">Rate per Hour:</span> {{ $overtimeRequest->rate_per_hour ? $formatExactMoney($overtimeRequest->rate_per_hour) : 'To be filled up by HR' }}</div>
            <div class="cell"><span class="label">Rate per Day:</span> {{ $overtimeRequest->daily_rate ? $formatExactMoney($overtimeRequest->daily_rate) : 'N/A' }}</div>
        </div>
        <div class="row">
            <div class="cell"><span class="label">Regular/Base OT Amount:</span> {{ $overtimeRequest->overtime_amount ? $formatExactMoney($overtimeRequest->overtime_amount) : 'To be filled up by HR' }}</div>
            <div class="cell"><span class="label">Night Differential OT Amount:</span> {{ $overtimeRequest->night_differential_amount ? $formatExactMoney($overtimeRequest->night_differential_amount) : '₱0.00' }}</div>
        </div>
        <div class="row">
            <div class="cell"><span class="label">Date Paid:</span> {{ $overtimeRequest->date_paid?->format('M d, Y') ?? 'N/A' }}</div>
            <div class="cell"><span class="label">TOTAL:</span> {{ $overtimeRequest->total_amount ? $formatExactMoney($overtimeRequest->total_amount) : 'To be filled up by HR' }}</div>
        </div>
        <div class="full">
            <div class="label">Computation:</div>
            <div class="pre-line">{{ $overtimeRequest->computation ?? 'To be filled up by HR' }}</div>
        </div>
        @endif
        <div class="row">
            <div class="cell"><span class="label">Status:</span> {{ $overtimeRequest->status_label }}</div>
        </div>

        <div class="note">
            I hereby certify that the above overtime services were rendered as indicated.<br><br>
            Kindly submit the following requirements:<br>
            1. A screenshot showing your start and end times captured through GPS time tracking.<br>
            2. Screenshots or a summary/list of your work output as proof of accomplishment.
        </div>

        <div class="signature-area">
            <div class="signature">
                <div class="line"></div>
                Certified by Employee<br>
                {{ $overtimeRequest->employee_certified_name ?? '' }}<br>
                Date Submitted: {{ $overtimeRequest->date_submitted?->format('M d, Y') ?? 'N/A' }}
            </div>

            <div class="signature">
                <div class="line"></div>
                Approved by Department Head<br>
                {{ $overtimeRequest->departmentHeadReviewer?->full_name ?? '' }}<br>
                Date: {{ $overtimeRequest->department_head_reviewed_at?->format('M d, Y') ?? '' }}
            </div>
        </div>

        <div class="section-title">Final Approval</div>
        <div class="signature-area">
            <div class="signature">
                <div class="line"></div>
                Approved by Admin / Authorized Official<br>
                Name and Signature: {{ $overtimeRequest->adminReviewer?->full_name ?? '' }}<br>
                Date: {{ $overtimeRequest->admin_reviewed_at?->format('M d, Y') ?? '' }}
            </div>
        </div>

        <div class="note">
            <strong>Note:</strong> This form must be submitted to the HR Department within five (5) working days after the overtime has been rendered.
            Please be advised that overtime requests with incomplete documentation, non-compliance with prescribed procedures, or late submissions will be considered invalid and may not be processed.
        </div>
    </div>
</body>
</html>
