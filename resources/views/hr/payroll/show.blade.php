<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $canManagePayroll = $canManagePayroll ?? auth()->user()->can('hr.payroll.view');

            $money = function ($value) {
                return '₱' . number_format((float) ($value ?? 0), 2);
            };

            $num = function ($value, $decimals = 2) {
                return number_format((float) ($value ?? 0), $decimals);
            };
        @endphp

        <style>
            .wmc-payroll-summary-card {
                border: 0;
                border-radius: 24px;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
                overflow: hidden;
            }

            .wmc-payroll-summary-card .card-header {
                background: #ffffff;
                border-bottom: 1px solid #edf0f7;
                padding: 24px 28px;
            }

            .wmc-payroll-summary-title {
                color: #07133b;
                font-weight: 800;
                letter-spacing: .02em;
            }

            .wmc-payroll-note {
                border: 1px solid #0f9aa7;
                background: #dff7f9;
                color: #03606a;
                border-radius: 10px;
                padding: 18px 20px;
                font-size: 15px;
                line-height: 1.6;
            }

            .wmc-payroll-table-wrap {
                border: 1px solid #eef1f6;
                border-radius: 18px;
                overflow: auto;
                background: #fff;
            }

            .wmc-payroll-table {
                min-width: 1920px;
                margin-bottom: 0;
                color: #07133b;
            }

            .wmc-payroll-table thead th {
                background: #f8fafc;
                border-bottom: 1px solid #edf1f7;
                color: #344054;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .06em;
                text-transform: uppercase;
                white-space: nowrap;
                vertical-align: middle;
                padding: 14px 12px;
            }

            .wmc-payroll-table tbody td {
                border-bottom: 1px solid #eef1f6;
                font-size: 13px;
                vertical-align: middle;
                padding: 14px 12px;
                white-space: nowrap;
            }

            .wmc-payroll-table tbody tr:hover {
                background: #fbfcff;
            }

            .wmc-payroll-employee-name {
                font-weight: 800;
                color: #07133b;
            }

            .wmc-payroll-muted {
                color: #667085;
                font-size: 12px;
            }

            .wmc-payroll-money {
                font-variant-numeric: tabular-nums;
                text-align: right;
            }

            .wmc-payroll-net {
                color: #07133b;
                font-weight: 900;
            }

            .wmc-payslip-icon-btn {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                border: 1px solid #4355f2;
                color: #4355f2;
                background: #ffffff;
                transition: all .18s ease;
                text-decoration: none;
            }

            .wmc-payslip-icon-btn svg {
                width: 18px;
                height: 18px;
                stroke: currentColor;
            }

            .wmc-payslip-icon-btn:hover {
                background: #4355f2;
                color: #ffffff;
                box-shadow: 0 10px 20px rgba(67, 85, 242, .22);
                transform: translateY(-1px);
            }

            .wmc-back-btn {
                border: 0;
                background: #eef1f4;
                color: #07133b;
                font-weight: 700;
                border-radius: 999px;
                padding: 10px 22px;
            }

            .wmc-back-btn:hover {
                background: #e2e8f0;
                color: #07133b;
            }
        </style>

        <div class="card wmc-payroll-summary-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1 wmc-payroll-summary-title">
                        {{ $canManagePayroll ? 'Payroll Summary' : 'My Payslip Summary' }}
                    </h4>
                    <p class="mb-0 text-secondary">
                        @if($payroll->period_from && $payroll->period_to)
                            {{ $payroll->period_from instanceof \Carbon\Carbon
                                ? $payroll->period_from->format('M d, Y')
                                : \Carbon\Carbon::parse($payroll->period_from)->format('M d, Y') }}
                            -
                            {{ $payroll->period_to instanceof \Carbon\Carbon
                                ? $payroll->period_to->format('M d, Y')
                                : \Carbon\Carbon::parse($payroll->period_to)->format('M d, Y') }}
                        @else
                            Payroll period
                        @endif
                    </p>
                </div>

                <a href="{{ route('hr.payroll.index') }}" class="btn wmc-back-btn">
                    Back
                </a>
            </div>

            <div class="card-body p-4">
                <div class="wmc-payroll-note mb-4">
                    Payroll computation now includes employee profile default deductions/additions, attendance days, holiday pay, and approved OT requests within the selected period.
                </div>

                <div class="wmc-payroll-table-wrap">
                    <table class="table align-middle wmc-payroll-table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Branch</th>
                                <th class="text-end">Rate / Day</th>
                                <th class="text-center">No. of Days</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Holiday Pay</th>
                                <th class="text-end">OT Pay</th>
                                <th class="text-end">Other Adjustment</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-end">SSS</th>
                                <th class="text-end">Pag-IBIG</th>
                                <th class="text-end">PhilHealth</th>
                                <th class="text-end">Cash Advance</th>
                                <th class="text-end">Account Receivables</th>
                                <th class="text-end">STL/MPL</th>
                                <th class="text-end">Charitable Contribution</th>
                                <th class="text-end">Savings / Share</th>
                                <th class="text-end">Rice Loan</th>
                                <th class="text-end">Loan Payment</th>
                                <th class="text-end">Lot Payment</th>
                                <th class="text-end">Birthday Savings</th>
                                <th class="text-end">Tax Withheld</th>
                                <th class="text-end">Allowances</th>
                                <th class="text-end">13th Month Pay</th>
                                <th class="text-end">Net Pay</th>
                                <th class="text-center">Payslip</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($payroll->items as $item)
                                @php
                                    $employee = $item->employeeProfile;
                                    $user = $employee?->user;
                                    $employeeId = $employee?->employee_id
                                        ?? $employee?->employee_no
                                        ?? $employee?->employee_number
                                        ?? $employee?->id
                                        ?? '—';
                                    $employeeName = trim(($user?->last_name ?? '') . ', ' . ($user?->first_name ?? ''));
                                    if ($employeeName === ',') {
                                        $employeeName = $employee?->name ?? $user?->name ?? 'Employee';
                                    }
                                    $designation = $employee?->position?->name
                                        ?? $employee?->designation
                                        ?? $employee?->position
                                        ?? '—';
                                    $branch = $user?->branch?->name
                                        ?? $employee?->user?->branch?->name
                                        ?? $employee?->branch?->name
                                        ?? $employee?->branch_name
                                        ?? $employee?->branch
                                        ?? '—';

                                    $ratePerDay = $item->rate_per_day
                                        ?? $item->daily_rate
                                        ?? $employee?->employee_rate
                                        ?? $employee?->rate_per_day
                                        ?? 0;
                                    $daysWorked = $item->no_of_days
                                        ?? $item->days_worked
                                        ?? $item->present_days
                                        ?? 0;
                                    $total = $item->total
                                        ?? $item->basic_pay
                                        ?? ((float) $ratePerDay * (float) $daysWorked);
                                    $holidayPay = $item->holiday_pay ?? 0;
                                    $otPay = $item->ot_pay ?? $item->overtime_pay ?? 0;
                                    $otherAdjustment = $item->other_adjustment ?? $item->adjustment_amount ?? 0;
                                    $grandTotal = $item->grand_total
                                        ?? ((float) $total + (float) $holidayPay + (float) $otPay + (float) $otherAdjustment);

                                    $sss = $item->sss ?? $item->sss_contribution ?? 0;
                                    $pagibig = $item->pagibig ?? $item->pag_ibig ?? $item->pagibig_contribution ?? 0;
                                    $philhealth = $item->philhealth ?? $item->phil_health ?? $item->philhealth_contribution ?? 0;
                                    $cashAdvance = $item->cash_advance ?? 0;
                                    $accountReceivables = $item->account_receivables ?? $item->accounts_receivable ?? 0;
                                    $stlMpl = $item->stl_mpl ?? $item->stl ?? 0;
                                    $charitable = $item->charitable_contribution ?? $item->charitable_contributions ?? 0;
                                    $savingsShare = $item->savings_share ?? $item->savings ?? 0;
                                    $riceLoan = $item->rice_loan ?? 0;
                                    $loanPayment = $item->loan_payment ?? 0;
                                    $lotPayment = $item->lot_payment ?? 0;
                                    $birthdaySavings = $item->birthday_savings ?? $item->brithday_savings ?? 0;
                                    $taxWithheld = $item->tax_withheld ?? $item->tax ?? 0;
                                    $allowances = $item->allowances ?? $item->allowance ?? 0;
                                    $thirteenthMonth = $item->thirteenth_month_pay ?? $item->{'13th_month_pay'} ?? 0;
                                    $netPay = $item->net_pay ?? 0;
                                @endphp

                                <tr>
                                    <td>{{ $employeeId }}</td>
                                    <td>
                                        <div class="wmc-payroll-employee-name">{{ $employeeName }}</div>
                                    </td>
                                    <td>{{ $designation }}</td>
                                    <td>{{ $branch }}</td>
                                    <td class="wmc-payroll-money">{{ $money($ratePerDay) }}</td>
                                    <td class="text-center">{{ $num($daysWorked, 2) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($total) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($holidayPay) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($otPay) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($otherAdjustment) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($grandTotal) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($sss) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($pagibig) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($philhealth) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($cashAdvance) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($accountReceivables) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($stlMpl) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($charitable) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($savingsShare) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($riceLoan) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($loanPayment) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($lotPayment) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($birthdaySavings) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($taxWithheld) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($allowances) }}</td>
                                    <td class="wmc-payroll-money">{{ $money($thirteenthMonth) }}</td>
                                    <td class="wmc-payroll-money wmc-payroll-net">{{ $money($netPay) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('hr.payroll.payslip', [$payroll->id, $item->id]) }}"
                                           class="wmc-payslip-icon-btn"
                                           title="View Payslip"
                                           aria-label="View Payslip">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M7 3.75h6.25L18 8.5v11.75H7V3.75Z" stroke-width="1.8" stroke-linejoin="round"/>
                                                <path d="M13.25 3.75V8.5H18" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M9.75 12h5.5M9.75 15h5.5M9.75 18h3.5" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="28" class="text-center text-muted py-4">
                                        {{ $canManagePayroll ? 'No payroll items generated.' : 'No payslip found for your account.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(!$canManagePayroll)
                    <div class="alert alert-info mt-3 mb-0">
                        This page only shows your own payroll information.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
