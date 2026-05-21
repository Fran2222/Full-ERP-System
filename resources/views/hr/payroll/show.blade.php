<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $canManagePayroll = $canManagePayroll ?? auth()->user()->can('hr.payroll.view');
        @endphp

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-0">
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

                <a href="{{ route('hr.payroll.index') }}" class="btn btn-light btn-sm rounded-3">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                @if($canManagePayroll)
                                    <th>Employee</th>
                                @endif

                                <th>Present</th>
                                <th>Absent</th>
                                <th>Worked Hrs</th>
                                <th>Late</th>
                                <th>UT</th>
                                <th>OT</th>
                                <th>Basic</th>
                                <th>OT Pay</th>
                                <th>Deductions</th>
                                <th>Net Pay</th>
                                <th class="text-end">Payslip</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($payroll->items as $item)
                                <tr>
                                    @if($canManagePayroll)
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $item->employeeProfile->user->last_name ?? '' }},
                                                {{ $item->employeeProfile->user->first_name ?? '' }}
                                            </div>

                                            @if(!empty($item->employeeProfile?->position?->name))
                                                <small class="text-secondary">
                                                    {{ $item->employeeProfile->position->name }}
                                                </small>
                                            @endif
                                        </td>
                                    @endif

                                    <td>{{ $item->present_days ?? 0 }}</td>
                                    <td>{{ $item->absent_days ?? 0 }}</td>
                                    <td>{{ number_format((float) ($item->worked_hours ?? 0), 2) }}</td>
                                    <td>{{ $item->late_minutes ?? 0 }} min</td>
                                    <td>{{ $item->undertime_minutes ?? 0 }} min</td>
                                    <td>{{ number_format((float) ($item->overtime_hours ?? 0), 2) }}</td>
                                    <td>₱{{ number_format((float) ($item->basic_pay ?? 0), 2) }}</td>
                                    <td>₱{{ number_format((float) ($item->overtime_pay ?? 0), 2) }}</td>
                                    <td>₱{{ number_format((float) ($item->total_deductions ?? 0), 2) }}</td>
                                    <td>
                                        <strong>₱{{ number_format((float) ($item->net_pay ?? 0), 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('hr.payroll.payslip', [$payroll->id, $item->id]) }}"
                                           class="btn btn-sm btn-outline-primary rounded-3">
                                            View Payslip
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManagePayroll ? 12 : 11 }}" class="text-center text-muted py-4">
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