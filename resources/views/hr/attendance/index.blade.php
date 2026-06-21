<x-app-layout>
    <style>

        /* Attendance page should not show Hope UI/global preloaders during filter changes, save draft,
           submit to payroll, or export. The request may still reload/download normally, but the loader
           overlay is intentionally suppressed only while this Attendance page is open. */
        body.attendance-no-preload .preloader,
        body.attendance-no-preload #preloader,
        body.attendance-no-preload #loading,
        body.attendance-no-preload #loading-center,
        body.attendance-no-preload .loading,
        body.attendance-no-preload .loading-overlay,
        body.attendance-no-preload .loader,
        body.attendance-no-preload .loader-wrapper,
        body.attendance-no-preload .page-loader,
        body.attendance-no-preload .iq-loader,
        body.attendance-no-preload .iq-loader-box,
        body.attendance-no-preload .iq-preloader,
        body.attendance-no-preload .pace,
        body.attendance-no-preload .pace-inactive,
        body.attendance-no-preload [data-loader="true"] {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        body.attendance-no-preload,
        body.attendance-no-preload *,
        html.attendance-no-preload,
        html.attendance-no-preload * {
            cursor: auto !important;
        }

        body.attendance-no-preload button:not(:disabled),
        body.attendance-no-preload a,
        body.attendance-no-preload label,
        body.attendance-no-preload select {
            cursor: pointer !important;
        }

        body.attendance-no-preload input,
        body.attendance-no-preload textarea {
            cursor: text !important;
        }

        .attendance-hero {
            margin-top: -18px;
        }

        .attendance-page-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .attendance-title-icon {
            width: 40px;
            height: 40px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #3f5be8;
            font-size: 20px;
        }

        .attendance-title-icon svg,
        .attendance-stat-icon svg,
        .attendance-cutoff-icon {
            width: 20px;
            height: 20px;
            stroke: currentColor;
        }

        .attendance-page-title h3 {
            font-size: 24px;
            line-height: 1.15;
        }

        .attendance-page-title p {
            font-size: 13px;
        }

        .attendance-action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .attendance-lock-banner {
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            background: #f0fdf4;
            color: #166534;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .attendance-lock-banner i {
            font-size: 18px;
        }

        .attendance-locked-cell input,
        .attendance-locked-cell select {
            background: #f8fafc !important;
            cursor: not-allowed;
        }

        .attendance-workspace-card {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 14px 38px rgba(15, 23, 42, .07);
            overflow: hidden;
        }

        .attendance-workspace-card > .card-body {
            padding: 20px;
        }

        .attendance-filter-card,
        .attendance-panel {
            border: 1px solid #eef0f4;
            border-radius: 18px;
            box-shadow: none;
            background: #ffffff;
        }

        .attendance-filter-label {
            font-size: 13px;
            color: #111827;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .attendance-cutoff-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .attendance-cutoff-toggle input {
            display: none;
        }

        .attendance-cutoff-toggle label {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            line-height: 1.05;
            font-weight: 800;
            cursor: pointer;
            color: #111827;
            background: #f8fafc;
            margin: 0;
        }

        .attendance-cutoff-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .attendance-cutoff-toggle label small {
            font-size: 11px;
            font-weight: 700;
            opacity: .8;
            margin-top: 3px;
        }

        .attendance-cutoff-toggle input:checked + label {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
            box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
        }

        .attendance-stat-card {
            border: 0;
            border-radius: 14px;
            min-height: 56px;
            overflow: visible;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .04);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .attendance-stats-row {
            justify-content: center;
            align-items: stretch;
        }

        .attendance-stats-row > [class*="col-"] {
            display: flex;
        }

        .attendance-stat-card {
            width: 100%;
        }

        .attendance-stat-body {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 56px;
            height: 100%;
            padding: 8px 10px;
            width: 100%;
            text-align: left;
        }

        .attendance-stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
            font-size: 16px;
        }

        .attendance-stat-icon svg {
            width: 17px;
            height: 17px;
        }

        .attendance-stat-content,
        .attendance-stat-body > div:last-child {
            min-width: 0;
            flex: 1 1 auto;
        }

        .attendance-stat-title {
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            margin-bottom: 2px;
            line-height: 1.08;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .attendance-stat-value {
            color: #020617;
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            word-break: keep-all;
        }

        #summaryGrandTotal,
        #summaryGrandTotalBottom {
            font-size: 16px;
            letter-spacing: -0.35px;
        }

        @media (min-width: 1200px) {
            .attendance-stats-row {
                --bs-gutter-x: .55rem;
                --bs-gutter-y: .55rem;
            }
        }

        @media (max-width: 575.98px) {
            .attendance-stat-body {
                min-height: 54px;
                padding: 8px 10px;
            }
        }

        .stat-blue { background: #eff6ff; }
        .stat-green { background: #ecfdf3; }
        .stat-red { background: #fff1f2; }
        .stat-orange { background: #fff7ed; }
        .stat-purple { background: #f5f3ff; }
        .stat-dark { background: #eef2ff; }

        .stat-blue .attendance-stat-icon { color: #2563eb; background: #dbeafe; }
        .stat-green .attendance-stat-icon { color: #16a34a; background: #dcfce7; }
        .stat-red .attendance-stat-icon { color: #dc2626; background: #fee2e2; }
        .stat-orange .attendance-stat-icon { color: #d97706; background: #ffedd5; }
        .stat-purple .attendance-stat-icon { color: #7c3aed; background: #ede9fe; }
        .stat-dark .attendance-stat-icon { color: #1d4ed8; background: #dbeafe; }

        .attendance-table-wrap {
            overflow-x: auto;
            border: 1px solid #eef0f4;
            border-radius: 16px;
        }

        .attendance-encoding-table {
            min-width: 1480px;
            margin-bottom: 0;
        }

        .attendance-encoding-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            text-transform: none;
            vertical-align: middle;
            border-bottom: 1px solid #eef0f4;
            white-space: nowrap;
        }

        .attendance-encoding-table tbody td {
            vertical-align: middle;
            border-color: #eef0f4;
            font-size: 13px;
        }

        .attendance-employee-cell {
            min-width: 190px;
        }

        .attendance-date-cell {
            width: 88px;
            min-width: 88px;
            text-align: center;
            padding: 7px 6px !important;
        }

        .attendance-day-head {
            text-align: center;
            min-width: 88px;
        }

        .attendance-day-head span {
            display: block;
            font-weight: 900;
            color: #111827;
        }

        .attendance-day-head small {
            display: block;
            color: #64748b;
            font-weight: 700;
        }

        .attendance-time-badge {
            border-radius: 9px;
            min-height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            padding: 6px;
            font-weight: 800;
            font-size: 11px;
            line-height: 1.2;
            border: 1px solid transparent;
        }

        .attendance-time-badge small {
            font-size: 10px;
            font-weight: 800;
        }

        .att-present { color: #15803d; background: #ecfdf3; border-color: #bbf7d0; }
        .att-late { color: #c2410c; background: #fff7ed; border-color: #fed7aa; }
        .att-absent { color: #dc2626; background: #fff1f2; border-color: #fecdd3; }
        .att-leave { color: #b45309; background: #fffbeb; border-color: #fde68a; }
        .att-wop { color: #7c2d12; background: #fff7ed; border-color: #fed7aa; }
        .att-holiday { color: #6d28d9; background: #f5f3ff; border-color: #ddd6fe; }
        .att-half { color: #7c3aed; background: #f5f3ff; border-color: #ddd6fe; }
        .att-rest { color: #1d4ed8; background: #eff6ff; border-color: #bfdbfe; }
        .att-empty { color: #94a3b8; background: #f8fafc; border-color: #e5e7eb; }

        .attendance-legend {
            display: flex;
            justify-content: flex-end;
            gap: 14px;
            flex-wrap: wrap;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }

        .attendance-legend span::before {
            content: '';
            width: 9px;
            height: 9px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        .legend-present::before { background: #22c55e; }
        .legend-absent::before { background: #ef4444; }
        .legend-leave::before { background: #f59e0b; }
        .legend-rest::before { background: #3b82f6; }
        .legend-holiday::before { background: #8b5cf6; }
        .legend-pending::before { background: #94a3b8; }

        .attendance-summary-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }

        .attendance-summary-box {
            border-radius: 14px;
            padding: 12px 10px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #eef0f4;
            min-width: 0;
        }

        .attendance-summary-box small {
            display: block;
            color: #475569;
            font-weight: 800;
            margin-bottom: 5px;
            line-height: 1.2;
            white-space: normal;
        }

        .attendance-summary-box strong {
            display: block;
            font-size: 18px;
            color: #020617;
            line-height: 1.15;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        #summaryGrandTotalBottom,
        #summaryOvertimePay,
        #summaryHolidayPay {
            font-size: 17px;
            letter-spacing: -0.25px;
        }

        .attendance-entry-stack {
            display: grid;
            gap: 4px;
            min-width: 104px;
        }

        .attendance-entry-input,
        .attendance-entry-select {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            color: #0f172a;
            font-size: 11px;
            font-weight: 700;
            min-height: 30px;
            padding: 4px 7px;
            outline: none;
        }

        .attendance-entry-input:focus,
        .attendance-entry-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
        }

        .attendance-entry-stack .attendance-entry-select {
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }



        .attendance-entry-stack.status-late .attendance-entry-select {
            color: #c2410c;
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .attendance-entry-stack.status-late .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .18);
        }

        .attendance-entry-stack.status-present .attendance-entry-select {
            color: #15803d;
            background: #ecfdf3;
            border-color: #bbf7d0;
        }

        .attendance-entry-stack.status-absent .attendance-entry-select {
            color: #dc2626;
            background: #fff1f2;
            border-color: #fecdd3;
        }

        .attendance-entry-stack.status-leave .attendance-entry-select {
            color: #b45309;
            background: #fffbeb;
            border-color: #fde68a;
        }

        .attendance-entry-stack.status-holiday .attendance-entry-select {
            color: #6d28d9;
            background: #f5f3ff;
            border-color: #ddd6fe;
        }

        .attendance-entry-stack.status-rest .attendance-entry-select {
            color: #1d4ed8;
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .attendance-entry-stack.status-pending .attendance-entry-select {
            color: #64748b;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .attendance-entry-stack.status-present .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .16);
        }

        .attendance-entry-stack.status-absent .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .16);
        }

        .attendance-entry-stack.status-leave .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .18);
        }

        .attendance-entry-stack.status-holiday .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
        }

        .attendance-entry-stack.status-rest .attendance-entry-select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .18);
        }

        .attendance-entry-remarks {
            display: none;
        }

        .attendance-auto-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border-radius: 999px;
            min-height: 22px;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: 900;
            line-height: 1;
            white-space: nowrap;
        }

        .attendance-auto-badge.leave-approved {
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .attendance-auto-badge.ot-approved {
            color: #1d4ed8;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .attendance-entry-stack.is-approved-leave input,
        .attendance-entry-stack.is-approved-leave select {
            cursor: not-allowed;
            background-color: #fffbeb !important;
            border-color: #fde68a !important;
        }

        .attendance-date-cell.is-editing {
            background: #f8fafc;
        }

        .attendance-row-tools {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .attendance-row-tools .attendance-row-action-btn {
            width: 34px;
            height: 34px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 10px;
            border-width: 1.4px;
            background: #ffffff;
            box-shadow: none;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .12s ease;
        }

        .attendance-row-tools .attendance-row-action-btn svg {
            width: 17px;
            height: 17px;
            display: block;
            stroke: currentColor;
        }

        .attendance-row-tools .attendance-row-action-btn:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .attendance-row-tools .attendance-mark-row-present {
            color: #16a34a;
            border-color: #16a34a;
        }

        .attendance-row-tools .attendance-mark-row-present:hover:not(:disabled),
        .attendance-row-tools .attendance-mark-row-present:focus:not(:disabled) {
            color: #ffffff;
            background: #16a34a;
            border-color: #16a34a;
        }

        .attendance-row-tools .attendance-clear-row {
            color: #64748b;
            border-color: #cbd5e1;
        }

        .attendance-row-tools .attendance-clear-row:hover:not(:disabled),
        .attendance-row-tools .attendance-clear-row:focus:not(:disabled) {
            color: #ffffff;
            background: #64748b;
            border-color: #64748b;
        }

        .attendance-row-tools .attendance-row-action-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
        }

        .attendance-note {
            border-radius: 14px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #475569;
            padding: 12px 14px;
            font-size: 13px;
        }

        @media (max-width: 1199.98px) {
            .attendance-summary-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .attendance-action-bar {
                justify-content: flex-start;
            }

            .attendance-summary-strip {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="container-fluid content-inner attendance-hero py-0">
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
            $isSubmitted = $isSubmitted ?? (bool) ($summary['is_submitted'] ?? false);
            $pendingEntriesForSubmit = (int) ($summary['pending_entries'] ?? 0);
            $canSubmitToPayroll = !$isSubmitted && $pendingEntriesForSubmit === 0 && (int) ($summary['total_employees'] ?? 0) > 0;

            try {
                $attendanceSelectedMonthStart = \Carbon\Carbon::createFromFormat('Y-m-d', ($selectedMonth ?? now()->format('Y-m')) . '-01')->startOfMonth();
            } catch (\Throwable $exception) {
                $attendanceSelectedMonthStart = now()->startOfMonth();
            }

            $attendanceSecondHalfEndDay = max(15, ((int) $attendanceSelectedMonthStart->copy()->endOfMonth()->format('d')) - 1);
        @endphp

        @if($canManageAttendance)
            @php
                $statusLabels = [
                    'present' => 'Present',
                    'late' => 'Late',
                    'absent' => 'Absent',
                    'half_day' => 'Half Day',
                    'leave' => 'Leave (WP)',
                    'leave_wop' => 'Leave (WOP)',
                    'holiday' => 'Holiday',
                    'regular_holiday' => 'Regular Holiday',
                    'regular_holiday_worked' => 'Regular Holiday Worked',
                    'special_holiday' => 'Special Non Working Holiday',
                    'special_holiday_worked' => 'Special Non Working Holiday (Worked)',
                    'special_working_present' => 'Special Working Holiday Present',
                    'special_working_absent' => 'Special Working Holiday Absent',
                    'rest_day' => 'Rest Day',
                    'pending' => 'Pending',
                ];

                $statusClasses = [
                    'present' => 'att-present',
                    'late' => 'att-late',
                    'absent' => 'att-absent',
                    'half_day' => 'att-half',
                    'leave' => 'att-leave',
                    'leave_wop' => 'att-wop',
                    'holiday' => 'att-holiday',
                    'regular_holiday' => 'att-holiday',
                    'regular_holiday_worked' => 'att-holiday',
                    'special_holiday' => 'att-holiday',
                    'special_holiday_worked' => 'att-holiday',
                    'special_working_present' => 'att-holiday',
                    'special_working_absent' => 'att-holiday',
                    'rest_day' => 'att-rest',
                    'pending' => 'att-empty',
                ];
            @endphp

            <div class="card attendance-workspace-card mb-3">
                <div class="card-body">
            <form id="attendanceFilterForm" method="GET" action="{{ route('hr.attendance.index') }}" data-attendance-no-preload="true">
                <div class="row align-items-center mb-3 gy-3">
                    <div class="col-xl-6 col-lg-12">
                        <div class="attendance-page-title">
                            <div class="attendance-title-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                    <path d="m9 16 2 2 4-5"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold">Attendance Encoding</h3>
                                <p class="mb-0 text-secondary">Semi-Monthly Attendance</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-12">
                        <div class="attendance-action-bar">
                            <button type="submit"
                                    form="attendanceDraftForm"
                                    name="attendance_action"
                                    value="save_draft"
                                    class="btn btn-outline-primary"
                                    {{ $isSubmitted ? 'disabled' : '' }}>
                                <i class="far fa-save me-1"></i> Save Draft
                            </button>
                            <button type="submit"
                                    form="attendanceDraftForm"
                                    id="submitToPayrollButton"
                                    name="attendance_action"
                                    value="submit_to_payroll"
                                    class="btn btn-primary"
                                    {{ $canSubmitToPayroll ? '' : 'disabled' }}
                                    title="{{ $isSubmitted ? 'Already submitted to payroll' : ($pendingEntriesForSubmit > 0 ? 'Complete all pending entries before submitting' : 'Submit and lock this cut-off') }}">
                                <i class="fas fa-user-check me-1"></i> Submit to Payroll
                            </button>
                            <button type="submit"
                                    form="attendanceDraftForm"
                                    name="attendance_action"
                                    value="export_excel"
                                    class="btn btn-success"
                                    title="Export the current selected cut-off to Excel">
                                <i class="far fa-file-excel me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>

                @if($isSubmitted)
                    <div class="attendance-lock-banner">
                        <i class="fas fa-lock"></i>
                        <div>
                            Submitted to payroll and locked
                            @if(!empty($submittedAt))
                                <span class="fw-normal">on {{ \Carbon\Carbon::parse($submittedAt)->format('M d, Y g:i A') }}</span>
                            @endif
                            @if(!empty($submittedByName))
                                <span class="fw-normal">by {{ $submittedByName }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card attendance-filter-card mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Branch Name</label>
                                <select name="branch_id" class="form-select attendance-auto-submit">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Department</label>
                                <select name="department_id" class="form-select attendance-auto-submit">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ (string) $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Month</label>
                                <input type="month"
                                       name="month"
                                       class="form-control attendance-auto-submit"
                                       value="{{ $selectedMonth }}">
                            </div>

                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Cut-off Period</label>
                                <div class="attendance-cutoff-toggle">
                                    <input type="radio"
                                           id="cutoffFirstHalf"
                                           name="cutoff_period"
                                           value="first_half"
                                           class="attendance-auto-submit-radio"
                                           {{ $cutoffPeriod === 'first_half' ? 'checked' : '' }}>
                                    <label for="cutoffFirstHalf">
                                        <svg class="attendance-cutoff-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                            <path d="M16 2v4M8 2v4M3 10h18"></path>
                                            <path d="M8 15h4"></path>
                                        </svg>
                                        <span class="attendance-cutoff-text">
                                            <small>1–14</small>
                                        </span>
                                    </label>

                                    <input type="radio"
                                           id="cutoffSecondHalf"
                                           name="cutoff_period"
                                           value="second_half"
                                           class="attendance-auto-submit-radio"
                                           {{ $cutoffPeriod === 'second_half' ? 'checked' : '' }}>
                                    <label for="cutoffSecondHalf">
                                        <svg class="attendance-cutoff-icon" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                            <path d="M16 2v4M8 2v4M3 10h18"></path>
                                            <path d="M12 15h4"></path>
                                        </svg>
                                        <span class="attendance-cutoff-text">
                                            <small>15–{{ str_pad($attendanceSecondHalfEndDay, 2, '0', STR_PAD_LEFT) }}</small>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Prepared By</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ auth()->user()->full_name ?? auth()->user()->name ?? auth()->user()->email }}"
                                       readonly>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Cut-off Start</label>
                                <input type="text" class="form-control" value="{{ $cutoffStart->format('M d, Y') }}" readonly>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="attendance-filter-label">Cut-off End</label>
                                <input type="text" class="form-control" value="{{ $cutoffEnd->format('M d, Y') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row g-2 mb-3 attendance-stats-row">
                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-blue h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Total Employees</div>
                                <div class="attendance-stat-value">{{ number_format($summary['total_employees'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-green h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Present Days</div>
                                <div class="attendance-stat-value" id="summaryPresentDays">{{ number_format($summary['present_days'] ?? 0, 1) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-red h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="m17 8 5 5M22 8l-5 5"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Absent Days</div>
                                <div class="attendance-stat-value" id="summaryAbsentDays">{{ number_format($summary['absent_days'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-orange h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Late Minutes</div>
                                <div class="attendance-stat-value" id="summaryLateMinutes">{{ number_format($summary['late_minutes'] ?? 0) }} <small class="fs-6">min</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-purple h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="14" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M12 11v4"></path><path d="M10 13h4"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Pending Entries</div>
                                <div class="attendance-stat-value" id="summaryPendingEntries">{{ number_format($summary['pending_entries'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                    <div class="card attendance-stat-card stat-dark h-100">
                        <div class="attendance-stat-body">
                            <div class="attendance-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="15" rx="2"></rect><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"></path><path d="M3 12h18"></path></svg></div>
                            <div>
                                <div class="attendance-stat-title">Grand Total</div>
                                <div class="attendance-stat-value" id="summaryGrandTotal">₱{{ number_format($summary['grand_total'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card attendance-panel mb-3">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1 fw-bold">Attendance Details ({{ $cutoffStart->format('M j') }} – {{ $cutoffEnd->format('M j, Y') }})</h5>
                        </div>

                        <div class="attendance-legend">
                            <span class="legend-present">Present</span>
                            <span class="legend-absent">Absent</span>
                            <span class="legend-leave">Leave</span>
                            <span class="legend-holiday">Holiday</span>
                            <span class="legend-rest">Rest Day</span>
                            <span class="legend-pending">Pending</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form id="attendanceDraftForm" method="POST" action="{{ route('hr.attendance.store') }}" data-attendance-no-preload="true">
                        @csrf
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="cutoff_period" value="{{ $cutoffPeriod }}">
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">

                    <div class="attendance-table-wrap">
                        <table class="table attendance-encoding-table align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>Employee Name</th>
                                    <th>Designation</th>
                                    <th class="text-end">Rate/Day</th>
                                    <th class="text-end">Rate/Hour</th>

                                    @foreach($cutoffDates as $date)
                                        <th class="attendance-day-head {{ $date->isSunday() ? 'text-primary' : '' }}">
                                            <span>{{ $date->format('M j') }}</span>
                                            <small>{{ $date->format('D') }}</small>
                                        </th>
                                    @endforeach

                                    <th class="text-center">Present<br>Days</th>
                                    <th class="text-center">Late<br>(min)</th>
                                    <th class="text-center">Absent<br>Days</th>
                                    <th class="text-center">Paid Leave<br>Days</th>
                                    <th class="text-end">Holiday<br>Pay</th>
                                    <th class="text-end">OT<br>Paid</th>
                                    <th class="text-end">Grand<br>Total</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php
                                        $employeeRecords = $attendanceMatrix->get($employee->id, collect());
                                        $dailyRate = (float) ($employee->employee_rate ?? $employee->salary ?? 0);
                                        $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
                                        $presentDays = 0;
                                        $absentDays = 0;
                                        $lateMinutes = 0;
                                        $paidLeaveDays = 0;
                                        $holidayPay = 0;
                                        $overtimePay = 0;
                                        $grandTotal = 0;
                                        $employeeOvertimeRecords = $approvedOvertimeMatrix->get($employee->id, collect());
                                        $restDayOvertimePay = $employeeOvertimeRecords->filter(function ($approvedOvertime, $dateKey) {
                                            return \Carbon\Carbon::parse($dateKey)->isSunday();
                                        })->sum(function ($approvedOvertime) {
                                            return (float) ($approvedOvertime['amount'] ?? 0);
                                        });
                                    @endphp

                                    <tr class="attendance-employee-row" data-daily-rate="{{ $dailyRate }}" data-rest-ot-pay="{{ $restDayOvertimePay }}">
                                        <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                        <td class="attendance-employee-cell">
                                            <div class="fw-bold text-dark">
                                                {{ $employee->user->last_name ?? '' }}, {{ $employee->user->first_name ?? '' }}
                                                @if(!empty($employee->user?->middle_name))
                                                    {{ mb_substr($employee->user->middle_name, 0, 1) }}.
                                                @endif
                                            </div>
                                            <small class="text-secondary">{{ $employee->employee_id ?? 'No Employee No.' }}</small>
                                        </td>
                                        <td>{{ $employee->position->name ?? 'N/A' }}</td>
                                        <td class="text-end fw-semibold">₱{{ number_format($dailyRate, 2) }}</td>
                                        <td class="text-end fw-semibold">₱{{ number_format($hourlyRate, 2) }}</td>

                                        @foreach($cutoffDates as $date)
                                            @php
                                                $dateKey = $date->format('Y-m-d');
                                                $record = $employeeRecords->get($dateKey);
                                                $approvedOvertime = $employeeOvertimeRecords->get($dateKey);
                                                $approvedOvertimePay = $approvedOvertime ? (float) ($approvedOvertime['amount'] ?? 0) : 0;
                                                $approvedOvertimeHours = $approvedOvertime ? (float) ($approvedOvertime['hours'] ?? 0) : 0;
                                                $isSunday = $date->isSunday();
                                                $status = $record->status ?? null;
                                                $badgeClass = $isSunday ? 'att-rest' : ($statusClasses[$status] ?? 'att-empty');
                                                $label = $isSunday ? 'Rest Day' : ($statusLabels[$status] ?? 'Pending');

                                                if (!$isSunday && $record) {
                                                    if (in_array($status, ['present', 'late'], true)) {
                                                        $presentDays += 1;
                                                        $grandTotal += $dailyRate;
                                                    } elseif ($status === 'half_day') {
                                                        $presentDays += 0.5;
                                                        $grandTotal += $dailyRate / 2;
                                                    } elseif ($status === 'leave') {
                                                        $paidLeaveDays += 1;
                                                        $grandTotal += $dailyRate;
                                                    } elseif (in_array($status, ['holiday', 'regular_holiday', 'regular_holiday_worked', 'special_holiday', 'special_holiday_worked', 'special_working_present', 'special_working_absent'], true)) {
                                                        $holidayAmount = match ($status) {
                                                            'holiday', 'regular_holiday' => $dailyRate,
                                                            'regular_holiday_worked' => $dailyRate * 2,
                                                            'special_holiday' => 0,
                                                            'special_holiday_worked' => $dailyRate * 1.3,
                                                            'special_working_present' => $dailyRate,
                                                            default => 0,
                                                        };
                                                        $holidayPay += $holidayAmount;
                                                        $grandTotal += $holidayAmount;

                                                        if (in_array($status, ['regular_holiday_worked', 'special_holiday_worked', 'special_working_present'], true)) {
                                                            $presentDays += 1;
                                                        }
                                                    } elseif ($status === 'absent' || $status === 'special_working_absent') {
                                                        $absentDays += 1;
                                                    }

                                                    $lateMinutes += (int) ($record->late_minutes ?? 0);
                                                }

                                                if ($approvedOvertimePay > 0) {
                                                    $overtimePay += $approvedOvertimePay;
                                                    $grandTotal += $approvedOvertimePay;
                                                }
                                            @endphp

                                            <td class="attendance-date-cell {{ $isSunday ? '' : 'is-editing' }} {{ $isSubmitted ? 'attendance-locked-cell' : '' }}">
                                                @if($isSunday)
                                                    <div class="attendance-time-badge att-rest">
                                                        <span>Rest Day</span>
                                                        @if($approvedOvertimePay > 0)
                                                            <small class="attendance-auto-badge ot-approved">OT ₱{{ number_format($approvedOvertimePay, 2) }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    @php
                                                        $isApprovedLeave = (bool) ($record->is_approved_leave ?? false);
                                                        $isAutoHoliday = (bool) ($record->is_auto_holiday ?? false);
                                                        $stackStatusClass = match ($status) {
                                                            'present', 'late', 'half_day' => 'status-present',
                                                            'absent', 'special_working_absent' => 'status-absent',
                                                            'leave', 'leave_wop' => 'status-leave',
                                                            'holiday', 'regular_holiday', 'regular_holiday_worked', 'special_holiday', 'special_holiday_worked', 'special_working_present' => 'status-holiday',
                                                            'rest_day' => 'status-rest',
                                                            default => 'status-pending',
                                                        };
                                                        $autoHolidayType = $record->auto_holiday_type ?? null;
                                                        $autoHolidayTypeLabel = $record->auto_holiday_type_label ?? ($record->auto_holiday_type ?? 'Holiday');
                                                        $holidayHoverTitle = $isAutoHoliday
                                                            ? 'Holiday: ' . ($record->auto_holiday_name ?? 'Holiday') . ' (' . $autoHolidayTypeLabel . ')'
                                                            : 'Status';
                                                        $isHolidayStatus = in_array($status, ['holiday', 'regular_holiday', 'regular_holiday_worked', 'special_holiday', 'special_holiday_worked', 'special_working_present', 'special_working_absent'], true);
                                                        $isHolidayWorkedStatus = in_array($status, ['regular_holiday_worked', 'special_holiday_worked', 'special_working_present'], true);
                                                        $isAutoHolidayLocked = $isAutoHoliday && $isHolidayStatus;
                                                        $isDayLocked = $isSubmitted || $isApprovedLeave;
                                                        $stackLockReason = $isApprovedLeave
                                                            ? 'Approved leave request already reflected.'
                                                            : ($isAutoHolidayLocked
                                                                ? $holidayHoverTitle . ''
                                                                : $holidayHoverTitle);
                                                    @endphp
                                                    <div class="attendance-entry-stack {{ $stackStatusClass }} {{ $isApprovedLeave ? 'is-approved-leave' : '' }} {{ $isAutoHolidayLocked ? 'is-auto-holiday-locked' : '' }}"
                                                         data-approved-leave="{{ $isApprovedLeave ? '1' : '0' }}"
                                                         data-auto-holiday="{{ $isAutoHoliday ? '1' : '0' }}"
                                                         data-auto-holiday-locked="{{ $isAutoHolidayLocked ? '1' : '0' }}"
                                                         data-locked="{{ $isDayLocked ? '1' : '0' }}"
                                                         data-holiday-type="{{ $autoHolidayType ?? '' }}"
                                                         data-ot-pay="{{ $approvedOvertimePay }}"
                                                         data-ot-hours="{{ $approvedOvertimeHours }}">
                                                        @if($isApprovedLeave)
                                                            <input type="hidden" name="attendance[{{ $employee->id }}][{{ $dateKey }}][status]" value="{{ $status }}">
                                                            <input type="hidden" name="attendance[{{ $employee->id }}][{{ $dateKey }}][remarks]" value="{{ $record->remarks ?? 'Approved leave' }}">
                                                        @elseif($isSubmitted && $isAutoHolidayLocked)
                                                            <input type="hidden" name="attendance[{{ $employee->id }}][{{ $dateKey }}][status]" value="{{ $status }}">
                                                            <input type="hidden" name="attendance[{{ $employee->id }}][{{ $dateKey }}][remarks]" value="{{ $record->remarks ?? ($record->auto_holiday_name ?? 'Holiday') }}">
                                                        @endif
                                                        <input type="time"
                                                               class="attendance-entry-input"
                                                               name="attendance[{{ $employee->id }}][{{ $dateKey }}][time_in]"
                                                               value="{{ !empty($record->time_in) ? \Carbon\Carbon::parse($record->time_in)->format('H:i') : '' }}"
                                                               title="{{ $stackLockReason }}"
                                                               {{ ($isDayLocked || ($isAutoHolidayLocked && !$isHolidayWorkedStatus)) ? 'disabled' : '' }}>

                                                        <input type="time"
                                                               class="attendance-entry-input"
                                                               name="attendance[{{ $employee->id }}][{{ $dateKey }}][time_out]"
                                                               value="{{ !empty($record->time_out) ? \Carbon\Carbon::parse($record->time_out)->format('H:i') : '' }}"
                                                               title="{{ $stackLockReason }}"
                                                               {{ ($isDayLocked || ($isAutoHolidayLocked && !$isHolidayWorkedStatus)) ? 'disabled' : '' }}>

                                                        <select class="attendance-entry-select attendance-status-select"
                                                                name="attendance[{{ $employee->id }}][{{ $dateKey }}][status]"
                                                                title="{{ $stackLockReason }}"
                                                                {{ $isDayLocked ? 'disabled' : '' }}>
                                                            @if($isAutoHolidayLocked && !$isApprovedLeave)
                                                                @if($autoHolidayType === 'regular' || in_array($status, ['regular_holiday', 'regular_holiday_worked'], true))
                                                                    <option value="regular_holiday" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Regular Holiday" title="Regular Holiday" {{ $status === 'regular_holiday' || $status === 'holiday' ? 'selected' : '' }}>Regular Holiday</option>
                                                                    <option value="regular_holiday_worked" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Regular Holiday Worked" title="Regular Holiday Worked" {{ $status === 'regular_holiday_worked' ? 'selected' : '' }}>Regular Holiday Worked</option>
                                                                @elseif($autoHolidayType === 'special_non_working' || in_array($status, ['special_holiday', 'special_holiday_worked'], true))
                                                                    <option value="special_holiday" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Special Non Working Holiday" title="Special Non Working Holiday" {{ $status === 'special_holiday' || $status === 'holiday' ? 'selected' : '' }}>Special Non Working Holiday</option>
                                                                    <option value="special_holiday_worked" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Special Non Working Holiday (Worked)" title="Special Non Working Holiday (Worked)" {{ $status === 'special_holiday_worked' ? 'selected' : '' }}>Special Non Working Holiday (Worked)</option>
                                                                @elseif($autoHolidayType === 'special_working' || in_array($status, ['special_working_present', 'special_working_absent'], true))
                                                                    <option value="special_working_present" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Special Working Holiday Present" title="Special Working Holiday Present" {{ $status === 'special_working_present' ? 'selected' : '' }}>Special Working Holiday Present</option>
                                                                    <option value="special_working_absent" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Special Working Holiday Absent" title="Special Working Holiday Absent" {{ $status === 'special_working_absent' || $status === 'holiday' ? 'selected' : '' }}>Special Working Holiday Absent</option>
                                                                @else
                                                                    <option value="holiday" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Holiday" title="Holiday" {{ $status === 'holiday' ? 'selected' : '' }}>Holiday</option>
                                                                    <option value="regular_holiday_worked" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Holiday Worked" title="Holiday Worked" {{ $status === 'regular_holiday_worked' ? 'selected' : '' }}>Holiday Worked</option>
                                                                @endif
                                                            @else
                                                                <option value="pending" {{ !$record ? 'selected' : '' }}>Pending</option>
                                                                <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Present</option>
                                                                <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                                                                <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                                <option value="half_day" {{ $status === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                                                @if($isApprovedLeave && $status === 'leave')
                                                                    <option value="leave" selected>Leave (WP)</option>
                                                                @endif
                                                                @if($isApprovedLeave && $status === 'leave_wop')
                                                                    <option value="leave_wop" selected>Leave (WOP)</option>
                                                                @endif
                                                                <option value="holiday" data-holiday-option="1" data-collapsed-label="Holiday" data-expanded-label="Holiday" title="Holiday" {{ $status === 'holiday' ? 'selected' : '' }}>Holiday</option>
                                                                <option value="rest_day" {{ $status === 'rest_day' ? 'selected' : '' }}>Rest Day</option>
                                                            @endif
                                                        </select>

                                                        <input type="text"
                                                               class="attendance-entry-input attendance-entry-remarks"
                                                               name="attendance[{{ $employee->id }}][{{ $dateKey }}][remarks]"
                                                               value="{{ $record->remarks ?? '' }}"
                                                               placeholder="Remarks"
                                                               title="{{ $stackLockReason }}"
                                                               {{ $isDayLocked ? 'disabled' : '' }}>


                                                        @if($approvedOvertimePay > 0)
                                                            <span class="attendance-auto-badge ot-approved">OT ₱{{ number_format($approvedOvertimePay, 2) }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="text-center fw-bold text-success row-present-days">{{ number_format($presentDays, 1) }}</td>
                                        <td class="text-center fw-bold text-danger row-late-minutes">{{ number_format($lateMinutes) }}</td>
                                        <td class="text-center fw-bold text-danger row-absent-days">{{ number_format($absentDays) }}</td>
                                        <td class="text-center fw-bold text-warning row-paid-leave-days">{{ number_format($paidLeaveDays, 1) }}</td>
                                        <td class="text-end fw-bold row-holiday-pay">₱{{ number_format($holidayPay, 2) }}</td>
                                        <td class="text-end fw-bold row-overtime-pay">₱{{ number_format($overtimePay, 2) }}</td>
                                        <td class="text-end fw-bold row-grand-total">₱{{ number_format($grandTotal, 2) }}</td>
                                        <td class="text-center">
                                            <div class="attendance-row-tools">
                                                <button type="button"
                                                        class="btn btn-sm attendance-row-action-btn attendance-mark-row-present"
                                                        title="Mark this row as present"
                                                        aria-label="Mark this row as present"
                                                        {{ $isSubmitted ? 'disabled' : '' }}>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M20 6L9 17L4 12"></path>
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm attendance-row-action-btn attendance-clear-row"
                                                        title="Clear this row to pending"
                                                        aria-label="Clear this row to pending"
                                                        {{ $isSubmitted ? 'disabled' : '' }}>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 12A9 9 0 1 0 6 5.3"></path>
                                                        <path d="M3 4V10H9"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 13 + $cutoffDates->count() }}" class="text-center text-secondary py-5">
                                            No employees found for the selected branch/department.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </form>
                </div>
            </div>

            <div class="card attendance-panel">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-1 fw-bold">Attendance Summary <span class="text-secondary fw-normal">(Auto Computed)</span></h5>
                </div>
                <div class="card-body">
                    <div class="attendance-summary-strip">
                        <div class="attendance-summary-box stat-green">
                            <small>Total Present Days</small>
                            <strong>{{ number_format($summary['present_days'] ?? 0, 1) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-red">
                            <small>Total Absent Days</small>
                            <strong>{{ number_format($summary['absent_days'] ?? 0) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-purple">
                            <small>Leave (WP / WOP)</small>
                            <strong id="summaryLeaveDays">{{ number_format($summary['leave_with_pay_days'] ?? 0) }} / {{ number_format($summary['leave_without_pay_days'] ?? 0) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-green">
                            <small>Paid Leave Days</small>
                            <strong id="summaryPaidLeaveDays">{{ number_format($summary['paid_leave_days'] ?? 0, 1) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-blue">
                            <small>OT Paid</small>
                            <strong id="summaryOvertimePay">₱{{ number_format($summary['overtime_pay'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-orange">
                            <small>Total Late Minutes</small>
                            <strong id="summaryLateMinutesBottom">{{ number_format($summary['late_minutes'] ?? 0) }} min</strong>
                        </div>
                        <div class="attendance-summary-box stat-blue">
                            <small>Holiday Pay</small>
                            <strong id="summaryHolidayPay">₱{{ number_format($summary['holiday_pay'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="attendance-summary-box stat-dark">
                            <small>Grand Total</small>
                            <strong id="summaryGrandTotalBottom">₱{{ number_format($summary['grand_total'] ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
        @else
            <div class="card rounded-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">My Attendance</h4>
                    <p class="mb-0 text-secondary">Your attendance records only.</p>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Late</th>
                                    <th>Undertime</th>
                                    <th>OT</th>
                                    <th>Worked</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceRecords as $record)
                                    <tr>
                                        <td>{{ $record->attendance_date ? $record->attendance_date->format('M d, Y') : '-' }}</td>
                                        <td>{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('g:i A') : '-' }}</td>
                                        <td>{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('g:i A') : '-' }}</td>
                                        <td>{{ $record->late_minutes ?? 0 }} min</td>
                                        <td>{{ $record->undertime_minutes ?? 0 }} min</td>
                                        <td>{{ number_format((float) ($record->overtime_hours ?? 0), 2) }} hr</td>
                                        <td>{{ number_format((float) ($record->total_worked_hours ?? 0), 2) }} hr</td>
                                        <td><span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary py-4">No attendance records found.</td>
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
        @endif
    </div>

    @if($canManageAttendance)
        <script>
            (function () {
                let attendanceAjaxController = null;

                function suppressAttendancePreloader() {
                    document.documentElement.classList.add('attendance-no-preload');
                    document.body.classList.add('attendance-no-preload');
                    document.body.classList.remove('attendance-no-preload-feedback');
                    document.documentElement.style.cursor = 'auto';
                    document.body.style.cursor = 'auto';

                    const loaderSelectors = [
                        '.preloader',
                        '#preloader',
                        '#loading',
                        '#loading-center',
                        '.loading',
                        '.loading-overlay',
                        '.loader',
                        '.loader-wrapper',
                        '.page-loader',
                        '.iq-loader',
                        '.iq-loader-box',
                        '.iq-preloader',
                        '.pace',
                        '[data-loader="true"]'
                    ];

                    loaderSelectors.forEach(function (selector) {
                        document.querySelectorAll(selector).forEach(function (loader) {
                            loader.style.setProperty('display', 'none', 'important');
                            loader.style.setProperty('opacity', '0', 'important');
                            loader.style.setProperty('visibility', 'hidden', 'important');
                            loader.style.setProperty('pointer-events', 'none', 'important');
                        });
                    });
                }

                function bindOnce(element, eventName, handler, key) {
                    if (!element) {
                        return;
                    }

                    const bindKey = 'attendanceBound' + key;
                    if (element.dataset[bindKey] === '1') {
                        return;
                    }

                    element.dataset[bindKey] = '1';
                    element.addEventListener(eventName, handler);
                }

                function notifyAttendance(message, icon) {
                    if (!message) {
                        return;
                    }

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: icon || 'success',
                            title: message,
                            showConfirmButton: false,
                            timer: 1700,
                            timerProgressBar: true
                        });
                        return;
                    }

                    console.log(message);
                }

                function getWorkspaceCard() {
                    return document.querySelector('.attendance-workspace-card');
                }

                function replaceAttendanceWorkspaceFromHtml(html, newUrl) {
                    const parser = new DOMParser();
                    const parsed = parser.parseFromString(html, 'text/html');
                    const newWorkspace = parsed.querySelector('.attendance-workspace-card');
                    const currentWorkspace = getWorkspaceCard();

                    if (!newWorkspace || !currentWorkspace) {
                        return false;
                    }

                    currentWorkspace.replaceWith(newWorkspace);

                    if (newUrl) {
                        window.history.pushState({ attendanceAjax: true }, '', newUrl);
                    }

                    suppressAttendancePreloader();
                    window.initAttendanceRealtimePage();
                    return true;
                }

                async function fetchAttendancePage(url, pushUrl) {
                    suppressAttendancePreloader();

                    if (attendanceAjaxController) {
                        attendanceAjaxController.abort();
                    }

                    attendanceAjaxController = new AbortController();

                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html, application/xhtml+xml'
                            },
                            credentials: 'same-origin',
                            signal: attendanceAjaxController.signal,
                            cache: 'no-store'
                        });

                        const html = await response.text();

                        if (!response.ok || !replaceAttendanceWorkspaceFromHtml(html, pushUrl ? url : null)) {
                            window.location.href = url;
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            window.location.href = url;
                        }
                    }
                }

                function buildFilterUrl(form) {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();

                    formData.forEach(function (value, key) {
                        if (value !== null && value !== '') {
                            params.append(key, value);
                        }
                    });

                    const url = new URL(form.action, window.location.origin);
                    url.search = params.toString();
                    return url.toString();
                }

                function nativeSubmitWithAction(form, actionValue) {
                    if (!form) {
                        return;
                    }

                    const hiddenAction = document.createElement('input');
                    hiddenAction.type = 'hidden';
                    hiddenAction.name = 'attendance_action';
                    hiddenAction.value = actionValue;
                    form.appendChild(hiddenAction);
                    HTMLFormElement.prototype.submit.call(form);
                }

                function showAttendanceAjaxError(message) {
                    const finalMessage = message || 'Something went wrong while processing attendance. Please try again.';

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Attendance Error',
                            text: finalMessage
                        });
                        return;
                    }

                    alert(finalMessage);
                }

                async function readJsonSafely(response) {
                    try {
                        return await response.json();
                    } catch (error) {
                        return null;
                    }
                }

                async function submitAttendanceDraft(actionValue) {
                    const form = document.getElementById('attendanceDraftForm');

                    if (!form) {
                        return;
                    }

                    suppressAttendancePreloader();

                    const formData = new FormData(form);
                    formData.set('attendance_action', actionValue);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            cache: 'no-store'
                        });

                        const contentType = response.headers.get('content-type') || '';

                        if (contentType.includes('application/json')) {
                            const json = await readJsonSafely(response);

                            if (!response.ok || !json || json.success === false) {
                                const firstError = json && json.errors
                                    ? Object.values(json.errors).flat()[0]
                                    : null;
                                showAttendanceAjaxError(firstError || (json ? json.message : null));
                                return;
                            }

                            notifyAttendance(json.message || (actionValue === 'submit_to_payroll' ? 'Submitted to payroll.' : 'Attendance draft saved.'));

                            if (json.redirect) {
                                fetchAttendancePage(json.redirect, true);
                            }

                            return;
                        }

                        const html = await response.text();

                        if (!response.ok || !replaceAttendanceWorkspaceFromHtml(html, response.url || window.location.href)) {
                            nativeSubmitWithAction(form, actionValue);
                            return;
                        }

                        notifyAttendance(actionValue === 'submit_to_payroll' ? 'Submitted to payroll.' : 'Attendance draft saved.');
                    } catch (error) {
                        nativeSubmitWithAction(form, actionValue);
                    }
                }

                async function exportAttendanceExcel() {
                    const form = document.getElementById('attendanceDraftForm');

                    if (!form) {
                        return;
                    }

                    suppressAttendancePreloader();

                    const formData = new FormData(form);
                    formData.set('attendance_action', 'export_excel');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            cache: 'no-store'
                        });

                        const contentType = response.headers.get('content-type') || '';

                        if (!response.ok || contentType.includes('text/html')) {
                            const html = await response.text();
                            if (!replaceAttendanceWorkspaceFromHtml(html, response.url || window.location.href)) {
                                nativeSubmitWithAction(form, 'export_excel');
                            }
                            return;
                        }

                        const blob = await response.blob();
                        const disposition = response.headers.get('content-disposition') || '';
                        const filenameMatch = disposition.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
                        const filename = filenameMatch
                            ? decodeURIComponent(filenameMatch[1] || filenameMatch[2])
                            : 'attendance-export.xls';

                        const objectUrl = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = objectUrl;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        URL.revokeObjectURL(objectUrl);

                        notifyAttendance('Excel export downloaded.');
                    } catch (error) {
                        nativeSubmitWithAction(form, 'export_excel');
                    }
                }

                const moneyFormatter = new Intl.NumberFormat('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                function parseMinutes(value) {
                    if (!value || !value.includes(':')) {
                        return null;
                    }

                    const parts = value.split(':');
                    return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
                }

                function formatMoney(value) {
                    return '₱' + moneyFormatter.format(Number(value || 0));
                }

                function setText(selector, value) {
                    const element = document.querySelector(selector);
                    if (element) {
                        element.innerHTML = value;
                    }
                }

                function getStatusColorClass(status) {
                    if (status === 'present' || status === 'half_day') {
                        return 'status-present';
                    }

                    if (status === 'late') {
                        return 'status-late';
                    }

                    if (status === 'absent' || status === 'special_working_absent') {
                        return 'status-absent';
                    }

                    if (status === 'leave' || status === 'leave_wop') {
                        return 'status-leave';
                    }

                    if (['holiday', 'regular_holiday', 'regular_holiday_worked', 'special_holiday', 'special_holiday_worked', 'special_working_present'].includes(status)) {
                        return 'status-holiday';
                    }

                    if (status === 'rest_day') {
                        return 'status-rest';
                    }

                    return 'status-pending';
                }

                const attendanceHolidayStatusLabels = {
                    holiday: 'Holiday',
                    regular_holiday: 'Regular Holiday',
                    regular_holiday_worked: 'Regular Holiday Worked',
                    special_holiday: 'Special Non Working Holiday',
                    special_holiday_worked: 'Special Non Working Holiday (Worked)',
                    special_working_present: 'Special Working Holiday Present',
                    special_working_absent: 'Special Working Holiday Absent'
                };

                function isAttendanceHolidayStatus(status) {
                    return Object.prototype.hasOwnProperty.call(attendanceHolidayStatusLabels, status || '');
                }

                function expandHolidayStatusSelect(select) {
                    if (!select) {
                        return;
                    }

                    Array.from(select.options).forEach(function (option) {
                        if (option.dataset.holidayOption === '1') {
                            option.textContent = option.dataset.expandedLabel || attendanceHolidayStatusLabels[option.value] || 'Holiday';
                        }
                    });
                }

                function collapseHolidayStatusSelect(select) {
                    if (!select) {
                        return;
                    }

                    const selected = select.options[select.selectedIndex];
                    const selectedValue = select.value;

                    expandHolidayStatusSelect(select);

                    if (selected && isAttendanceHolidayStatus(selectedValue)) {
                        const fullLabel = selected.dataset.expandedLabel || attendanceHolidayStatusLabels[selectedValue] || 'Holiday';
                        selected.textContent = selected.dataset.collapsedLabel || 'Holiday';

                        if (!select.dataset.baseTitle) {
                            select.dataset.baseTitle = select.getAttribute('title') || '';
                        }

                        const baseTitle = select.dataset.baseTitle || 'Status';
                        select.setAttribute('title', fullLabel + (baseTitle && baseTitle !== 'Status' ? ' • ' + baseTitle : ''));
                    } else if (select.dataset.baseTitle) {
                        select.setAttribute('title', select.dataset.baseTitle);
                    }
                }

                function applyStackStatusColor(stack) {
                    const statusSelect = stack.querySelector('select');
                    const status = statusSelect ? statusSelect.value : 'pending';

                    stack.classList.remove('status-present', 'status-late', 'status-absent', 'status-leave', 'status-holiday', 'status-rest', 'status-pending');
                    stack.classList.add(getStatusColorClass(status));
                }

                function computeStack(stack, dailyRate) {
                    applyStackStatusColor(stack);
                    const timeInputs = stack.querySelectorAll('input[type="time"]');
                    const statusSelect = stack.querySelector('select');
                    const status = statusSelect ? statusSelect.value : 'pending';
                    const timeIn = parseMinutes(timeInputs[0] ? timeInputs[0].value : null);
                    const timeOut = parseMinutes(timeInputs[1] ? timeInputs[1].value : null);

                    let presentDays = 0;
                    let absentDays = 0;
                    let lateMinutes = 0;
                    let undertimeMinutes = 0;
                    let leaveWp = 0;
                    let leaveWop = 0;
                    let holidayPay = 0;
                    let overtimePay = Number(stack.dataset.otPay || 0);
                    let grandTotal = overtimePay;
                    let pending = 0;

                    if (status === 'pending') {
                        pending = 1;
                    } else if (status === 'present' || status === 'late') {
                        presentDays = 1;
                        grandTotal += dailyRate;

                        if (timeIn !== null && timeIn > 480) {
                            lateMinutes = timeIn - 480;
                        }

                        if (timeOut !== null && timeOut < 1020) {
                            undertimeMinutes = 1020 - timeOut;
                        }
                    } else if (status === 'half_day') {
                        presentDays = 0.5;
                        grandTotal += dailyRate / 2;

                        if (timeIn !== null && timeIn > 480) {
                            lateMinutes = timeIn - 480;
                        }
                    } else if (status === 'absent' || status === 'special_working_absent') {
                        absentDays = 1;
                    } else if (status === 'leave') {
                        leaveWp = 1;
                        grandTotal += dailyRate;
                    } else if (status === 'leave_wop') {
                        leaveWop = 1;
                    } else if (['holiday', 'regular_holiday', 'regular_holiday_worked', 'special_holiday', 'special_holiday_worked', 'special_working_present', 'special_working_absent'].includes(status)) {
                        const holidayAmounts = {
                            holiday: dailyRate,
                            regular_holiday: dailyRate,
                            regular_holiday_worked: dailyRate * 2,
                            special_holiday: 0,
                            special_holiday_worked: dailyRate * 1.3,
                            special_working_present: dailyRate,
                            special_working_absent: 0
                        };
                        holidayPay = Number(holidayAmounts[status] || 0);
                        grandTotal += holidayPay;

                        if (['regular_holiday_worked', 'special_holiday_worked', 'special_working_present'].includes(status)) {
                            presentDays = 1;
                        }

                        if (status === 'special_working_absent') {
                            absentDays = 1;
                        }
                    } else if (status === 'rest_day') {
                        // Manual rest day on a weekday: no pay, no absent, no pending.
                    }

                    return {
                        presentDays,
                        absentDays,
                        lateMinutes,
                        undertimeMinutes,
                        leaveWp,
                        leaveWop,
                        holidayPay,
                        overtimePay,
                        grandTotal,
                        pending
                    };
                }

                function recomputeRow(row) {
                    const dailyRate = Number(row.dataset.dailyRate || 0);
                    const totals = {
                        presentDays: 0,
                        absentDays: 0,
                        lateMinutes: 0,
                        undertimeMinutes: 0,
                        leaveWp: 0,
                        leaveWop: 0,
                        holidayPay: 0,
                        overtimePay: 0,
                        grandTotal: 0,
                        pending: 0
                    };

                    totals.overtimePay += Number(row.dataset.restOtPay || 0);
                    totals.grandTotal += Number(row.dataset.restOtPay || 0);

                    row.querySelectorAll('.attendance-entry-stack').forEach(function (stack) {
                        const computed = computeStack(stack, dailyRate);

                        Object.keys(totals).forEach(function (key) {
                            totals[key] += computed[key];
                        });
                    });

                    const presentCell = row.querySelector('.row-present-days');
                    const lateCell = row.querySelector('.row-late-minutes');
                    const absentCell = row.querySelector('.row-absent-days');
                    const paidLeaveCell = row.querySelector('.row-paid-leave-days');
                    const holidayCell = row.querySelector('.row-holiday-pay');
                    const overtimeCell = row.querySelector('.row-overtime-pay');
                    const grandCell = row.querySelector('.row-grand-total');

                    if (presentCell) presentCell.textContent = totals.presentDays.toFixed(1);
                    if (lateCell) lateCell.textContent = Math.round(totals.lateMinutes).toLocaleString();
                    if (absentCell) absentCell.textContent = Math.round(totals.absentDays).toLocaleString();
                    if (paidLeaveCell) paidLeaveCell.textContent = totals.leaveWp.toFixed(1);
                    if (holidayCell) holidayCell.textContent = formatMoney(totals.holidayPay);
                    if (overtimeCell) overtimeCell.textContent = formatMoney(totals.overtimePay);
                    if (grandCell) grandCell.textContent = formatMoney(totals.grandTotal);

                    row.dataset.presentDays = totals.presentDays;
                    row.dataset.absentDays = totals.absentDays;
                    row.dataset.lateMinutes = totals.lateMinutes;
                    row.dataset.undertimeMinutes = totals.undertimeMinutes;
                    row.dataset.leaveWp = totals.leaveWp;
                    row.dataset.leaveWop = totals.leaveWop;
                    row.dataset.holidayPay = totals.holidayPay;
                    row.dataset.overtimePay = totals.overtimePay;
                    row.dataset.grandTotal = totals.grandTotal;
                    row.dataset.pending = totals.pending;

                    return totals;
                }

                function recomputePageSummary() {
                    const totals = {
                        presentDays: 0,
                        absentDays: 0,
                        lateMinutes: 0,
                        undertimeMinutes: 0,
                        leaveWp: 0,
                        leaveWop: 0,
                        holidayPay: 0,
                        overtimePay: 0,
                        grandTotal: 0,
                        pending: 0
                    };

                    document.querySelectorAll('.attendance-employee-row').forEach(function (row) {
                        const rowTotals = recomputeRow(row);

                        Object.keys(totals).forEach(function (key) {
                            totals[key] += rowTotals[key];
                        });
                    });

                    setText('#summaryPresentDays', totals.presentDays.toFixed(1));
                    setText('#summaryAbsentDays', Math.round(totals.absentDays).toLocaleString());
                    setText('#summaryLateMinutes', Math.round(totals.lateMinutes).toLocaleString() + ' <small class="fs-6">min</small>');
                    setText('#summaryPendingEntries', Math.round(totals.pending).toLocaleString());
                    setText('#summaryGrandTotal', formatMoney(totals.grandTotal));
                    setText('#summaryLeaveDays', Math.round(totals.leaveWp).toLocaleString() + ' / ' + Math.round(totals.leaveWop).toLocaleString());
                    setText('#summaryPaidLeaveDays', totals.leaveWp.toFixed(1));
                    setText('#summaryOvertimePay', formatMoney(totals.overtimePay));
                    setText('#summaryLateMinutesBottom', Math.round(totals.lateMinutes).toLocaleString() + ' min');
                    setText('#summaryHolidayPay', formatMoney(totals.holidayPay));
                    setText('#summaryGrandTotalBottom', formatMoney(totals.grandTotal));

                    const submitButton = document.getElementById('submitToPayrollButton');
                    const attendanceIsSubmitted = !!document.querySelector('.attendance-lock-banner');

                    if (submitButton && !attendanceIsSubmitted) {
                        const canSubmit = Math.round(totals.pending) === 0 && document.querySelectorAll('.attendance-employee-row').length > 0;
                        submitButton.disabled = !canSubmit;
                        submitButton.title = canSubmit
                            ? 'Submit and lock this cut-off'
                            : 'Complete all pending entries before submitting';
                    }
                }

                window.initAttendanceRealtimePage = function () {
                    suppressAttendancePreloader();

                    const filterForm = document.getElementById('attendanceFilterForm');

                    if (filterForm) {
                        document.querySelectorAll('.attendance-auto-submit').forEach(function (field) {
                            bindOnce(field, 'change', function () {
                                fetchAttendancePage(buildFilterUrl(filterForm), true);
                            }, 'Filter');
                        });

                        document.querySelectorAll('.attendance-auto-submit-radio').forEach(function (field) {
                            bindOnce(field, 'change', function () {
                                fetchAttendancePage(buildFilterUrl(filterForm), true);
                            }, 'FilterRadio');
                        });

                        bindOnce(filterForm, 'submit', function (event) {
                            event.preventDefault();
                            fetchAttendancePage(buildFilterUrl(filterForm), true);
                        }, 'FilterSubmit');
                    }

                    const draftForm = document.getElementById('attendanceDraftForm');
                    if (draftForm) {
                        bindOnce(draftForm, 'submit', function (event) {
                            event.preventDefault();
                        }, 'DraftSubmit');
                    }

                    document.querySelectorAll('button[form="attendanceDraftForm"][name="attendance_action"]').forEach(function (button) {
                        bindOnce(button, 'click', function (event) {
                            event.preventDefault();

                            const actionValue = button.value;

                            if (actionValue === 'export_excel') {
                                exportAttendanceExcel();
                                return;
                            }

                            if (actionValue === 'submit_to_payroll') {
                                const message = 'Submit this attendance cut-off to payroll? After submitting, the records will be locked.';

                                if (window.Swal) {
                                    Swal.fire({
                                        title: 'Submit to Payroll?',
                                        text: message,
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, submit',
                                        cancelButtonText: 'Cancel'
                                    }).then(function (result) {
                                        if (result.isConfirmed) {
                                            submitAttendanceDraft('submit_to_payroll');
                                        }
                                    });
                                    return;
                                }

                                if (!confirm(message)) {
                                    return;
                                }
                            }

                            submitAttendanceDraft(actionValue || 'save_draft');
                        }, 'ActionButton');
                    });

                    function isAttendanceStackLocked(stack) {
                        if (!stack) {
                            return true;
                        }

                        return stack.dataset.approvedLeave === '1'
                            || stack.dataset.autoHolidayLocked === '1'
                            || stack.dataset.locked === '1'
                            || stack.classList.contains('is-auto-holiday-locked')
                            || stack.classList.contains('is-approved-leave');
                    }

                    document.querySelectorAll('.attendance-mark-row-present').forEach(function (button) {
                        bindOnce(button, 'click', function () {
                            const row = button.closest('tr');

                            if (!row) {
                                return;
                            }

                            row.querySelectorAll('.attendance-entry-stack').forEach(function (stack) {
                                if (isAttendanceStackLocked(stack)) {
                                    return;
                                }

                                const timeIn = stack.querySelector('input[type="time"]:first-of-type');
                                const timeOut = stack.querySelectorAll('input[type="time"]')[1];
                                const status = stack.querySelector('select');

                                if (timeIn && !timeIn.value) {
                                    timeIn.value = '08:00';
                                }

                                if (timeOut && !timeOut.value) {
                                    timeOut.value = '17:00';
                                }

                                if (status) {
                                    status.value = 'present';
                                }

                                applyStackStatusColor(stack);
                            });

                            recomputePageSummary();
                        }, 'MarkPresent');
                    });

                    document.querySelectorAll('.attendance-clear-row').forEach(function (button) {
                        bindOnce(button, 'click', function () {
                            const row = button.closest('tr');

                            if (!row) {
                                return;
                            }

                            row.querySelectorAll('.attendance-entry-stack').forEach(function (stack) {
                                if (isAttendanceStackLocked(stack)) {
                                    return;
                                }

                                stack.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
                                    input.value = '';
                                });

                                const status = stack.querySelector('select');

                                if (status) {
                                    status.value = 'pending';
                                }

                                applyStackStatusColor(stack);
                            });

                            recomputePageSummary();
                        }, 'ClearRow');
                    });

                    document.querySelectorAll('.attendance-status-select').forEach(function (select) {
                        collapseHolidayStatusSelect(select);

                        bindOnce(select, 'mousedown', function () {
                            expandHolidayStatusSelect(select);
                        }, 'HolidayExpandMouse');

                        bindOnce(select, 'focus', function () {
                            expandHolidayStatusSelect(select);
                        }, 'HolidayExpandFocus');

                        bindOnce(select, 'blur', function () {
                            collapseHolidayStatusSelect(select);
                        }, 'HolidayCollapseBlur');

                        bindOnce(select, 'change', function () {
                            const stack = select.closest('.attendance-entry-stack');

                            if (!stack) {
                                return;
                            }

                            const timeInputs = stack.querySelectorAll('input[type="time"]');

                            const noTimeStatuses = ['absent', 'leave', 'leave_wop', 'holiday', 'regular_holiday', 'special_holiday', 'special_working_absent', 'rest_day', 'pending'];
                            const workedHolidayStatuses = ['regular_holiday_worked', 'special_holiday_worked', 'special_working_present'];

                            if (noTimeStatuses.includes(select.value)) {
                                timeInputs.forEach(function (input) {
                                    input.value = '';
                                    if (stack.dataset.autoHolidayLocked === '1') {
                                        input.disabled = true;
                                    }
                                });
                            }

                            if (workedHolidayStatuses.includes(select.value)) {
                                timeInputs.forEach(function (input) {
                                    if (stack.dataset.locked !== '1' && stack.dataset.approvedLeave !== '1') {
                                        input.disabled = false;
                                    }
                                });

                                if (timeInputs[0] && !timeInputs[0].value) {
                                    timeInputs[0].value = '08:00';
                                }

                                if (timeInputs[1] && !timeInputs[1].value) {
                                    timeInputs[1].value = '17:00';
                                }
                            }

                            if (select.value === 'present' || select.value === 'late') {
                                if (timeInputs[0] && !timeInputs[0].value) {
                                    timeInputs[0].value = select.value === 'late' ? '08:15' : '08:00';
                                }

                                if (timeInputs[1] && !timeInputs[1].value) {
                                    timeInputs[1].value = '17:00';
                                }
                            }

                            if (select.value === 'half_day') {
                                if (timeInputs[0] && !timeInputs[0].value) {
                                    timeInputs[0].value = '08:00';
                                }

                                if (timeInputs[1] && !timeInputs[1].value) {
                                    timeInputs[1].value = '12:00';
                                }
                            }

                            applyStackStatusColor(stack);
                            collapseHolidayStatusSelect(select);
                            recomputePageSummary();
                        }, 'StatusSelect');
                    });

                    document.querySelectorAll('.attendance-entry-input').forEach(function (input) {
                        bindOnce(input, 'change', recomputePageSummary, 'EntryChange');
                        bindOnce(input, 'input', recomputePageSummary, 'EntryInput');
                    });

                    document.querySelectorAll('.attendance-status-select').forEach(collapseHolidayStatusSelect);
                    document.querySelectorAll('.attendance-entry-stack').forEach(applyStackStatusColor);
                    recomputePageSummary();
                };

                window.addEventListener('popstate', function () {
                    fetchAttendancePage(window.location.href, false);
                });

                document.addEventListener('DOMContentLoaded', function () {
                    document.body.classList.add('attendance-no-preload');
                    suppressAttendancePreloader();
                    window.initAttendanceRealtimePage();
                });
            })();
        </script>
    @endif
</x-app-layout>
