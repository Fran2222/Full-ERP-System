<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $canManagePayroll = $canManagePayroll ?? auth()->user()->can('hr.payroll.view');
        @endphp

        <div class="row">
            @if($canManagePayroll)
                <div class="col-lg-4">
                    <div class="card rounded-4">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Generate Payroll</h4>
                            <p class="mb-0 text-secondary">Based on attendance records.</p>
                        </div>

                        <div class="card-body">
                            <form id="wmcGeneratePayrollForm" action="{{ route('hr.payroll.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Period From</label>
                                    <input type="date"
                                           name="period_from"
                                           class="form-control"
                                           value="{{ old('period_from') }}"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Period To</label>
                                    <input type="date"
                                           name="period_to"
                                           class="form-control"
                                           value="{{ old('period_to') }}"
                                           required>
                                </div>

                                <button type="submit"
                                        class="btn btn-primary w-100">
                                    Generate Payroll
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ $canManagePayroll ? 'col-lg-8 mt-3 mt-lg-0' : 'col-lg-12' }}">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-0">
                                {{ $canManagePayroll ? 'Payroll Runs' : 'My Payslips' }}
                            </h4>
                            <p class="mb-0 text-secondary">
                                {{ $canManagePayroll ? 'Generated payroll periods.' : 'Only payroll records with your payslip are shown.' }}
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>{{ $canManagePayroll ? 'Employees' : 'Payslip' }}</th>
                                        <th>Generated On</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($payrollRuns as $run)
                                        <tr>
                                            <td>{{ $run->id }}</td>

                                            <td>
                                                @if($run->period_from && $run->period_to)
                                                    {{ $run->period_from instanceof \Carbon\Carbon
                                                        ? $run->period_from->format('M d, Y')
                                                        : \Carbon\Carbon::parse($run->period_from)->format('M d, Y') }}
                                                    -
                                                    {{ $run->period_to instanceof \Carbon\Carbon
                                                        ? $run->period_to->format('M d, Y')
                                                        : \Carbon\Carbon::parse($run->period_to)->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                @php
                                                    $status = $run->status ?? 'draft';

                                                    $badgeClass = match($status) {
                                                        'draft' => 'secondary',
                                                        'posted' => 'success',
                                                        'paid' => 'primary',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp

                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </td>

                                            <td>
                                                @if($canManagePayroll)
                                                    {{ $run->items_count }} employee(s)
                                                @else
                                                    {{ $run->items_count }} payslip
                                                @endif
                                            </td>

                                            <td>
                                                {{ $run->created_at
                                                    ? $run->created_at->format('M d, Y h:i A')
                                                    : '-' }}
                                            </td>

                                            <td class="text-end">
                                                <a href="{{ route('hr.payroll.show', $run) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-3">
                                                    {{ $canManagePayroll ? 'View' : 'View Payslip' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                {{ $canManagePayroll ? 'No payroll runs yet.' : 'No payslips found for your account.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($payrollRuns, 'links'))
                            <div class="mt-3">
                                {{ $payrollRuns->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('wmcGeneratePayrollForm');

            if (!form) {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                var fromInput = form.querySelector('input[name="period_from"]');
                var toInput = form.querySelector('input[name="period_to"]');
                var periodFrom = fromInput && fromInput.value ? fromInput.value : '-';
                var periodTo = toInput && toInput.value ? toInput.value : '-';

                if (typeof Swal === 'undefined') {
                    if (confirm('Generate payroll for this period?')) {
                        form.submit();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Generate payroll?',
                    html: '<div style="font-size:14px;line-height:1.7;">This will create payroll records for:<br><strong>' + periodFrom + ' to ' + periodTo + '</strong></div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, generate',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-primary mx-1',
                        cancelButton: 'btn btn-light mx-1'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Generating payroll...',
                            text: 'Please wait while the payroll records are being prepared.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });

                        form.submit();
                    }
                });
            });
        });
    </script>

</x-app-layout>