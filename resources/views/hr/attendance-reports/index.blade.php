@php
    $exportMode = $exportMode ?? false;
    $exportFormat = $exportFormat ?? null;
    $reportCategories = [
        'summary' => ['title' => 'Attendance Summary', 'desc' => 'Summary of attendance by employee', 'icon' => 'summary'],
        'daily' => ['title' => 'Daily Attendance', 'desc' => 'Daily time in/out records', 'icon' => 'daily'],
        'employee' => ['title' => 'Employee Attendance', 'desc' => 'Individual employee attendance', 'icon' => 'employee'],
        'late_undertime' => ['title' => 'Late / Undertime', 'desc' => 'Late and undertime report', 'icon' => 'time'],
        'absences' => ['title' => 'Absences', 'desc' => 'Absences and leave without pay', 'icon' => 'absence'],
        'holiday_pay' => ['title' => 'Holiday Pay', 'desc' => 'Holiday pay computation', 'icon' => 'holiday'],
        'audit_trail' => ['title' => 'Audit Trail', 'desc' => 'Generated report activity', 'icon' => 'audit'],
    ];

    $reportSvgIcons = [
        'page' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3.75h6.2c.55 0 1.08.22 1.47.61l3.97 3.97c.39.39.61.92.61 1.47V20A2.25 2.25 0 0 1 17 22.25H7A2.25 2.25 0 0 1 4.75 20V6A2.25 2.25 0 0 1 7 3.75Zm6 1.75V9c0 .55.45 1 1 1h3.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.75 14h6.5M8.75 17h4.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'summary' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5h8M8 9h8M8 13h5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M6.75 21h10.5A2.75 2.75 0 0 0 20 18.25V5.75A2.75 2.75 0 0 0 17.25 3H6.75A2.75 2.75 0 0 0 4 5.75v12.5A2.75 2.75 0 0 0 6.75 21Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
        'daily' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3M17 3v3M4.5 9h15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M7.25 5h9.5A2.75 2.75 0 0 1 19.5 7.75v9.5A2.75 2.75 0 0 1 16.75 20h-9.5A2.75 2.75 0 0 1 4.5 17.25v-9.5A2.75 2.75 0 0 1 7.25 5Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 13h2.5M8 16h5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'employee' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.75 20.25c.7-3.25 3.35-5.25 7.25-5.25s6.55 2 7.25 5.25" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.5 12.5h2.75M18.88 11.13v2.75" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'time' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.25V12l3.25 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'absence' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.25 11.5a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5ZM3.75 20c.62-3.05 2.96-4.85 6.5-4.85 1.46 0 2.73.31 3.75.92" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="m16.25 16.25 4 4M20.25 16.25l-4 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'holiday' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v3M17 3v3M4.5 9h15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M7.25 5h9.5A2.75 2.75 0 0 1 19.5 7.75v9.5A2.75 2.75 0 0 1 16.75 20h-9.5A2.75 2.75 0 0 1 4.5 17.25v-9.5A2.75 2.75 0 0 1 7.25 5Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.25 14.25 2.15 2.15 5.35-5.35" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'audit' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.5 12a7.5 7.5 0 1 0 2.2-5.3L4.5 8.9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M4.5 4.75V8.9h4.15M12 7.75V12l3 1.75" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'excel' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3.75h10A2.25 2.25 0 0 1 19.25 6v12A2.25 2.25 0 0 1 17 20.25H7A2.25 2.25 0 0 1 4.75 18V6A2.25 2.25 0 0 1 7 3.75Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m8.75 8.75 6.5 6.5M15.25 8.75l-6.5 6.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'pdf' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3.75h6.25L19.25 9.75V18A2.25 2.25 0 0 1 17 20.25H7A2.25 2.25 0 0 1 4.75 18V6A2.25 2.25 0 0 1 7 3.75Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M13.25 3.95V9.75h5.8M8 15h8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        'print' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 8V4.75h9V8M7.5 17.25H6A2.5 2.5 0 0 1 3.5 14.75v-3A2.5 2.5 0 0 1 6 9.25h12a2.5 2.5 0 0 1 2.5 2.5v3a2.5 2.5 0 0 1-2.5 2.5h-1.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M7.5 14.25h9v6h-9z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M17.5 12.25h.01" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.75 18.25a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15ZM16.25 16.25l4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    ];
    $activeReport = $reportCategories[$selectedReportType] ?? $reportCategories['summary'];
    $baseQuery = request()->except(['format', 'export_format']);
    $currentReportQuery = [
        'report_type' => $selectedReportType,
        'month' => $selectedMonth,
        'cutoff_period' => $selectedPeriod,
        'branch_id' => $selectedBranchId,
        'department_id' => $selectedDepartmentId,
        'employee_profile_id' => $selectedEmployeeId,
        'group_by' => $selectedGroupBy,
    ];
    $currentReportQuery = array_filter($currentReportQuery, fn ($value) => $value !== null && $value !== '');
@endphp

@if($exportMode)
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $activeReport['title'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; font-size: 12px; margin: 0; }
        .report-shell { padding: 22px; }
        .report-header { text-align: center; margin-bottom: 20px; position: relative; }
        .brand { position: absolute; left: 0; top: 0; text-align: left; font-size: 20px; font-weight: 800; letter-spacing: 2px; }
        .brand small { display: block; font-size: 9px; color: #0ea5e9; letter-spacing: 4px; }
        h2 { margin: 0 0 5px; font-size: 18px; letter-spacing: .03em; }
        .muted, .text-secondary { color: #64748b; font-size: 11px; }
        .fw-bold { font-weight: 800; }
        .fw-semibold { font-weight: 600; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .mb-1 { margin-bottom: 4px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-3 { margin-top: 16px; }
        .mt-5 { margin-top: 46px; }
        .small { font-size: 11px; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dbe3ef; padding: 7px 8px; vertical-align: middle; }
        th { background: #f3f6fb; font-weight: 700; text-align: center; }
        tfoot td { font-weight: 800; background: #f8fafc; }
        .attendance-status-pill { border-radius: 999px; padding: 3px 7px; font-size: 10px; font-weight: 800; background: #eef2ff; color: #3342a6; }
        .signature-row { display: flex; justify-content: space-between; margin-top: 46px; }
        .signature-line { width: 34%; border-top: 1px solid #111827; padding-top: 7px; text-align: center; }
        @media print { .no-print { display: none !important; } body { margin: 0; } }
    </style>
</head>
<body @if($exportFormat === 'pdf' || $exportFormat === 'print') onload="window.print()" @endif>
<div class="report-shell">
@else
<x-app-layout>
    <style>
        html.attendance-reports-no-preload #loading,
        html.attendance-reports-no-preload #loading-center,
        html.attendance-reports-no-preload .preloader,
        html.attendance-reports-no-preload .loading,
        html.attendance-reports-no-preload .loading-overlay,
        html.attendance-reports-no-preload .loader,
        html.attendance-reports-no-preload .loader-wrapper,
        html.attendance-reports-no-preload .page-loader,
        html.attendance-reports-no-preload .iq-loader,
        html.attendance-reports-no-preload .iq-loader-box,
        html.attendance-reports-no-preload .iq-preloader,
        html.attendance-reports-no-preload .pace,
        body.attendance-reports-no-preload #loading,
        body.attendance-reports-no-preload #loading-center,
        body.attendance-reports-no-preload .preloader,
        body.attendance-reports-no-preload .loading,
        body.attendance-reports-no-preload .loading-overlay,
        body.attendance-reports-no-preload .loader,
        body.attendance-reports-no-preload .loader-wrapper,
        body.attendance-reports-no-preload .page-loader,
        body.attendance-reports-no-preload .iq-loader,
        body.attendance-reports-no-preload .iq-loader-box,
        body.attendance-reports-no-preload .iq-preloader,
        body.attendance-reports-no-preload .pace {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .attendance-report-shell { --wmc-blue: #0f62fe; --wmc-soft: #f6f8fc; overflow-x: hidden; }
        .attendance-report-card { border: 0; border-radius: 18px; box-shadow: 0 14px 38px rgba(15,23,42,.06); overflow: hidden; max-width: 100%; }
        .attendance-report-bordered-card { border: 1px solid #e5eaf3 !important; box-shadow: none !important; background: #fff; }
        .attendance-report-bordered-card .card-body { border-radius: 18px; }
        .attendance-report-section-title { display: flex; align-items: center; gap: .55rem; color: #0f172a; }
        .attendance-report-section-title .attendance-svg { font-size: 1rem; color: #3154d4; }
        .attendance-report-page-card { border-radius: 22px; }
        .attendance-report-page-card > .card-body { padding: 1.35rem 1.4rem 1.45rem !important; }
        .attendance-report-page-header { border-bottom: 1px solid #eef2f7; padding-bottom: 1.05rem; margin-bottom: 1.1rem; }
        .attendance-svg { width: 1em; height: 1em; display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .attendance-svg svg { width: 100%; height: 100%; display: block; }
        .attendance-report-title-icon .attendance-svg { font-size: 1.35rem; }
        .attendance-report-action .attendance-svg { font-size: .95rem; margin-right: .35rem; }
        .attendance-report-title-icon { width: 44px; height: 44px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: #3154d4; flex: 0 0 44px; }
        .attendance-report-card .card-body { padding: 1.15rem !important; }
        .attendance-report-layout { display: flex; flex-direction: column; gap: 18px; width: 100%; max-width: 100%; }
        .attendance-report-controls-row { display: grid; grid-template-columns: minmax(240px, .95fr) minmax(360px, 1.25fr) minmax(250px, 1fr); gap: 18px; align-items: stretch; width: 100%; max-width: 100%; }
        .attendance-report-controls-row > .card { height: 100%; }
        .attendance-report-category { border: 1px solid #edf2f7; border-radius: 12px; padding: .62rem .72rem; display: flex; align-items: center; gap: .65rem; color: #1f2937; text-decoration: none; transition: .18s ease; min-height: 55px; }
        .attendance-report-category:hover { border-color: #cfe0ff; background: #f8fbff; color: #0f62fe; }
        .attendance-report-category.active { border-color: #bdd5ff; background: #eff6ff; color: #0f62fe; box-shadow: inset 3px 0 0 #0f62fe; }
        .attendance-report-category-icon { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: #0f62fe; flex: 0 0 32px; }
        .attendance-report-category-icon .attendance-svg { font-size: 1rem; }
        .attendance-report-category.active .attendance-report-category-icon { background: #0f62fe; color: #fff; }
        .attendance-report-category-title { font-size: .78rem; font-weight: 800; line-height: 1.15; }
        .attendance-report-category-desc { font-size: .68rem; color: #64748b; line-height: 1.2; }
        .attendance-report-filter-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .attendance-report-filter-grid .form-label { color: #64748b; margin-bottom: .35rem; }
        .attendance-report-filter-grid .form-control,
        .attendance-report-filter-grid .form-select { min-height: 36px; font-size: .8rem; border-color: #e5ebf5; }
        .attendance-report-actions { display: grid; grid-template-columns: 1fr 105px; gap: 10px; }
        .attendance-report-action { border-radius: 10px; font-weight: 800; min-height: 38px; display: inline-flex; align-items: center; justify-content: center; }
        .attendance-last-generated { border: 1px solid #edf2f7; border-radius: 12px; padding: .72rem .82rem; }
        .attendance-last-generated .small { line-height: 1.25; }
        .attendance-report-preview { width: 100%; max-width: 100%; }
        .attendance-report-preview .card-body { padding: 1.05rem !important; }
        .attendance-report-paper { background: #fff; border: 1px solid #edf2f7; border-radius: 14px; padding: 16px; overflow-x: hidden; max-width: 100%; }
        .attendance-report-brand { font-weight: 900; letter-spacing: 2px; color: #0f172a; font-size: 1.25rem; line-height: 1; }
        .attendance-report-brand small { display: block; color: #0ea5e9; font-size: .52rem; letter-spacing: 4px; margin-top: 2px; }
        .attendance-report-table { min-width: 0 !important; width: 100% !important; max-width: 100%; table-layout: fixed; margin-bottom: 0; }
        .attendance-report-table th { background: #f4f7fb; color: #1f2937; font-size: .64rem; border-color: #e5ebf5; white-space: normal; word-break: normal; overflow-wrap: normal; text-align: center; padding: .5rem .28rem; line-height: 1.15; vertical-align: middle; }
        .attendance-report-table td { border-color: #edf2f7; font-size: .66rem; vertical-align: middle; white-space: nowrap; padding: .48rem .32rem; line-height: 1.2; }
        .attendance-report-table td:nth-child(2),
        .attendance-report-table td:nth-child(3) { overflow: hidden; text-overflow: ellipsis; }
        .attendance-report-table th:nth-child(1), .attendance-report-table td:nth-child(1) { width: 4%; }
        .attendance-report-table th:nth-child(2), .attendance-report-table td:nth-child(2) { width: 14%; }
        .attendance-report-table th:nth-child(3), .attendance-report-table td:nth-child(3) { width: 13%; }
        .attendance-report-table th:nth-child(4), .attendance-report-table td:nth-child(4) { width: 8%; }
        .attendance-report-table th:nth-child(5), .attendance-report-table td:nth-child(5) { width: 8%; }
        .attendance-report-table th:nth-child(6), .attendance-report-table td:nth-child(6) { width: 8%; }
        .attendance-report-table th:nth-child(7), .attendance-report-table td:nth-child(7) { width: 8%; }
        .attendance-report-table th:nth-child(8), .attendance-report-table td:nth-child(8) { width: 7%; }
        .attendance-report-table th:nth-child(9), .attendance-report-table td:nth-child(9) { width: 8%; }
        .attendance-report-table th:nth-child(10), .attendance-report-table td:nth-child(10) { width: 10%; }
        .attendance-report-table th:nth-child(11), .attendance-report-table td:nth-child(11) { width: 12%; }
        .attendance-report-table td:nth-child(4),
        .attendance-report-table td:nth-child(5),
        .attendance-report-table td:nth-child(6),
        .attendance-report-table td:nth-child(7),
        .attendance-report-table td:nth-child(8),
        .attendance-report-table td:nth-child(10),
        .attendance-report-table td:nth-child(11) {
            text-align: right !important;
        }

        .attendance-report-table th:nth-child(1),
        .attendance-report-table td:nth-child(1),
        .attendance-report-table th:nth-child(9),
        .attendance-report-table td:nth-child(9) {
            text-align: center !important;
        }
        .attendance-report-table tfoot td { background: #f8fafc; font-weight: 800; }
        .attendance-status-pill { border-radius: 999px; padding: 4px 8px; font-size: .7rem; font-weight: 800; background: #eef2ff; color: #3342a6; }
        .attendance-status-pill.absent, .attendance-status-pill.leave_wop, .attendance-status-pill.special_working_absent { background: #fee2e2; color: #b91c1c; }
        .attendance-status-pill.late { background: #fef3c7; color: #92400e; }
        .attendance-status-pill.present, .attendance-status-pill.leave { background: #dcfce7; color: #166534; }
        .signature-line { width: 34%; border-top: 1px solid #111827; padding-top: 7px; text-align: center; }

        /* FINAL FIX: Right-align No. and Undertime */
        .attendance-report-table th:nth-child(1),
        .attendance-report-table td:nth-child(1),
        .attendance-report-table th:nth-child(9),
        .attendance-report-table td:nth-child(9) {
            text-align: right !important;
        }

        @media (min-width: 1200px) { .attendance-report-shell { padding-left: 1.15rem !important; padding-right: 1.15rem !important; } }
        @media (max-width: 1399.98px) { .attendance-report-controls-row { grid-template-columns: 1fr 1.15fr 1fr; gap: 16px; } .attendance-report-paper { padding: 14px; } .attendance-report-table th { font-size: .59rem; padding-left: .2rem; padding-right: .2rem; } .attendance-report-table td { font-size: .61rem; padding-left: .24rem; padding-right: .24rem; } }
        @media (max-width: 1199.98px) { .attendance-report-controls-row { grid-template-columns: 1fr; } .attendance-report-categories-grid { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); } .attendance-report-paper { overflow-x: auto; } .attendance-report-table { min-width: 920px !important; table-layout: auto; } }
        @media (max-width: 767.98px) { .attendance-report-categories-grid { grid-template-columns: 1fr; } .attendance-report-filter-grid { grid-template-columns: 1fr; } .attendance-report-actions { grid-template-columns: 1fr; } .attendance-report-paper { padding: 14px; } }
        @media print { .iq-sidebar, .iq-navbar, .btn, .attendance-report-controls-row, .no-print { display: none !important; } .content-inner { padding: 0 !important; } .attendance-report-card { box-shadow: none !important; } }
    </style>

    <script>
        (function () {
            document.documentElement.classList.add('attendance-reports-no-preload');
            if (document.body) {
                document.body.classList.add('attendance-reports-no-preload');
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    document.body.classList.add('attendance-reports-no-preload');
                });
            }
        })();

        document.addEventListener('DOMContentLoaded', function () {
            var filterForm = document.getElementById('attendanceReportFilterForm');
            var exportUrl = @json(route('hr.attendance-reports.export'));

            document.querySelectorAll('[data-attendance-export-format]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    if (!filterForm) {
                        return;
                    }

                    event.preventDefault();

                    var params = new URLSearchParams(new FormData(filterForm));
                    params.set('format', button.getAttribute('data-attendance-export-format') || 'print');

                    var targetUrl = exportUrl + '?' + params.toString();
                    var target = button.getAttribute('target');

                    if (target === '_blank') {
                        window.open(targetUrl, '_blank');
                    } else {
                        window.location.href = targetUrl;
                    }
                });
            });
        });
    </script>

    <div class="container-fluid content-inner py-0 attendance-report-shell">
        <div class="card attendance-report-card attendance-report-page-card">
            <div class="card-body">
                <div class="attendance-report-page-header d-flex align-items-center gap-3">
                    <div class="attendance-report-title-icon">
                        <span class="attendance-svg">{!! $reportSvgIcons['page'] !!}</span>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">Reports</h3>
                        <p class="mb-0 text-secondary">Generate and export attendance reports</p>
                    </div>
                </div>

                <div class="attendance-report-layout">
            <div class="attendance-report-main">
                <div class="card attendance-report-card attendance-report-preview">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3 no-print">
                            <h5 class="fw-bold mb-0">{{ $activeReport['title'] }} Report</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('hr.attendance-reports.export', array_merge($currentReportQuery, ['format' => 'excel'])) }}" class="btn btn-outline-success btn-sm px-3 attendance-report-action" data-attendance-export-format="excel"><span class="attendance-svg">{!! $reportSvgIcons['excel'] !!}</span> Excel</a>
                                <a href="{{ route('hr.attendance-reports.export', array_merge($currentReportQuery, ['format' => 'pdf'])) }}" target="_blank" class="btn btn-outline-danger btn-sm px-3 attendance-report-action" data-attendance-export-format="pdf"><span class="attendance-svg">{!! $reportSvgIcons['pdf'] !!}</span> PDF</a>
                                <a href="{{ route('hr.attendance-reports.export', array_merge($currentReportQuery, ['format' => 'print'])) }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 attendance-report-action" data-attendance-export-format="print"><span class="attendance-svg">{!! $reportSvgIcons['print'] !!}</span> Print</a>
                            </div>
                        </div>
@endif

@if(!$exportMode)
                        <div class="attendance-report-paper">
@endif
                            <div class="report-header mb-4">
                                <h2 class="fw-bold text-center mb-1">{{ strtoupper($activeReport['title']) }} REPORT</h2>
                                <div class="text-center fw-semibold">{{ $cutoffStart->format('M j') }} - {{ $cutoffEnd->format('M j, Y') }} ({{ $periodLabel }})</div>
                                <div class="text-center text-secondary small">{{ $branchName }} • {{ $departmentName }}</div>
                                <div class="d-flex justify-content-between mt-3 small text-secondary">
                                    <span>Generated By: {{ auth()->user()->full_name ?? auth()->user()->name ?? 'HR Admin' }}</span>
                                    <span>Date Generated: {{ now()->format('M j, Y h:i A') }}</span>
                                </div>
                            </div>

                            @if($selectedReportType === 'daily')
                                @include('hr.attendance-reports.partials.daily-table', ['tableRows' => $dailyRows])
                            @elseif($selectedReportType === 'late_undertime')
                                @include('hr.attendance-reports.partials.daily-table', ['tableRows' => $lateUndertimeRows, 'lateOnly' => true])
                            @elseif($selectedReportType === 'absences')
                                @include('hr.attendance-reports.partials.daily-table', ['tableRows' => $absenceRows])
                            @elseif($selectedReportType === 'holiday_pay')
                                @include('hr.attendance-reports.partials.daily-table', ['tableRows' => $holidayRows, 'holidayOnly' => true])
                            @elseif($selectedReportType === 'audit_trail')
                                <table class="table table-bordered attendance-report-table">
                                    <thead><tr><th>No.</th><th>Activity</th><th>Report Type</th><th>Date/Time</th><th>Generated By</th></tr></thead>
                                    <tbody>
                                        @foreach($lastGeneratedReports as $i => $generated)
                                            <tr>
                                                <td class="text-center">{{ $i + 1 }}</td>
                                                <td>{{ $generated['title'] }}</td>
                                                <td class="text-center">{{ $generated['type'] }}</td>
                                                <td class="text-center">{{ $generated['time'] }}</td>
                                                <td>{{ auth()->user()->full_name ?? auth()->user()->name ?? 'HR Admin' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-bordered attendance-report-table">
                                    <colgroup>
                                        <col style="width:4%;">
                                        <col style="width:14%;">
                                        <col style="width:13%;">
                                        <col style="width:8%;">
                                        <col style="width:8%;">
                                        <col style="width:8%;">
                                        <col style="width:8%;">
                                        <col style="width:7%;">
                                        <col style="width:8%;">
                                        <col style="width:10%;">
                                        <col style="width:12%;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Employee Name</th>
                                            <th>Designation</th>
                                            <th>Rate/Day</th>
                                            <th>Rate/Hour</th>
                                            <th>Present<br>Days</th>
                                            <th>Absent<br>Days</th>
                                            <th>Late<br>(min)</th>
                                            <th>Undertime<br>(min)</th>
                                            <th>Holiday<br>Pay</th>
                                            <th>Grand Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $row)
                                            <tr>
                                                <td class="text-end">{{ $row['no'] }}</td>
                                                <td>{{ $row['employee_name'] }}</td>
                                                <td>{{ $row['designation'] }}</td>
                                                <td class="text-end">₱{{ number_format($row['rate_day'], 2) }}</td>
                                                <td class="text-end">₱{{ number_format($row['rate_hour'], 2) }}</td>
                                                <td class="text-center">{{ number_format($row['present_days'], 1) }}</td>
                                                <td class="text-center">{{ number_format($row['absent_days'], 1) }}</td>
                                                <td class="text-center">{{ number_format($row['late_minutes']) }}</td>
                                                <td class="text-center">{{ number_format($row['undertime_minutes']) }}</td>
                                                <td class="text-end">₱{{ number_format($row['holiday_pay'], 2) }}</td>
                                                <td class="text-end fw-bold">₱{{ number_format($row['grand_total'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="11" class="text-center text-secondary py-4">No attendance records found for the selected filters.</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end">TOTAL</td>
                                            <td class="text-center">{{ number_format($totals['present_days'], 1) }}</td>
                                            <td class="text-center">{{ number_format($totals['absent_days'], 1) }}</td>
                                            <td class="text-center">{{ number_format($totals['late_minutes']) }}</td>
                                            <td class="text-center">{{ number_format($totals['undertime_minutes']) }}</td>
                                            <td class="text-end">₱{{ number_format($totals['holiday_pay'], 2) }}</td>
                                            <td class="text-end">₱{{ number_format($totals['grand_total'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endif

                            <div class="signature-row d-flex justify-content-between mt-5">
                                <div class="signature-line">Prepared By</div>
                                <div class="signature-line">Approved By</div>
                            </div>

@if($exportMode)
</div>
</body>
</html>
@else
                        </div>
                    </div>
                </div>
            </div>

            <div class="attendance-report-controls-row attendance-report-left">
                <div class="card attendance-report-card attendance-report-bordered-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 attendance-report-section-title"><span class="attendance-svg">{!! $reportSvgIcons['summary'] !!}</span><span>Report Categories</span></h6>
                        <div class="d-flex flex-column gap-2 attendance-report-categories-grid">
                            @foreach($reportCategories as $key => $category)
                                <a href="{{ route('hr.attendance-reports.index', array_merge(request()->query(), ['report_type' => $key])) }}"
                                   class="attendance-report-category {{ $selectedReportType === $key ? 'active' : '' }}">
                                    <span class="attendance-report-category-icon"><span class="attendance-svg">{!! $reportSvgIcons[$category['icon']] ?? $reportSvgIcons['summary'] !!}</span></span>
                                    <span>
                                        <span class="attendance-report-category-title d-block">{{ $category['title'] }}</span>
                                        <span class="attendance-report-category-desc d-block">{{ $category['desc'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card attendance-report-card attendance-report-bordered-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1 attendance-report-section-title"><span class="attendance-svg">{!! $reportSvgIcons[$activeReport['icon']] ?? $reportSvgIcons['summary'] !!}</span><span>{{ $activeReport['title'] }}</span></h6>
                        <p class="text-secondary small mb-3">{{ $activeReport['desc'] }} for a selected period.</p>
                        <form id="attendanceReportFilterForm" method="GET" action="{{ route('hr.attendance-reports.index') }}">
                            <input type="hidden" name="report_type" value="{{ $selectedReportType }}">
                            <div class="attendance-report-filter-grid">
                                <div>
                                    <label class="form-label small fw-semibold">Branch</label>
                                    <select name="branch_id" class="form-select form-select-sm">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ (string)$selectedBranchId === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Month</label>
                                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Period Type</label>
                                    <select name="cutoff_period" class="form-select form-select-sm">
                                        <option value="first_half" {{ $selectedPeriod === 'first_half' ? 'selected' : '' }}>1st Half (1-15)</option>
                                        <option value="second_half" {{ $selectedPeriod === 'second_half' ? 'selected' : '' }}>2nd Half (16-31)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Department</label>
                                    <select name="department_id" class="form-select form-select-sm">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ (string)$selectedDepartmentId === (string)$department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Employee</label>
                                    <select name="employee_profile_id" class="form-select form-select-sm">
                                        <option value="">All Employees</option>
                                        @foreach($employeeOptions as $employee)
                                            <option value="{{ $employee->id }}" {{ (string)$selectedEmployeeId === (string)$employee->id ? 'selected' : '' }}>
                                                {{ trim(($employee->user->last_name ?? '') . ', ' . ($employee->user->first_name ?? '')) ?: 'Employee #' . $employee->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold">Group By</label>
                                    <select name="group_by" class="form-select form-select-sm">
                                        <option value="department" {{ $selectedGroupBy === 'department' ? 'selected' : '' }}>Department</option>
                                        <option value="branch" {{ $selectedGroupBy === 'branch' ? 'selected' : '' }}>Branch</option>
                                        <option value="employee" {{ $selectedGroupBy === 'employee' ? 'selected' : '' }}>Employee</option>
                                    </select>
                                </div>
                            </div>
                            <div class="attendance-report-actions mt-3">
                                <button type="submit" class="btn btn-primary btn-sm attendance-report-action"><span class="attendance-svg">{!! $reportSvgIcons['search'] !!}</span> Generate Report</button>
                                <a href="{{ route('hr.attendance-reports.export', array_merge($currentReportQuery, ['format' => 'excel'])) }}" class="btn btn-outline-success btn-sm attendance-report-action" data-attendance-export-format="excel"><span class="attendance-svg">{!! $reportSvgIcons['excel'] !!}</span> Export</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card attendance-report-card attendance-report-bordered-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 attendance-report-section-title"><span class="attendance-svg">{!! $reportSvgIcons['audit'] !!}</span><span>Last Generated Reports</span></h6>
                        <div class="d-flex flex-column gap-2">
                            @foreach($lastGeneratedReports as $generated)
                                <div class="attendance-last-generated d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="fw-bold small">{{ $generated['title'] }}</div>
                                        <div class="text-secondary" style="font-size:.72rem;">Generated by {{ auth()->user()->full_name ?? auth()->user()->name ?? 'HR Admin' }} • {{ $generated['time'] }}</div>
                                    </div>
                                    <span class="badge bg-soft-primary text-primary">{{ $generated['type'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@endif
