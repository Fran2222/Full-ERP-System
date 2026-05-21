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
            $canManageAttendance = $canManageAttendance ?? auth()->user()->can('hr.attendance.view');
        @endphp

        <div class="row">
            @if($canManageAttendance)
                <div class="col-lg-4">
                    <div class="card rounded-4">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Add Attendance</h4>
                            <p class="mb-0 text-secondary">Record employee timekeeping details.</p>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('hr.attendance.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Employee</label>
                                    <select name="employee_profile_id" class="form-select" required>
                                        <option value="">Select Employee</option>

                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ (string) old('employee_profile_id') === (string) $employee->id ? 'selected' : '' }}>
                                                {{ $employee->user->last_name ?? '' }},
                                                {{ $employee->user->first_name ?? '' }}
                                                @if(!empty($employee->position?->name))
                                                    - {{ $employee->position->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date"
                                           name="attendance_date"
                                           class="form-control"
                                           value="{{ old('attendance_date', date('Y-m-d')) }}"
                                           required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time In</label>
                                        <input type="time"
                                               name="time_in"
                                               class="form-control"
                                               value="{{ old('time_in') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time Out</label>
                                        <input type="time"
                                               name="time_out"
                                               class="form-control"
                                               value="{{ old('time_out') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Break Hours</label>
                                    <input type="number"
                                           step="0.25"
                                           min="0"
                                           name="break_hours"
                                           class="form-control"
                                           value="{{ old('break_hours', 1) }}">
                                </div>

                                <div class="alert alert-info mb-3">
                                    <strong>Auto Compute:</strong><br>
                                    Late, undertime, overtime, and worked hours are computed automatically.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="present" {{ old('status') === 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                                        <option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>Late</option>
                                        <option value="half_day" {{ old('status') === 'half_day' ? 'selected' : '' }}>Half-day</option>
                                        <option value="leave" {{ old('status') === 'leave' ? 'selected' : '' }}>Leave</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Optional remarks">{{ old('remarks') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Save Attendance
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ $canManageAttendance ? 'col-lg-8 mt-3 mt-lg-0' : 'col-lg-12' }}">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-0">
                                {{ $canManageAttendance ? 'Attendance / Timekeeping' : 'My Attendance' }}
                            </h4>
                            <p class="mb-0 text-secondary">
                                {{ $canManageAttendance ? 'Daily attendance for payroll computation.' : 'Your attendance records only.' }}
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>

                                        @if($canManageAttendance)
                                            <th>Employee</th>
                                        @endif

                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Late</th>
                                        <th>Undertime</th>
                                        <th>OT</th>
                                        <th>Worked</th>
                                        <th>Status</th>

                                        @if($canManageAttendance)
                                            <th class="text-end">Action</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($attendanceRecords as $record)
                                        <tr>
                                            <td>
                                                @if($record->attendance_date)
                                                    {{ $record->attendance_date instanceof \Carbon\Carbon
                                                        ? $record->attendance_date->format('M d, Y')
                                                        : \Carbon\Carbon::parse($record->attendance_date)->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            @if($canManageAttendance)
                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $record->employeeProfile->user->last_name ?? '' }},
                                                        {{ $record->employeeProfile->user->first_name ?? '' }}
                                                    </div>

                                                    @if(!empty($record->employeeProfile?->position?->name))
                                                        <small class="text-secondary">
                                                            {{ $record->employeeProfile->position->name }}
                                                        </small>
                                                    @endif
                                                </td>
                                            @endif

                                            <td>
                                                {{ $record->time_in ? date('h:i A', strtotime($record->time_in)) : '-' }}
                                            </td>

                                            <td>
                                                {{ $record->time_out ? date('h:i A', strtotime($record->time_out)) : '-' }}
                                            </td>

                                            <td>{{ $record->late_minutes ?? 0 }} min</td>
                                            <td>{{ $record->undertime_minutes ?? 0 }} min</td>
                                            <td>{{ number_format((float) ($record->overtime_hours ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($record->total_worked_hours ?? 0), 2) }}</td>

                                            <td>
                                                @php
                                                    $status = $record->status ?? 'present';

                                                    $badgeClass = match($status) {
                                                        'present' => 'success',
                                                        'late' => 'warning text-dark',
                                                        'absent' => 'danger',
                                                        'half_day' => 'info',
                                                        'leave' => 'primary',
                                                        default => 'secondary',
                                                    };
                                                @endphp

                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>

                                            @if($canManageAttendance)
                                                <td class="text-end">
                                                    <form action="{{ route('hr.attendance.destroy', $record) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Delete this attendance record?')">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $canManageAttendance ? 10 : 8 }}" class="text-center text-muted py-4">
                                                {{ $canManageAttendance ? 'No attendance records found.' : 'No attendance records found for your account.' }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($attendanceRecords, 'links'))
                            <div class="mt-3">
                                {{ $attendanceRecords->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($canManageAttendance)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const timeIn = document.querySelector('input[name="time_in"]');
                const timeOut = document.querySelector('input[name="time_out"]');
                const breakHours = document.querySelector('input[name="break_hours"]');

                if (!timeIn || !timeOut || !breakHours) {
                    return;
                }

                const preview = document.createElement('div');
                preview.className = 'alert alert-light border mt-2 small';
                preview.innerHTML = `
                    <strong>Preview:</strong><br>
                    Worked Hours: <span id="worked">0.00</span><br>
                    Late Minutes: <span id="late">0</span><br>
                    Undertime: <span id="under">0</span><br>
                    Overtime: <span id="ot">0.00</span>
                `;

                breakHours.closest('.mb-3').after(preview);

                function toMinutes(value) {
                    if (!value) {
                        return null;
                    }

                    const parts = value.split(':');
                    const hours = parseInt(parts[0], 10);
                    const minutes = parseInt(parts[1], 10);

                    return (hours * 60) + minutes;
                }

                function compute() {
                    const inMin = toMinutes(timeIn.value);
                    const outMin = toMinutes(timeOut.value);
                    const breakMin = parseFloat(breakHours.value || 0) * 60;

                    const scheduleIn = 480;   // 08:00 AM
                    const scheduleOut = 1020; // 05:00 PM

                    if (!inMin || !outMin || outMin <= inMin) {
                        document.getElementById('worked').innerText = '0.00';
                        document.getElementById('late').innerText = '0';
                        document.getElementById('under').innerText = '0';
                        document.getElementById('ot').innerText = '0.00';
                        return;
                    }

                    const grossMinutes = outMin - inMin;
                    const workedHours = Math.max(0, (grossMinutes - breakMin) / 60);
                    const lateMinutes = inMin > scheduleIn ? inMin - scheduleIn : 0;
                    const undertimeMinutes = outMin < scheduleOut ? scheduleOut - outMin : 0;
                    const overtimeHours = outMin > scheduleOut ? (outMin - scheduleOut) / 60 : 0;

                    document.getElementById('worked').innerText = workedHours.toFixed(2);
                    document.getElementById('late').innerText = lateMinutes;
                    document.getElementById('under').innerText = undertimeMinutes;
                    document.getElementById('ot').innerText = overtimeHours.toFixed(2);
                }

                timeIn.addEventListener('input', compute);
                timeOut.addEventListener('input', compute);
                breakHours.addEventListener('input', compute);

                compute();
            });
        </script>
    @endif
</x-app-layout>