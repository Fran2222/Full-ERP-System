<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @php
            $formatNumber = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $totalAllocated = collect($leaveCredits ?? [])->sum('allocated');
            $totalUsed = collect($leaveCredits ?? [])->sum('used');
            $totalPending = collect($leaveCredits ?? [])->sum('pending');
            $totalRemaining = collect($leaveCredits ?? [])->sum('remaining');
        @endphp

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">{{ $employeeName }} - Leave Credits</h4>
                    <p class="mb-0 text-secondary">
                        Detailed leave credit balance for {{ $currentYear }}.
                    </p>
                </div>

                <a href="{{ route('hr.leave.credit-management') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <small class="text-secondary">Employee</small>
                            <h5 class="mb-0 mt-1">{{ $employeeName }}</h5>
                            <small class="text-secondary">{{ optional($employee->employeeProfile)->employee_id ?? 'No Employee ID' }}</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <small class="text-secondary">Branch / Department</small>
                            <h6 class="mb-0 mt-1">{{ optional($employee->branch)->name ?? 'No Branch' }}</h6>
                            <small class="text-secondary">{{ optional($employee->department)->name ?? 'No Department' }}</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <small class="text-secondary">Total Used</small>
                            <h4 class="mb-0 mt-1">{{ $formatNumber($totalUsed) }}</h4>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <small class="text-secondary">Total Remaining</small>
                            <h4 class="mb-0 mt-1">{{ $formatNumber($totalRemaining) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 260px;">Leave Type</th>
                                <th style="min-width: 130px;">Allocated</th>
                                <th style="min-width: 130px;">Used</th>
                                <th style="min-width: 130px;">Pending</th>
                                <th style="min-width: 140px;">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveCredits as $credit)
                                @php
                                    $remaining = (float) ($credit['remaining'] ?? 0);
                                @endphp

                                <tr>
                                    <td class="fw-semibold">{{ $credit['name'] ?? 'Leave Type' }}</td>
                                    <td>{{ $formatNumber($credit['allocated'] ?? 0) }}</td>
                                    <td>{{ $formatNumber($credit['used'] ?? 0) }}</td>
                                    <td>{{ $formatNumber($credit['pending'] ?? 0) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $remaining > 0 ? 'success' : 'danger' }}">
                                            {{ $formatNumber($remaining) }} day(s)
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        No leave credit records available for this employee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
