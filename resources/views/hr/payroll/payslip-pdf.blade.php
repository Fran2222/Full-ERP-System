<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>

    <style>
        :root {
            --wmc-blue: #0f3f8f;
            --wmc-blue-2: #1f6feb;
            --wmc-red: #d9232e;
            --wmc-ink: #0f172a;
            --wmc-muted: #64748b;
            --wmc-line: #dbe3ef;
            --wmc-soft: #f4f7fb;
            --wmc-green-soft: #eaf7e8;
        }

        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: var(--wmc-ink);
            margin: 0;
            padding: 28px;
        }

        .payslip-shell {
            max-width: 820px;
            margin: 0 auto;
        }

        .print-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
        }

        .btn-print {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
        }

        .payslip {
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #dbe3ef;
            box-shadow: 0 22px 55px rgba(15, 23, 42, .10);
        }

        .company-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 22px 14px;
            border-top: 5px solid var(--wmc-blue);
            border-bottom: 3px solid var(--wmc-blue);
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 60%, #eef5ff 100%);
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand-logo {
            width: 84px;
            height: 54px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .brand-text h1 {
            font-size: 28px;
            line-height: .95;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
            color: var(--wmc-blue);
            font-weight: 900;
        }

        .brand-text .corp {
            display: block;
            color: var(--wmc-red);
            font-size: 18px;
            letter-spacing: 2px;
            font-weight: 900;
            margin-top: 2px;
        }

        .brand-text .services {
            display: block;
            color: #334155;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 700;
            margin-top: 3px;
        }

        .company-details {
            text-align: right;
            font-size: 10px;
            line-height: 1.35;
            color: #475569;
            max-width: 300px;
        }

        .payslip-title {
            text-align: center;
            padding: 12px 22px 8px;
        }

        .payslip-title h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 1.5px;
            color: var(--wmc-ink);
        }

        .payslip-title p {
            margin: 4px 0 0;
            color: var(--wmc-muted);
            font-size: 13px;
        }

        .payslip-body {
            padding: 14px 24px 22px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 20px;
            margin-bottom: 16px;
            padding: 12px;
            border: 1px solid var(--wmc-line);
            border-radius: 14px;
            background: #fbfdff;
        }

        .info-row {
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            font-size: 13px;
        }

        .info-row strong {
            color: #334155;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .info-row span {
            font-weight: 700;
            color: #0f172a;
        }

        .section-card {
            border: 1px solid var(--wmc-line);
            border-radius: 14px;
            overflow: hidden;
            margin-top: 12px;
        }

        .section-title {
            background: var(--wmc-soft);
            padding: 10px 14px;
            font-weight: 900;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: var(--wmc-blue);
            border-bottom: 1px solid var(--wmc-line);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        td, th {
            padding: 8px 12px;
            border-bottom: 1px solid #e8edf5;
            vertical-align: middle;
        }

        tr:last-child td, tr:last-child th { border-bottom: none; }

        .amount { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .label { color: #1e293b; }
        .subtle { color: #64748b; font-size: 12px; }
        .strong { font-weight: 800; }

        .total-row td {
            background: #f8fafc;
            font-weight: 900;
        }

        .grand-row td {
            background: #eef4ff;
            color: var(--wmc-blue);
            font-weight: 900;
        }

        .net-pay-row td {
            background: var(--wmc-green-soft);
            color: #14532d;
            font-size: 15px;
            font-weight: 900;
            border-top: 2px solid #a7d99b;
        }

        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 12px;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 40px;
            margin-top: 28px;
            padding-top: 8px;
        }

        .signature-box {
            text-align: center;
            font-size: 12px;
            color: #334155;
        }

        .signature-line {
            border-top: 1px solid #0f172a;
            margin: 34px auto 6px;
            max-width: 220px;
            padding-top: 6px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            body { padding: 16px; }
            .company-header { align-items: flex-start; flex-direction: column; }
            .company-details { text-align: left; max-width: none; }
            .info-grid, .two-col, .signatures { grid-template-columns: 1fr; }
            .brand-text h1 { font-size: 22px; }
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .print-toolbar { display: none !important; }
            .payslip-shell { max-width: none; margin: 0; }
            .payslip { border-radius: 0; box-shadow: none; border: none; }
            .company-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .section-title, .total-row td, .grand-row td, .net-pay-row td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>

<body>
@php
    $employee = $item->employeeProfile ?? null;
    $user = $employee->user ?? null;

    $employeeId = $employee->employee_id
        ?? $employee->employee_no
        ?? $employee->employee_number
        ?? $user->employee_id
        ?? $user->id
        ?? '-';

    $employeeName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
    if ($employeeName === '') {
        $employeeName = $employee->name ?? $user->name ?? '-';
    }

    $designation = $employee->position->name
        ?? $employee->designation
        ?? $employee->position
        ?? '-';

    $branch = $employee->branch->name
        ?? $user->branch->name
        ?? $employee->branch_name
        ?? '-';

    $department = $user->department->name ?? $employee->department->name ?? '-';

    $moneyValue = function (...$values) {
        foreach ($values as $value) {
            if (is_numeric($value) && (float) $value > 0) {
                return (float) $value;
            }
        }

        foreach ($values as $value) {
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return 0.0;
    };

    $ratePerDay = $moneyValue(
        $item->daily_rate ?? null,
        $item->rate_per_day ?? null,
        $employee->employee_rate ?? null,
        $employee->rate_per_day ?? null,
        $employee->daily_rate ?? null,
        $employee->salary ?? null
    );

    $holidayPay = $moneyValue($item->holiday_pay ?? null);
    $otPay = $moneyValue($item->ot_pay ?? null, $item->overtime_pay ?? null);
    $otherAdjustment = $moneyValue($item->other_adjustment ?? null, $item->adjustment_pay ?? null);
    $allowances = $moneyValue($item->allowances ?? null, $item->allowance ?? null);
    $thirteenthMonth = $moneyValue($item->thirteenth_month_pay ?? null, $item->{'13th_month_pay'} ?? null);

    $days = $moneyValue(
        $item->no_of_days ?? null,
        $item->present_days ?? null,
        $item->days_worked ?? null
    );

    $basicTotal = $moneyValue($item->total ?? null, $item->basic_pay ?? null);

    $grossPay = $moneyValue($item->grand_total ?? null, $item->gross_pay ?? null);

    if ($basicTotal <= 0 && $ratePerDay > 0 && $days > 0) {
        $basicTotal = $ratePerDay * $days;
    }

    if ($basicTotal <= 0 && $grossPay > 0) {
        $basicTotal = max(0, $grossPay - $holidayPay - $otPay - $otherAdjustment - $allowances - $thirteenthMonth);
    }

    if ($days <= 0 && $ratePerDay > 0 && $basicTotal > 0) {
        $days = round($basicTotal / $ratePerDay, 2);
    }

    if ($grossPay <= 0) {
        $grossPay = $basicTotal + $holidayPay + $otPay + $otherAdjustment + $allowances + $thirteenthMonth;
    }

    $sss = $moneyValue($item->sss ?? null);
    $pagibig = $moneyValue($item->pagibig ?? null, $item->pag_ibig ?? null);
    $philhealth = $moneyValue($item->philhealth ?? null, $item->phil_health ?? null);
    $cashAdvance = $moneyValue($item->cash_advance ?? null);
    $accountReceivable = $moneyValue($item->account_receivable ?? null, $item->account_receivables ?? null, $item->accounts_receivable ?? null);
    $stlMpl = $moneyValue($item->stl_mpl ?? null);
    $charitableContribution = $moneyValue($item->charitable_contribution ?? null, $item->charitable_contributions ?? null);
    $savingsShare = $moneyValue($item->savings_share ?? null);
    $riceLoan = $moneyValue($item->rice_loan ?? null);
    $loanPayment = $moneyValue($item->loan_payment ?? null);
    $lotPayment = $moneyValue($item->lot_payment ?? null);
    $birthdaySavings = $moneyValue($item->birthday_savings ?? null, $item->brithday_savings ?? null);
    $taxWithheld = $moneyValue($item->tax_withheld ?? null, $item->tax ?? null);

    $deductionsTotal = $moneyValue($item->total_deductions ?? null);
    if ($deductionsTotal <= 0) {
        $deductionsTotal = $sss + $pagibig + $philhealth + $cashAdvance + $accountReceivable + $stlMpl +
            $charitableContribution + $savingsShare + $riceLoan + $loanPayment + $lotPayment +
            $birthdaySavings + $taxWithheld;
    }

    $netPay = $moneyValue($item->net_pay ?? null);
    if ($netPay <= 0 && ($grossPay > 0 || $allowances > 0 || $thirteenthMonth > 0 || $deductionsTotal > 0)) {
        $netPay = ($grossPay + $allowances + $thirteenthMonth) - $deductionsTotal;
    }
@endphp

<div class="payslip-shell">
    <div class="print-toolbar">
        <button onclick="window.print()" class="btn-print">Print Payslip</button>
    </div>

    <div class="payslip">
        <div class="company-header">
            <div class="brand-wrap">
                <img src="{{ asset('images/wizmaster-logo.png') }}" alt="Wizmaster Logo" class="brand-logo">
                <div class="brand-text">
                    <h1>Wizmaster</h1>
                    <span class="corp">Corporation</span>
                    <span class="services">Computer Sales &amp; Services</span>
                </div>
            </div>
            <div class="company-details">
                <div><strong>ADDRESS</strong> 1/F Solana District, Andres Bonifacio Ave., San Miguel, Iligan City</div>
                <div><strong>TEL. NUMBER</strong> (063) 222-4277 / (063) 915-501-4668</div>
                <div><strong>EMAIL</strong> sales@wizmaster.com.co</div>
                <div><strong>Website</strong> http://wizmaster.com.co</div>
            </div>
        </div>

        <div class="payslip-title">
            <h2>PAYSLIP</h2>
            <p>{{ $payroll->period_from?->format('M d, Y') }} - {{ $payroll->period_to?->format('M d, Y') }}</p>
        </div>

        <div class="payslip-body">
            <div class="info-grid">
                <div class="info-row"><strong>Employee ID</strong><span>{{ $employeeId }}</span></div>
                <div class="info-row"><strong>Branch</strong><span>{{ $branch }}</span></div>
                <div class="info-row"><strong>Name</strong><span>{{ $employeeName }}</span></div>
                <div class="info-row"><strong>Department</strong><span>{{ $department }}</span></div>
                <div class="info-row"><strong>Designation</strong><span>{{ $designation }}</span></div>
                <div class="info-row"><strong>Payroll Period</strong><span>{{ $payroll->period_from?->format('M d') }} - {{ $payroll->period_to?->format('M d, Y') }}</span></div>
            </div>

            <div class="section-card">
                <div class="section-title">Earnings</div>
                <table>
                    <tr>
                        <td class="label">Rate Per Day</td>
                        <td class="amount">₱{{ number_format($ratePerDay, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. of Days</td>
                        <td class="amount">{{ number_format($days, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total</td>
                        <td class="amount">₱{{ number_format($basicTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Holiday Pay</td>
                        <td class="amount">₱{{ number_format($holidayPay, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">OT Pay</td>
                        <td class="amount">₱{{ number_format($otPay, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Other Adjustment</td>
                        <td class="amount">₱{{ number_format($otherAdjustment, 2) }}</td>
                    </tr>
                    <tr class="grand-row">
                        <td>Grand Total</td>
                        <td class="amount">₱{{ number_format($grossPay, 2) }}</td>
                    </tr>
                </table>
            </div>

            <div class="two-col">
                <div class="section-card">
                    <div class="section-title">Deductions</div>
                    <table>
                        <tr><td>SSS</td><td class="amount">₱{{ number_format($sss, 2) }}</td></tr>
                        <tr><td>Pag-IBIG</td><td class="amount">₱{{ number_format($pagibig, 2) }}</td></tr>
                        <tr><td>PhilHealth</td><td class="amount">₱{{ number_format($philhealth, 2) }}</td></tr>
                        <tr><td>Cash Advance</td><td class="amount">₱{{ number_format($cashAdvance, 2) }}</td></tr>
                        <tr><td>Account Receivables</td><td class="amount">₱{{ number_format($accountReceivable, 2) }}</td></tr>
                        <tr><td>STL/MPL</td><td class="amount">₱{{ number_format($stlMpl, 2) }}</td></tr>
                        <tr><td>Charitable Contribution</td><td class="amount">₱{{ number_format($charitableContribution, 2) }}</td></tr>
                        <tr><td>Savings/Share</td><td class="amount">₱{{ number_format($savingsShare, 2) }}</td></tr>
                        <tr><td>Rice Loan</td><td class="amount">₱{{ number_format($riceLoan, 2) }}</td></tr>
                        <tr><td>Loan Payment</td><td class="amount">₱{{ number_format($loanPayment, 2) }}</td></tr>
                        <tr><td>Lot Payment</td><td class="amount">₱{{ number_format($lotPayment, 2) }}</td></tr>
                        <tr><td>Birthday Savings</td><td class="amount">₱{{ number_format($birthdaySavings, 2) }}</td></tr>
                        <tr><td>TAX Withheld</td><td class="amount">₱{{ number_format($taxWithheld, 2) }}</td></tr>
                        <tr class="total-row"><td>Total Deductions</td><td class="amount">₱{{ number_format($deductionsTotal, 2) }}</td></tr>
                    </table>
                </div>

                <div>
                    <div class="section-card">
                        <div class="section-title">Additions</div>
                        <table>
                            <tr><td>Allowances</td><td class="amount">₱{{ number_format($allowances, 2) }}</td></tr>
                            <tr><td>13th Month Pay</td><td class="amount">₱{{ number_format($thirteenthMonth, 2) }}</td></tr>
                        </table>
                    </div>

                    <div class="section-card">
                        <table>
                            <tr class="net-pay-row">
                                <td>Net Pay</td>
                                <td class="amount">₱{{ number_format($netPay, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="signatures">
                        <div class="signature-box">
                            <div class="subtle">Prepared By:</div>
                            <div class="signature-line">Janice G. Gernia</div>
                            <div>Cashier / Disbursement Officer</div>
                        </div>
                        <div class="signature-box">
                            <div class="subtle">Approved By:</div>
                            <div class="signature-line">Lucia A. Teatro</div>
                            <div>Accounting &amp; Finance Dept. Head</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
