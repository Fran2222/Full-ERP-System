<x-app-layout>
    @php
        $dashboardType = $dashboardType ?? 'management';
    @endphp

    <style>
        .hr-dashboard-page {
            margin-top: -2rem;
            padding-bottom: 4.5rem;
        }
        .hr-card {
            border: 1px solid #e9edf5;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .045);
            background: #fff;
            overflow: hidden;
        }
        .hr-card .card-header {
            background: #fff;
            border-bottom: 1px solid #eef2f7;
            padding: 18px 20px;
        }
        .hr-muted { color: #64748b; }
        .hr-stat-card .card-body { padding: 18px; }
        .hr-stat-link {
            display: block;
            height: 100%;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: .15s ease;
        }
        .hr-stat-link:hover {
            color: inherit;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .hr-stat-link:hover .hr-stat-card {
            box-shadow: 0 14px 34px rgba(15, 23, 42, .10);
            border-color: #dbeafe;
        }
        .hr-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            background: #eef4ff;
            color: #2563eb;
        }
        .hr-stat-icon.green { background: #ecfdf3; color: #16a34a; }
        .hr-stat-icon.orange { background: #fff7ed; color: #f59e0b; }
        .hr-stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
        .hr-stat-icon.cyan { background: #ecfeff; color: #0891b2; }
        .hr-stat-icon.red { background: #fef2f2; color: #dc2626; }
        .hr-table { margin-bottom: 0; }
        .hr-table th {
            font-size: 11px;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #eef2f7;
            background: #fbfdff;
        }
        .hr-table td { vertical-align: middle; border-color: #eef2f7; }
        .hr-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e0ecff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex: 0 0 auto;
        }
        .hr-soft-box {
            border: 1px solid #edf2f7;
            border-radius: 16px;
            background: #fff;
            padding: 14px;
        }
        .hr-action-card {
            border: 1px solid #edf2f7;
            border-radius: 18px;
            width: 100%;
            min-height: 112px;
            padding: 14px 10px;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            transition: .15s ease;
            text-align: center;
        }

        .hr-action-card i {
            line-height: 1;
            flex: 0 0 auto;
        }

        .hr-action-card svg {
            width: 30px;
            height: 30px;
            display: block;
            flex: 0 0 auto;
        }

        .hr-action-card svg path,
        .hr-action-card svg circle,
        .hr-action-card svg rect {
            stroke: currentColor;
        }

        .hr-action-card strong {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.25;
            font-size: 14px;
            text-align: center;
        }
        .hr-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
            color: inherit;
        }
        .hr-action-card.blue { background: #eef4ff; color: #2563eb; }
        .hr-action-card.green { background: #f0fdf4; color: #16a34a; }
        .hr-action-card.orange { background: #fff7ed; color: #f59e0b; }
        .hr-action-card.red { background: #fef2f2; color: #dc2626; }
        .hr-action-card.sky { background: #e0f2fe; color: #0284c7; }
        .hr-action-card.teal { background: #ecfdf5; color: #0f766e; }
        .hr-action-card.purple { background: #f5f3ff; color: #7c3aed; }
        .hr-action-card.indigo { background: #eef2ff; color: #4f46e5; }


        /*
        |--------------------------------------------------------------------------
        | Quick Actions 3x2 Grid Fix
        |--------------------------------------------------------------------------
        | Keeps the Quick Actions card equal in height with the row and fills the
        | empty space using 3 rows x 2 columns at 125% display scaling.
        */
        .hr-quick-actions-card {
            display: flex;
            flex-direction: column;
        }

        .hr-quick-actions-card > .card-body {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            padding: 18px 20px 20px;
        }

        .hr-quick-actions-grid {
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .hr-quick-action-item {
            min-width: 0;
            min-height: 0;
            display: flex;
        }

        .hr-quick-action-item .hr-action-card {
            height: 100%;
            min-height: 0;
            padding: 10px 8px;
        }

        .hr-quick-action-item .hr-action-card svg {
            width: 27px;
            height: 27px;
        }

        .hr-quick-action-item .hr-action-card strong {
            min-height: 34px;
            font-size: 14px;
        }

        @media (max-width: 1199.98px) {
            .hr-quick-actions-grid {
                height: auto;
                grid-template-rows: none;
            }

            .hr-quick-action-item .hr-action-card {
                min-height: 108px;
            }
        }
        .hr-pie {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: conic-gradient(var(--pie-gradient));
            position: relative;
            margin: auto;
        }
        .hr-pie::after {
            content: '';
            position: absolute;
            inset: 44px;
            background: #fff;
            border-radius: 50%;
        }
        .hr-pie-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 2;
            pointer-events: none;
        }
        .hr-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .hr-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 7px;
        }
        .hr-calendar-day {
            height: 34px;
            border-radius: 10px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #475569;
        }
        .hr-calendar-day.active { background: #2563eb; color: #fff; font-weight: 700; }
        .hr-bulletin {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
        }
        .hr-bulletin .hr-muted { color: rgba(255,255,255,.75); }


        .hr-pending-leave-card .card-body {
            display: flex;
            flex-direction: column;
            min-height: 246px;
        }

        .hr-pending-leave-table-wrap {
            flex: 1;
            min-height: 178px;
        }

        .hr-pending-leave-table th,
        .hr-pending-leave-table td {
            padding-top: 0.72rem;
            padding-bottom: 0.72rem;
        }

        .hr-pending-leave-pagination {
            border-top: 1px solid #eef2f7;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 46px;
        }

        .hr-pending-leave-pagination .btn {
            min-width: 34px;
            height: 30px;
            padding: 0 10px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hr-pending-leave-pagination small {
            color: #64748b;
            font-weight: 600;
        }

        /*
        |--------------------------------------------------------------------------
        | HR Dashboard Second Row Focus Layout
        |--------------------------------------------------------------------------
        | Keep only Pending Leave Requests and On Leave Today on the second row so
        | both cards get enough width at 125% Windows display scaling.
        */
        .hr-leave-focus-row .hr-card {
            min-height: 372px;
        }

        .hr-on-leave-card .card-body {
            display: flex;
            flex-direction: column;
        }

        .hr-on-leave-list {
            flex: 1 1 auto;
            min-height: 0;
        }

        .hr-on-leave-item {
            border: 1px solid #eef2f7;
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
        }

        .hr-on-leave-item:last-child {
            margin-bottom: 0;
        }

        .hr-on-leave-item .badge {
            white-space: nowrap;
        }

        .hr-on-leave-total {
            margin-top: auto;
        }

        @media (max-width: 1199.98px) {
            .hr-leave-focus-row .hr-card {
                min-height: auto;
            }
        }

        .hr-timeline-list {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .hr-timeline-item {
            border: 1px solid #eef2f7;
            border-radius: 16px;
            padding: 14px;
            background: #fff;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: .15s ease;
        }
        .hr-timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, .07);
            border-color: #dbeafe;
        }
        .hr-timeline-icon {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef4ff;
            color: #2563eb;
            flex: 0 0 auto;
        }
        .hr-timeline-date {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .hr-timeline-badge {
            font-size: 11px;
            border-radius: 999px;
            padding: 5px 9px;
            background: #f1f5f9;
            color: #475569;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | Bottom Dashboard Cards Fixed Height + Pagination
        |--------------------------------------------------------------------------
        */
        .hr-bottom-row-card {
            height: 455px;
            min-height: 455px;
            max-height: 455px;
            display: flex;
            flex-direction: column;
        }

        .hr-bottom-row-card > .card-header {
            flex: 0 0 auto;
        }

        .hr-bottom-row-card > .card-body {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .hr-department-chart-wrap {
            flex: 0 0 auto;
        }

        .hr-bottom-list-wrap {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .hr-bottom-pagination {
            flex: 0 0 auto;
            border-top: 1px solid #eef2f7;
            padding-top: 10px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .hr-bottom-pagination .btn {
            min-width: 34px;
            height: 30px;
            padding: 0 10px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hr-bottom-pagination small {
            color: #64748b;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | HR Dashboard Row Alignment + Larger Calendar Fix
        |--------------------------------------------------------------------------
        | Keeps the 4th row cards level at 125% display scale and gives the
        | Add Event calendar more horizontal and vertical space without reducing text.
        */
        .hr-row-4-dashboard > [class*="col-"] {
            display: flex;
        }

        .hr-row-4-dashboard .hr-card {
            width: 100%;
        }

        .hr-row-4-card {
            height: 500px;
            min-height: 500px;
            max-height: 500px;
            display: flex;
            flex-direction: column;
        }

        .hr-row-4-card > .card-header {
            min-height: 98px;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hr-row-4-card > .card-body {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .hr-row-4-card .hr-bottom-list-wrap {
            flex: 1 1 auto;
            min-height: 0;
        }

        .hr-add-event-card > .card-header {
            justify-content: center;
        }

        .hr-add-event-card .card-body {
            padding: 20px 24px 18px;
            justify-content: space-between;
            overflow: visible;
        }

        .hr-add-event-calendar {
            width: 100%;
            max-width: 390px;
            margin: 0 auto;
        }

        .hr-add-event-calendar .hr-calendar-grid {
            gap: 9px;
        }

        .hr-add-event-calendar .hr-calendar-day {
            height: 41px;
            border-radius: 13px;
            font-size: 13px;
        }

        .hr-add-event-calendar .calendar-week-label {
            font-size: 13px;
            font-weight: 700;
        }

        .hr-add-event-calendar .calendar-nav-btn {
            min-width: 42px;
            height: 38px;
            border-radius: 11px;
            font-weight: 700;
        }

        .hr-add-event-calendar .calendar-month-title {
            font-size: 21px;
            font-weight: 700;
        }

        @media (max-width: 1399.98px) {
            .hr-row-4-card {
                height: 475px;
                min-height: 475px;
                max-height: 475px;
            }

            .hr-add-event-card .card-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .hr-add-event-calendar .hr-calendar-day {
                height: 38px;
            }
        }

        @media (max-width: 1199.98px) {
            .hr-row-4-card {
                height: auto;
                min-height: 420px;
                max-height: none;
            }
        }

        .hr-department-row,
        .birthday-row,
        .hr-schedule-row {
            transition: .15s ease;
        }



        /*
        |--------------------------------------------------------------------------
        | Exact Dashboard Row Gap Fix
        |--------------------------------------------------------------------------
        | Row 4 and Row 5 must use the same vertical gap as the upper dashboard rows.
        | Use horizontal gutters only (gx-3) on these rows, then control the vertical
        | distance with one fixed 16px bottom margin. This prevents Bootstrap's
        | vertical gutter (gy) from adding an extra hidden gap between rows.
        */
        .hr-dashboard-page {
            --hr-dashboard-row-gap: 16px;
        }

        .hr-row-4-dashboard,
        .hr-row-5-dashboard {
            --bs-gutter-y: 0 !important;
            margin-top: 0 !important;
            margin-bottom: var(--hr-dashboard-row-gap) !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .hr-row-4-dashboard > [class*="col-"],
        .hr-row-5-dashboard > [class*="col-"] {
            display: flex;
            margin-top: 0 !important;
        }

        .hr-row-4-dashboard .hr-card,
        .hr-row-5-dashboard .hr-card {
            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL Uniform Dashboard Row Spacing
        |--------------------------------------------------------------------------
        | Hope UI/Bootstrap can add hidden spacing through .card margin-bottom,
        | row gutters, and column margin-top. This block makes the dashboard rows
        | use ONE spacing source only: --hr-dashboard-row-gap.
        */
        .hr-dashboard-page {
            --hr-dashboard-row-gap: 18px;
        }

        .hr-dashboard-page .hr-row-3-dashboard,
        .hr-dashboard-page .hr-row-4-dashboard,
        .hr-dashboard-page .hr-row-5-dashboard {
            --bs-gutter-x: 1rem !important;
            --bs-gutter-y: 0 !important;
            margin-top: 0 !important;
            margin-bottom: var(--hr-dashboard-row-gap) !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .hr-dashboard-page .hr-row-3-dashboard > [class*="col-"],
        .hr-dashboard-page .hr-row-4-dashboard > [class*="col-"],
        .hr-dashboard-page .hr-row-5-dashboard > [class*="col-"] {
            display: flex !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .hr-dashboard-page .hr-row-3-dashboard .hr-card,
        .hr-dashboard-page .hr-row-4-dashboard .hr-card,
        .hr-dashboard-page .hr-row-5-dashboard .hr-card {
            width: 100% !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Match the exact gap below Evaluation Summary row to upper dashboard rows. */
        .hr-dashboard-page .hr-row-5-dashboard {
            margin-top: 0 !important;
        }

        .quick-link-card {
            border: 1px solid #e9edf5;
            border-radius: 16px;
            padding: 18px;
            height: 100%;
            display: block;
            color: inherit;
            background: #fff;
            transition: .15s ease;
        }
        .quick-link-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }
        @media (max-width: 1199px) {
            .hr-dashboard-page {
                margin-top: 0;
                padding-bottom: 3.5rem;
            }
        }
    
        .announcement-mini-item {
            border: 1px solid transparent;
            border-radius: 14px;
            padding: 8px;
            transition: .2s ease;
        }
        .announcement-mini-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            transform: translateY(-1px);
        }
        .announcement-memo-paper {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 28px;
            color: #111827;
        }
        .announcement-memo-meta div {
            display: grid;
            grid-template-columns: 70px 1fr;
            gap: 10px;
            margin-bottom: 4px;
        }
        .announcement-memo-content {
            line-height: 1.8;
            white-space: normal;
        }

        .hr-bottom-pagination .btn svg,
        .hr-pending-leave-pagination .btn svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .hr-bottom-pagination .btn svg path,
        .hr-pending-leave-pagination .btn svg path {
            stroke: currentColor;
        }

        .hr-bottom-pagination .btn,
        .hr-pending-leave-pagination .btn {
            width: 34px;
            min-width: 34px;
            height: 30px;
            padding: 0 !important;
            border-radius: 10px;
            color: #3b4df0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hr-bottom-pagination .btn:disabled,
        .hr-pending-leave-pagination .btn:disabled {
            color: #94a3b8;
            opacity: .65;
        }

        .hr-timeline-icon svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .hr-timeline-icon svg path,
        .hr-timeline-icon svg circle,
        .hr-timeline-icon svg rect {
            stroke: currentColor;
        }

        .hr-stat-icon svg {
            width: 24px;
            height: 24px;
            display: block;
            color: currentColor;
        }

        .hr-stat-icon svg path,
        .hr-stat-icon svg rect,
        .hr-stat-icon svg circle {
            stroke: currentColor;
        }

        .quick-link-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .quick-link-icon svg {
            width: 32px;
            height: 32px;
            display: block;
            color: currentColor;
        }

        .quick-link-icon svg path,
        .quick-link-icon svg rect,
        .quick-link-icon svg circle {
            stroke: currentColor;
        }



        /*
        |--------------------------------------------------------------------------
        | HR Dashboard wide-card display fix for 125% Windows scaling
        |--------------------------------------------------------------------------
        | Do not shrink text. Instead, give the summary cards more room by allowing
        | them to become wider and taller on laptop/desktop widths commonly affected
        | by 125% Windows display scaling.
        */
        .hr-summary-row {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 1.25rem;
        }

        .hr-summary-row .hr-card {
            min-height: 178px;
        }

        .hr-summary-row .hr-stat-card .card-body {
            min-height: 178px;
            padding: 24px 22px !important;
            align-items: center !important;
        }

        .hr-summary-row .hr-stat-card .card-body > div:first-child {
            min-width: 0;
            flex: 1 1 auto;
        }

        .hr-summary-row .hr-stat-card small {
            line-height: 1.35;
            display: block;
        }

        .hr-summary-row .hr-stat-card h2 {
            line-height: 1.05;
            margin-top: .45rem !important;
            margin-bottom: .45rem !important;
            white-space: nowrap;
        }

        .hr-summary-row .hr-stat-card .d-flex.flex-column {
            gap: .15rem !important;
        }

        .hr-summary-row .hr-stat-icon {
            flex: 0 0 52px;
            margin-left: 18px;
        }

        /*
         * At 125% scale, six cards in one row become too narrow. Make them
         * three cards per row so the text keeps its normal size and still fits.
         */
        @media (min-width: 1200px) and (max-width: 1699.98px) {
            .hr-summary-row > [class*="col-"] {
                flex: 0 0 auto;
                width: 33.33333333%;
            }

            .hr-summary-row .hr-card,
            .hr-summary-row .hr-stat-card .card-body {
                min-height: 165px;
            }
        }

        @media (min-width: 1700px) {
            .hr-summary-row > [class*="col-"] {
                flex: 0 0 auto;
                width: 16.66666667%;
            }
        }

        @media (max-width: 1199.98px) {
            .hr-summary-row > [class*="col-"] {
                width: 50%;
            }
        }

        /* Employee Dashboard - card spacing uniform fix */
        .employee-announcement-row {
            margin-bottom: 12px !important;
        }

        .employee-announcement-card {
            margin-bottom: 0 !important;
        }

        .employee-stat-row {
            --bs-gutter-y: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 12px !important;
        }

        .employee-dashboard-main-row {
            --bs-gutter-y: 0 !important;
            margin-top: 0 !important;
        }
        
    </style>

    <div class="container-fluid content-inner py-0 hr-dashboard-page">
        @if($dashboardType === 'employee')
            {{-- EMPLOYEE DASHBOARD: RECENT ANNOUNCEMENT --}}
    <div class="row g-0 employee-announcement-row">
        <div class="col-12">
            <div class="card hr-card employee-announcement-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">Recent Announcement</h4>
                                <p class="mb-0 hr-muted">Click the subject to view the full memorandum.</p>
                            </div>
                            <i class="ri-megaphone-line text-primary fs-4"></i>
                        </div>
                        <div class="card-body">
                            @forelse($recentAnnouncements as $announcement)
                                @php
                                    $announcementDate = $announcement->memo_date ?? $announcement->published_at ?? $announcement->created_at;
                                @endphp
                                <button type="button"
                                        class="w-100 border-0 bg-transparent text-start p-0 {{ ! $loop->last ? 'mb-3' : '' }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#employeeAnnouncementMemoModal{{ $announcement->id }}">
                                    <div class="hr-soft-box d-flex justify-content-between align-items-center announcement-mini-item">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="hr-dot mt-2" style="background:#2563eb"></span>
                                            <div>
                                                <strong class="d-block text-dark">{{ $announcement->title }}</strong>
                                                <p class="mb-0 small hr-muted">
                                                    {{ \Carbon\Carbon::parse($announcementDate)->format('M d, Y') }} • {{ $announcement->memo_from ?? optional($announcement->user)->full_name ?? 'Management' }}
                                                </p>
                                            </div>
                                        </div>
                                        <i class="ri-eye-line text-primary fs-5"></i>
                                    </div>
                                </button>
                            @empty
                                <p class="text-muted mb-0">No published announcements yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gx-3 gy-0 employee-stat-row">
                <div class="col-md-4">
                    <div class="card hr-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">{{ $myPendingLeaves }}</h2>
                                <p class="mb-0 hr-muted">Pending Leave</p>
                            </div>

                            <div class="hr-stat-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="5" width="16" height="15" rx="2"
                                        stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8 3V7M16 3V7M4 10H20"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round"/>
                                    <path d="M9 14H9.01M12 14H12.01M15 14H15.01"
                                        stroke="currentColor" stroke-width="2.4"
                                        stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card hr-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">{{ $myApprovedLeaves }}</h2>
                                <p class="mb-0 hr-muted">Approved Leave</p>
                            </div>

                            <div class="hr-stat-icon green">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="8.5"
                                            stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8.5 12.3L10.8 14.6L15.8 9.6"
                                        stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card hr-card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">{{ $latestEvaluationAverage ?? '-' }}</h2>
                                <p class="mb-0 hr-muted">Latest Rating</p>
                            </div>

                            <div class="hr-stat-icon purple">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19V5"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round"/>
                                    <path d="M4 19H20"
                                        stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round"/>
                                    <rect x="7" y="12" width="3" height="5" rx="1"
                                        stroke="currentColor" stroke-width="1.8"/>
                                    <rect x="12" y="9" width="3" height="8" rx="1"
                                        stroke="currentColor" stroke-width="1.8"/>
                                    <rect x="17" y="6" width="3" height="11" rx="1"
                                        stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gx-3 gy-0 employee-dashboard-main-row">
                <div class="col-xl-8">
                    <div class="card hr-card mb-3">
                        <div class="card-header"><h4 class="card-title mb-0">Quick Access</h4><p class="mb-0 hr-muted">Employee self-service shortcuts.</p></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="{{ route('hr.leave.index') }}" class="quick-link-card text-decoration-none">
                                        <span class="quick-link-icon text-primary">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <rect x="4" y="5" width="16" height="15" rx="2"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M8 3V7M16 3V7M4 10H20"
                                                    stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round"/>
                                                <path d="M8.5 15L10.5 17L15.5 12"
                                                    stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </span>

                                        <h6 class="mt-3 mb-1">My Leave</h6>
                                        <small class="hr-muted">File and track leave.</small>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('hr.evaluation.index') }}" class="quick-link-card text-decoration-none">
                                        <span class="quick-link-icon text-primary">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 19V5"
                                                    stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round"/>
                                                <path d="M4 19H20"
                                                    stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round"/>
                                                <rect x="7" y="12" width="3" height="5" rx="1"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                                <rect x="12" y="9" width="3" height="8" rx="1"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                                <rect x="17" y="6" width="3" height="11" rx="1"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                        </span>

                                        <h6 class="mt-3 mb-1">My Evaluation</h6>
                                        <small class="hr-muted">View ratings.</small>
                                    </a>
                                </div>

                                <div class="col-md-4">
                                    <a href="{{ route('hr.payroll.index') }}" class="quick-link-card text-decoration-none">
                                        <span class="quick-link-icon text-primary">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <rect x="4" y="5" width="16" height="14" rx="2"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M7 9H17"
                                                    stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round"/>
                                                <path d="M8 14H8.01M11 14H11.01M14 14H16"
                                                    stroke="currentColor" stroke-width="2.2"
                                                    stroke-linecap="round"/>
                                                <path d="M12 3V7"
                                                    stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round"/>
                                            </svg>
                                        </span>

                                        <h6 class="mt-3 mb-1">My Payslip</h6>
                                        <small class="hr-muted">View payroll.</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card hr-card">
                        <div class="card-header"><h4 class="card-title mb-0">My Recent Leave Requests</h4></div>
                        <div class="card-body">
                            @forelse($myRecentLeaves as $request)
                                <div class="hr-soft-box d-flex justify-content-between align-items-center mb-2">
                                    <div><strong>{{ optional($request->leaveType)->name ?? 'Leave' }}</strong><p class="mb-0 small hr-muted">{{ optional($request->start_datetime)->format('M d, Y') }} - {{ optional($request->end_datetime)->format('M d, Y') }}</p></div>
                                    <span class="badge @if($request->status === 'approved') bg-success @elseif($request->status === 'rejected') bg-danger @else bg-warning text-dark @endif">{{ $request->status === 'pending' ? 'Still Pending' : ucfirst($request->status) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No current or upcoming leave requests.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card hr-card mb-3"><div class="card-header"><h4 class="card-title mb-0">Latest Payslip</h4></div><div class="card-body">
                        @if($latestPayslip)
                            <div class="d-flex justify-content-between mb-2"><span class="hr-muted">Period</span><strong>{{ optional(optional($latestPayslip->payrollRun)->period_from)->format('M d') }} - {{ optional(optional($latestPayslip->payrollRun)->period_to)->format('M d, Y') }}</strong></div>
                            <div class="d-flex justify-content-between mb-3"><span class="hr-muted">Net Pay</span><strong>₱{{ number_format($latestPayslip->net_pay, 2) }}</strong></div>
                            <a href="{{ route('hr.payroll.show', $latestPayslip->payroll_run_id) }}" class="btn btn-primary w-100">View Payslip</a>
                        @else
                            <p class="text-muted mb-0">No payslip found yet.</p>
                        @endif
                    </div></div>
                    <div class="card hr-card"><div class="card-header"><h4 class="card-title mb-0">Latest Evaluation</h4></div><div class="card-body">
                        @if($latestEvaluation)
                            <div class="d-flex justify-content-between mb-2"><span class="hr-muted">Period</span><strong>{{ $latestEvaluation->period ?? '-' }}</strong></div>
                            <div class="d-flex justify-content-between mb-3"><span class="hr-muted">Average Rating</span><strong>{{ number_format($latestEvaluationAverage, 2) }} / 5</strong></div>
                            <a href="{{ route('hr.evaluation.show', $latestEvaluation->id) }}" class="btn btn-outline-primary w-100">View Result</a>
                        @else
                            <p class="text-muted mb-0">No evaluation result found yet.</p>
                        @endif
                    </div></div>
                </div>
            </div>

            {{-- Employee Announcement Memo Modals --}}
            @foreach($recentAnnouncements as $announcement)
                @php
                    $announcementDate = $announcement->memo_date ?? $announcement->published_at ?? $announcement->created_at;
                @endphp
                <div class="modal fade" id="employeeAnnouncementMemoModal{{ $announcement->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h4 class="modal-title mb-1">Memorandum</h4>
                                    <p class="mb-0 text-secondary small">Company announcement</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4 pb-4">
                                <div class="announcement-memo-paper">
                                    <div class="text-center mb-3">
                                        <h5 class="fw-bold text-primary mb-0">WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</h5>
                                        <small class="text-secondary">Official HR Memorandum</small>
                                    </div>

                                    <hr class="my-3">

                                    <h5 class="fw-bold text-uppercase mb-4">Memorandum</h5>

                                    <div class="announcement-memo-meta mb-4">
                                        <div><strong>To:</strong> <span>{{ $announcement->memo_to ?? 'All Employees' }}</span></div>
                                        <div><strong>From:</strong> <span>{{ $announcement->memo_from ?? 'Management' }}</span></div>
                                        <div><strong>Date:</strong> <span>{{ \Carbon\Carbon::parse($announcementDate)->format('F d, Y') }}</span></div>
                                    </div>

                                    <p class="mb-4"><strong>Subject:</strong> <u>{{ $announcement->title }}</u></p>

                                    <div class="announcement-memo-content">
                                        {!! nl2br(e($announcement->content)) !!}
                                    </div>

                                    <div class="mt-5">
                                        <p class="mb-5">Sincerely,</p>
                                        <strong>{{ $announcement->memo_from ?? 'Management' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            {{-- HR MANAGEMENT DASHBOARD --}}
            @php
                $totalDept = collect($departmentStats)->sum('count') ?: 1;
                $deptColors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];
                $pieStops = [];
                $start = 0;
                foreach ($departmentStats as $index => $dept) {
                    $percent = (($dept['count'] ?? 0) / $totalDept) * 100;
                    $end = $start + $percent;
                    $color = $deptColors[$index % count($deptColors)];
                    $pieStops[] = "{$color} {$start}% {$end}%";
                    $start = $end;
                }
                $pieGradient = count($pieStops) ? implode(', ', $pieStops) : '#e5e7eb 0% 100%';

                $evaluationTotal = max(1, $evaluationSummary['total'] ?? 0);
                $evalCompletedPercent = (($evaluationSummary['completed'] ?? 0) / $evaluationTotal) * 100;
                $evalPendingPercent = (($evaluationSummary['pending'] ?? 0) / $evaluationTotal) * 100;
                $evalOverduePercent = (($evaluationSummary['overdue'] ?? 0) / $evaluationTotal) * 100;
                $evalGradient = "#22c55e 0% {$evalCompletedPercent}%, #f59e0b {$evalCompletedPercent}% " . ($evalCompletedPercent + $evalPendingPercent) . "%, #ef4444 " . ($evalCompletedPercent + $evalPendingPercent) . "% 100%";
            @endphp

            <div style="--pie-gradient: {{ $pieGradient }}; --eval-gradient: {{ $evalGradient }};">
                {{-- 1ST ROW: 6 SUMMARY CARDS --}}
                <div class="row g-3 mb-3 hr-summary-row">
                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.employees.index') }}" class="hr-stat-link" title="Open Employee List">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Total Employees</small>
                                        <h2 class="mb-1 mt-1">{{ $employeeCount }}</h2>

                                        <div class="d-flex flex-column gap-1">
                                            <small class="fw-semibold" style="color: #60a5fa;">
                                                Total Male: {{ $maleEmployeeCount }}
                                            </small>
                                            <small class="fw-semibold" style="color: #f472b6;">
                                                Total Female: {{ $femaleEmployeeCount }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="hr-stat-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M17 21V19C17 16.7909 15.2091 15 13 15H7C4.79086 15 3 16.7909 3 19V21"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M10 11C12.2091 11 14 9.20914 14 7C14 4.79086 12.2091 3 10 3C7.79086 3 6 4.79086 6 7C6 9.20914 7.79086 11 10 11Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 21V19C21 17.1362 19.7252 15.5701 18 15.126"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 3.126C17.7252 3.57006 19 5.13616 19 7C19 8.86384 17.7252 10.4299 16 10.874"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>

                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.overtime-requests.index') }}" class="hr-stat-link" title="Open Overtime Requests">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Overtime Requests</small>
                                        <h2 class="mb-1 mt-1">{{ $pendingOvertimeRequestsCount ?? $overtimeSubmittedCount ?? 0 }}</h2>
                                        <small class="text-warning fw-semibold">Pending requests</small>
                                    </div>
                                    <div class="hr-stat-icon green">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="9"
                                                    stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 7V12L15.5 14"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.travel-orders.index') }}" class="hr-stat-link" title="Open Travel Orders">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Travel Orders</small>
                                        <h2 class="mb-1 mt-1">{{ $travelOrderSubmittedCount ?? 0 }}</h2>
                                        <small class="fw-semibold" style="color: #0ea5e9;">Submitted requests</small>
                                    </div>

                                    <div class="hr-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 18L3 21V6L9 3L15 6L21 3V18L15 21L9 18Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9 3V18"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M15 6V21"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.leave.requests') }}" class="hr-stat-link" title="Open Pending Leave Requests">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Pending Requests</small>
                                        <h2 class="mb-1 mt-1">{{ $pendingApprovals }}</h2>
                                        <small class="text-danger fw-semibold">Requires action</small>
                                    </div>
                                    <div class="hr-stat-icon purple">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="5" width="16" height="15" rx="2"
                                                stroke="currentColor" stroke-width="2"/>
                                            <path d="M8 3V7M16 3V7M4 10H20"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M8 14H12M8 17H15"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.evaluation.center.index') }}" class="hr-stat-link" title="Open Evaluation Center">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Evaluation Due</small>
                                        <h2 class="mb-1 mt-1">{{ $evaluationsDueCount }}</h2>
                                        <small class="text-info fw-semibold">This month</small>
                                    </div>
                                    <div class="hr-stat-icon cyan">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 4H7C5.89543 4 5 4.89543 5 6V20C5 21.1046 5.89543 22 7 22H17C18.1046 22 19 21.1046 19 20V6C19 4.89543 18.1046 4 17 4H15"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <rect x="9" y="2" width="6" height="4" rx="1"
                                                stroke="currentColor" stroke-width="2"/>
                                            <path d="M9 12H15M9 16H13"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xxl-2 col-lg-4 col-md-6">
                        <a href="{{ route('hr.payroll.index') }}" class="hr-stat-link" title="Open Payroll">
                            <div class="card hr-card hr-stat-card h-100">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="fw-semibold hr-muted">Next Payroll</small>
                                        <h2 class="mb-1 mt-1 fs-4">{{ $nextPayrollDate->format('M d') }}</h2>
                                        <small class="text-primary fw-semibold">{{ $daysUntilPayroll }} day{{ $daysUntilPayroll == 1 ? '' : 's' }} remaining</small>
                                    </div>
                                    <div class="hr-stat-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="9"
                                                    stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 7V17"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M15 9.5C14.3 8.7 13.2 8.3 12 8.3C10.3 8.3 9 9.1 9 10.4C9 13 15 11.4 15 14.2C15 15.5 13.7 16.3 12 16.3C10.8 16.3 9.7 15.9 9 15.1"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- 2ND ROW: PENDING LEAVE REQUESTS + ON LEAVE TODAY ONLY --}}
                <div class="row g-3 mb-3 hr-leave-focus-row">
                    <div class="col-xl-8 col-lg-12">
                        <div class="card hr-card h-100 hr-pending-leave-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Pending Leave Requests</h4>
                                @can('hr.leave.requests.view')
                                    <a href="{{ route('hr.leave.requests') }}" class="btn btn-link btn-sm text-decoration-none">View all</a>
                                @endcan
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive hr-pending-leave-table-wrap">
                                    <table class="table hr-table align-middle hr-pending-leave-table">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Leave Type</th>
                                                <th>Date Range</th>
                                                <th>Days</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pendingLeaveRequests as $request)
                                                @php
                                                    $employeeName = $request->employee->full_name ?? 'Employee';
                                                    $employeePosition = optional(optional($request->employee)->employeeProfile)->position->name ?? optional(optional($request->employee)->department)->name ?? 'Employee';
                                                @endphp
                                                <tr class="hr-pending-leave-row">
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="hr-avatar">{{ strtoupper(substr($employeeName, 0, 1)) }}</div>
                                                            <div>
                                                                <strong>{{ $employeeName }}</strong>
                                                                <p class="mb-0 small hr-muted">{{ $employeePosition }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-primary-subtle text-primary">{{ optional($request->leaveType)->name ?? 'Leave' }}</span></td>
                                                    <td><small>{{ optional($request->start_datetime)->format('M d') }} - {{ optional($request->end_datetime)->format('M d, Y') }}</small></td>
                                                    <td>{{ number_format((float) $request->days, 1) }}</td>
                                                    <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No pending leave requests.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($pendingLeaveRequests->count() > 3)
                                    <div class="hr-pending-leave-pagination" id="pendingLeavePagination">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="pendingLeavePrev" title="Previous page">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M15 18L9 12L15 6"
                                                    stroke="currentColor"
                                                    stroke-width="2.4"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <small id="pendingLeavePageInfo">Page 1 of 1</small>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="pendingLeaveNext" title="Next page">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 6L15 12L9 18"
                                                    stroke="currentColor"
                                                    stroke-width="2.4"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="hr-pending-leave-pagination border-0"></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-12">
                        <div class="card hr-card h-100 hr-on-leave-card">
                            <div class="card-header d-flex justify-content-between align-items-center"><h4 class="card-title mb-0">On Leave Today</h4>@can('hr.leave.requests.view')<a href="{{ route('hr.leave.requests') }}" class="btn btn-link btn-sm text-decoration-none">View all</a>@endcan</div>
                            <div class="card-body">
                                <div class="hr-on-leave-list">
                                    @forelse($onLeaveEmployees as $request)
                                        @php $employeeName = $request->employee->full_name ?? 'Employee'; @endphp
                                        <div class="hr-on-leave-item">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div class="d-flex align-items-start gap-2 flex-grow-1 min-w-0">
                                                    <div class="hr-avatar">{{ strtoupper(substr($employeeName, 0, 1)) }}</div>
                                                    <div class="min-w-0">
                                                        <strong class="d-block">{{ $employeeName }}</strong>
                                                        <p class="mb-0 small hr-muted">{{ optional($request->leaveType)->name ?? 'Leave' }}</p>
                                                    </div>
                                                </div>
                                                <span class="badge bg-success-subtle text-success mt-1">{{ number_format((float) $request->days, 0) }} day{{ (float) $request->days > 1 ? 's' : '' }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">No employees on leave today.</p>
                                    @endforelse
                                </div>
                                <div class="border-top pt-3 mt-3 hr-on-leave-total"><strong class="text-primary">Total on leave today: {{ $onLeaveCount }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- 3RD ROW: PAYROLL REMINDER + STRETCHED RECENT ANNOUNCEMENT + QUICK ACTIONS --}}
                <div class="row gx-3 gy-0 mb-3 align-items-stretch hr-row-3-dashboard">
                    <div class="col-xl-3 col-lg-6">
                        <div class="card hr-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center"><h4 class="card-title mb-0">Payroll Reminder</h4><i class="ri-calendar-check-line text-primary fs-4"></i></div>
                            <div class="card-body">
                                <div class="hr-soft-box mb-3">
                                    <div class="row g-3">
                                        <div class="col-6 border-end"><small class="hr-muted fw-semibold">Next Cutoff Date</small><h5 class="mb-1 mt-2">{{ $nextCutoffDate->format('M d, Y') }}</h5><small class="hr-muted">{{ $nextCutoffDate->diffForHumans() }}</small></div>
                                        <div class="col-6"><small class="hr-muted fw-semibold">Payroll Processing</small><h5 class="mb-2 mt-2">{{ $nextPayrollDate->format('M d, Y') }}</h5><span class="badge bg-success-subtle text-success">Upcoming</span></div>
                                    </div>
                                </div>
                                <div class="hr-soft-box d-flex justify-content-between align-items-center">
                                    <div><small class="hr-muted fw-semibold">Latest Payroll Run</small><h5 class="mb-0 mt-2">{{ $latestPayrollRun ? optional($latestPayrollRun->period_to)->format('M d, Y') : 'No payroll yet' }}</h5></div>
                                    @can('hr.payroll.view')<a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-primary btn-sm">View</a>@endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-12">
                        <div class="card hr-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Recent Announcement</h4>
                                <i class="ri-megaphone-line text-primary fs-4"></i>
                            </div>
                            <div class="card-body">
                                @forelse($recentAnnouncements as $announcement)
                                    @php
                                        $announcementDate = $announcement->memo_date ?? $announcement->published_at ?? $announcement->created_at;
                                    @endphp
                                    <button type="button"
                                            class="w-100 border-0 bg-transparent text-start p-0 mb-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#announcementMemoModal{{ $announcement->id }}">
                                        <div class="d-flex gap-2 announcement-mini-item">
                                            <span class="hr-dot mt-2" style="background:#2563eb"></span>
                                            <div class="flex-grow-1">
                                                <strong class="d-block text-dark">{{ $announcement->title }}</strong>
                                                <p class="mb-0 small hr-muted">
                                                    {{ \Carbon\Carbon::parse($announcementDate)->format('M d, Y') }} • {{ $announcement->memo_from ?? optional($announcement->user)->full_name ?? 'Management' }}
                                                </p>
                                            </div>
                                            <i class="ri-eye-line text-primary"></i>
                                        </div>
                                    </button>
                                @empty
                                    <p class="text-muted mb-0">No published announcements yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6">
                        <div class="card hr-card h-100 hr-quick-actions-card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Quick Actions</h4>
                            </div>

                            <div class="card-body">
                                <div class="hr-quick-actions-grid">
                                    @can('hr.employees.create')
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.employees.create') }}" class="hr-action-card blue">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M4 20C4.8 16.8 7.7 15 12 15"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M18 14V20M15 17H21"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                <strong>Add Employee</strong>
                                            </a>
                                        </div>
                                    @endcan

                                    @can('hr.employees.view')
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.employees.index') }}" class="hr-action-card green">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M8 11C9.933 11 11.5 9.433 11.5 7.5C11.5 5.567 9.933 4 8 4C6.067 4 4.5 5.567 4.5 7.5C4.5 9.433 6.067 11 8 11Z"
                                                        stroke="currentColor" stroke-width="2"/>
                                                    <path d="M16 11C17.933 11 19.5 9.433 19.5 7.5C19.5 5.567 17.933 4 16 4C14.067 4 12.5 5.567 12.5 7.5C12.5 9.433 14.067 11 16 11Z"
                                                        stroke="currentColor" stroke-width="2"/>
                                                    <path d="M3 20C3.6 16.8 5.5 15 8 15C10.5 15 12.4 16.8 13 20"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M11 20C11.6 16.8 13.5 15 16 15C18.5 15 20.4 16.8 21 20"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                <strong>Employee List</strong>
                                            </a>
                                        </div>
                                    @endcan

                                    @if(auth()->user()?->can('hr.leave.apply') || auth()->user()?->can('hr.leave.view') || auth()->user()?->can('hr.leave.own.view'))
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.leave.file') }}" class="hr-action-card purple">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M7 3H14L19 8V21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3Z"
                                                        stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                    <path d="M14 3V8H19"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M9 16L11.2 18L15.5 13"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <strong>File a Leave</strong>
                                            </a>
                                        </div>
                                    @endif

                                    @can('hr.leave.requests.view')
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.leave.requests') }}" class="hr-action-card orange">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <rect x="4" y="5" width="16" height="15" rx="2"
                                                        stroke="currentColor" stroke-width="2"/>
                                                    <path d="M8 3V7M16 3V7M4 10H20"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M8 15L10.3 17L16 13"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <strong>Leave Requests</strong>
                                            </a>
                                        </div>
                                    @endcan

                                    @can('hr.view')
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.travel-orders.create') }}" class="hr-action-card sky">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M9 18L3 21V6L9 3M9 18L15 21M9 18V3M15 21L21 18V3L15 6M15 21V6M15 6L9 3"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M17.5 8.5L18.5 9.5L17.5 10.5"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <strong>Travel Order</strong>
                                            </a>
                                        </div>
                                    @endcan

                                    @if(auth()->user()?->can('hr.view') || auth()->user()?->can('accounting.view'))
                                        <div class="hr-quick-action-item">
                                            <a href="{{ route('hr.overtime-requests.index') }}" class="hr-action-card red">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <circle cx="12" cy="13" r="7"
                                                            stroke="currentColor" stroke-width="2"/>
                                                    <path d="M12 9V13L15 15"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M9 3H15"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M19 5L21 7"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M17.5 3.5L20.5 6.5"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                <strong>Overtime Requests</strong>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4TH ROW: EVALUATION SUMMARY + ADD EVENT + STRETCHED UPCOMING BIRTHDAY --}}
                <div class="row gx-3 gy-0 mb-3 align-items-stretch hr-row-4-dashboard">
                    <div class="col-xl-3 col-lg-6">
                        <div class="card hr-card h-100 hr-row-4-card">
                            <div class="card-header"><h4 class="card-title mb-0">Evaluation Summary</h4></div>
                            <div class="card-body">
                                <div class="position-relative mb-3" style="--pie-gradient: var(--eval-gradient);">
                                    <div class="hr-pie"></div>
                                    <div class="hr-pie-center"><h4 class="mb-0">{{ $evaluationSummary['total'] ?? 0 }}</h4><small class="hr-muted">Total</small></div>
                                </div>
                                <div class="d-flex justify-content-between mb-2"><span><span class="hr-dot me-2" style="background:#22c55e"></span>Completed This Month</span><strong>{{ $evaluationSummary['completed'] ?? 0 }}</strong></div>
                                <div class="d-flex justify-content-between mb-2"><span><span class="hr-dot me-2" style="background:#f59e0b"></span>Pending</span><strong>{{ $evaluationSummary['pending'] ?? 0 }}</strong></div>
                                <div class="d-flex justify-content-between"><span><span class="hr-dot me-2" style="background:#ef4444"></span>Overdue</span><strong>{{ $evaluationSummary['overdue'] ?? 0 }}</strong></div>
                                @can('hr.evaluation.view')<a href="{{ route('hr.evaluation.index') }}" class="btn btn-link px-0 mt-3 text-decoration-none">View all evaluations <i class="ri-arrow-right-line"></i></a>@endcan
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-6">
                        <div class="card hr-card h-100 hr-row-4-card hr-add-event-card">
                            <div class="card-header"><button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#hrAddEventModal">Add Event +</button></div>
                            <div class="card-body">
                                <div class="hr-add-event-calendar">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <button class="btn btn-light btn-sm calendar-nav-btn" type="button">&lt;</button>
                                        <h5 class="mb-0 calendar-month-title">{{ now()->format('M Y') }}</h5>
                                        <button class="btn btn-light btn-sm calendar-nav-btn" type="button">&gt;</button>
                                    </div>
                                    <div class="hr-calendar-grid mb-3">
                                        @foreach(['S','M','T','W','T','F','S'] as $day)<small class="text-center hr-muted calendar-week-label">{{ $day }}</small>@endforeach
                                        @for($i = 1; $i <= 35; $i++)<div class="hr-calendar-day {{ $i == now()->day ? 'active' : '' }}">{{ $i <= now()->daysInMonth ? $i : '' }}</div>@endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="col-xl-5 col-lg-12">
                    <div class="card hr-card h-100 hr-row-4-card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="card-title mb-0">Upcoming Birthday</h4>
                                <small class="hr-muted">Filter birthdays by month.</small>
                            </div>

                            <select id="birthdayMonthFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="this-month" selected>This Month</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="all">All</option>
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}">
                                        {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="card-body">
                            <div class="hr-bottom-list-wrap" id="birthdayList">
                                @forelse($upcomingBirthdays as $user)
                                    @php
                                        $birthDate = $user->birthday_date ?? \Carbon\Carbon::parse($user->employeeProfile->birth_date);
                                        $birthMonth = $user->birthday_month ?? (int) $birthDate->format('n');
                                        $nextBirthday = $user->next_birthday ?? $birthDate->copy()->year(now()->year);
                                        if ($nextBirthday->isPast()) { $nextBirthday->addYear(); }
                                        $isThisMonth = (int) $birthMonth === (int) ($currentBirthdayMonth ?? now()->month);
                                        $employeeName = $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                                        $employeeName = $employeeName !== '' ? $employeeName : 'Employee';
                                    @endphp

                                    <div class="hr-soft-box birthday-row d-flex align-items-center justify-content-between mb-2"
                                        data-birthday-month="{{ $birthMonth }}"
                                        data-next-birthday="{{ $nextBirthday->format('Y-m-d') }}"
                                        data-this-month="{{ $isThisMonth ? 'yes' : 'no' }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="hr-avatar">{{ strtoupper(substr($user->first_name ?? 'E', 0, 1)) }}</div>
                                            <div>
                                                <strong>{{ $employeeName }}</strong>
                                                <p class="mb-0 small hr-muted">{{ optional($user->employeeProfile->position)->name ?? 'Employee' }}</p>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <small class="hr-muted">{{ $birthDate->format('M d') }}</small>
                                            @if($isThisMonth)
                                                <span class="badge bg-primary d-block mt-1">This Month</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No birthdays found.</p>
                                @endforelse

                                <div id="birthdayEmptyState" class="text-center text-muted py-4 d-none">
                                    No birthdays found.
                                </div>
                            </div>

                            @if($upcomingBirthdays->count() > 3)
                                <div class="hr-bottom-pagination">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="birthdayPrev">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 18L9 12L15 6"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <small id="birthdayPageInfo">Page 1 of 1</small>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="birthdayNext">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 6L15 12L9 18"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="hr-bottom-pagination border-0"></div>
                            @endif
                        </div>
                    </div>
                </div>
                </div>

                {{-- 5TH ROW: EMPLOYEE BY DEPARTMENT + STRETCHED UPCOMING HR SCHEDULE --}}
                <div class="row gx-3 gy-0 mb-3 align-items-stretch hr-row-5-dashboard">
                <div class="col-xl-4 col-lg-12">
                    <div class="card hr-card h-100 hr-bottom-row-card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Employee by Department</h4>
                        </div>

                        <div class="card-body">
                            <div class="position-relative mb-3 hr-department-chart-wrap">
                                <div class="hr-pie"></div>
                                <div class="hr-pie-center">
                                    <h4 class="mb-0">{{ $employeeCount }}</h4>
                                    <small class="hr-muted">Total</small>
                                </div>
                            </div>

                            <div class="hr-bottom-list-wrap" id="departmentStatsList">
                                @foreach($departmentStats as $index => $dept)
                                    <div class="d-flex justify-content-between align-items-center mb-3 hr-department-row">
                                        <span>
                                            <span class="hr-dot me-2" style="background: {{ $deptColors[$index % count($deptColors)] }}"></span>
                                            {{ $dept['name'] }}
                                        </span>
                                        <strong>{{ $dept['count'] }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            @if(collect($departmentStats)->count() > 6)
                                <div class="hr-bottom-pagination">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="departmentPrev">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 18L9 12L15 6"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <small id="departmentPageInfo">Page 1 of 1</small>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="departmentNext">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 6L15 12L9 18"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="hr-bottom-pagination border-0"></div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-12">
                    <div class="card hr-card h-100 hr-bottom-row-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0">Upcoming HR Schedule</h4>
                                <small class="hr-muted">HR activity timeline and reminders.</small>
                            </div>
                            <i class="ri-calendar-event-line text-primary fs-4"></i>
                        </div>

                        <div class="card-body">
                            <div class="hr-bottom-list-wrap">
                                <div class="hr-timeline-list" id="hrScheduleTimelineList">
                                    @forelse($calendarEvents as $event)
                                    @php
                                        $eventBadge = strtolower($event['badge'] ?? '');
                                    @endphp

                                    <div class="hr-timeline-item hr-schedule-row">
                                        <div class="hr-timeline-icon">
                                            @if(str_contains($eventBadge, 'payroll'))
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                    <circle cx="12" cy="12" r="9"
                                                            stroke="currentColor" stroke-width="2"/>
                                                    <path d="M12 7V17"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M15 9.5C14.3 8.7 13.2 8.3 12 8.3C10.3 8.3 9 9.1 9 10.4C9 13 15 11.4 15 14.2C15 15.5 13.7 16.3 12 16.3C10.8 16.3 9.7 15.9 9 15.1"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            @elseif(str_contains($eventBadge, 'hr'))
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"
                                                        stroke="currentColor" stroke-width="2"/>
                                                    <path d="M4 20C4.8 16.8 7.7 15 12 15C16.3 15 19.2 16.8 20 20"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            @else
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                    <rect x="4" y="5" width="16" height="15" rx="2"
                                                        stroke="currentColor" stroke-width="2"/>
                                                    <path d="M8 3V7M16 3V7M4 10H20"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                            @endif
                                        </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <h6 class="mb-0">{{ $event['title'] }}</h6>
                                                        <div class="hr-timeline-date">{{ $event['date'] }}</div>
                                                    </div>

                                                    <span class="hr-timeline-badge">{{ $event['badge'] ?? 'HR' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-4" id="hrScheduleEmptyState">
                                            No upcoming HR schedule yet.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @if(collect($calendarEvents)->count() > 3)
                                <div class="hr-bottom-pagination">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="schedulePrev">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 18L9 12L15 6"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <small id="schedulePageInfo">Page 1 of 1</small>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="scheduleNext">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 6L15 12L9 18"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="hr-bottom-pagination border-0"></div>
                            @endif
                        </div>
                    </div>
                </div>
                </div>
            {{-- Announcement Memo Modals --}}
            @foreach($recentAnnouncements as $announcement)
                @php
                    $announcementDate = $announcement->memo_date ?? $announcement->published_at ?? $announcement->created_at;
                @endphp
                <div class="modal fade" id="announcementMemoModal{{ $announcement->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h4 class="modal-title mb-1">Memorandum</h4>
                                    <p class="mb-0 text-secondary small">Company announcement</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4 pb-4">
                                <div class="announcement-memo-paper">
                                    <div class="text-center mb-3">
                                        <h5 class="fw-bold text-primary mb-0">WIZMASTER COMPUTER SALES AND SERVICES CORPORATION</h5>
                                        <small class="text-secondary">Official HR Memorandum</small>
                                    </div>

                                    <hr class="my-3">

                                    <h5 class="fw-bold text-uppercase mb-4">Memorandum</h5>

                                    <div class="announcement-memo-meta mb-4">
                                        <div><strong>To:</strong> <span>{{ $announcement->memo_to ?? 'All Employees' }}</span></div>
                                        <div><strong>From:</strong> <span>{{ $announcement->memo_from ?? 'Management' }}</span></div>
                                        <div><strong>Date:</strong> <span>{{ \Carbon\Carbon::parse($announcementDate)->format('F d, Y') }}</span></div>
                                    </div>

                                    <p class="mb-4"><strong>Subject:</strong> <u>{{ $announcement->title }}</u></p>

                                    <div class="announcement-memo-content">
                                        {!! nl2br(e($announcement->content)) !!}
                                    </div>

                                    <div class="mt-5">
                                        <p class="mb-5">Sincerely,</p>
                                        <strong>{{ $announcement->memo_from ?? 'Management' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="modal fade" id="hrAddEventModal" tabindex="-1" aria-labelledby="hrAddEventModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4">
                        <div class="modal-header"><h5 class="modal-title" id="hrAddEventModalLabel">Add Dashboard Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label">Event Title</label><input type="text" id="hrEventTitle" class="form-control" placeholder="Example: HR Meeting"></div>
                            <div class="mb-3"><label class="form-label">Event Date</label><input type="date" id="hrEventDate" class="form-control"></div>
                            <div class="mb-0"><label class="form-label">Badge</label><input type="text" id="hrEventBadge" class="form-control" placeholder="Example: HR"></div>
                            <small class="text-muted d-block mt-3">Note: This will add the event to the Upcoming HR Schedule card on the dashboard.</small>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="hrSaveEventBtn">Add Event</button></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        /*
        |--------------------------------------------------------------------------
        | Bottom Row Fixed Card Pagination
        |--------------------------------------------------------------------------
        */
        function setupSimplePagination(config) {
            const rows = Array.from(document.querySelectorAll(config.rowSelector));
            const prev = document.getElementById(config.prevId);
            const next = document.getElementById(config.nextId);
            const pageInfo = document.getElementById(config.pageInfoId);
            const perPage = config.perPage || 3;
            let page = 1;

            function getVisibleRows() {
                if (typeof config.filter === 'function') {
                    return rows.filter(config.filter);
                }

                return rows;
            }

            function render() {
                const visibleRows = getVisibleRows();
                const totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));

                page = Math.min(Math.max(page, 1), totalPages);

                rows.forEach(function (row) {
                    row.classList.add('d-none');
                });

                visibleRows.forEach(function (row, index) {
                    const start = (page - 1) * perPage;
                    const end = start + perPage;

                    row.classList.toggle('d-none', index < start || index >= end);
                });

                if (pageInfo) {
                    pageInfo.textContent = 'Page ' + page + ' of ' + totalPages;
                }

                if (prev) {
                    prev.disabled = page <= 1;
                }

                if (next) {
                    next.disabled = page >= totalPages;
                }

                if (typeof config.afterRender === 'function') {
                    config.afterRender(visibleRows.length);
                }
            }

            if (prev) {
                prev.addEventListener('click', function () {
                    page--;
                    render();
                });
            }

            if (next) {
                next.addEventListener('click', function () {
                    page++;
                    render();
                });
            }

            return {
                reset: function () {
                    page = 1;
                    render();
                },
                render: render
            };
        }

        setupSimplePagination({
            rowSelector: '.hr-department-row',
            prevId: 'departmentPrev',
            nextId: 'departmentNext',
            pageInfoId: 'departmentPageInfo',
            perPage: 6
        });

        const birthdayFilter = document.getElementById('birthdayMonthFilter');
        const birthdayEmptyState = document.getElementById('birthdayEmptyState');

        const birthdayPager = setupSimplePagination({
            rowSelector: '.birthday-row',
            prevId: 'birthdayPrev',
            nextId: 'birthdayNext',
            pageInfoId: 'birthdayPageInfo',
            perPage: 3,
            filter: function (row) {
                if (!birthdayFilter) {
                    return true;
                }

                const selected = birthdayFilter.value;
                const rowMonth = row.getAttribute('data-birthday-month');
                const isThisMonth = row.getAttribute('data-this-month') === 'yes';

                if (selected === 'this-month') {
                    return isThisMonth;
                }

                if (selected === 'upcoming' || selected === 'all') {
                    return true;
                }

                return rowMonth === selected;
            },
            afterRender: function (visibleCount) {
                if (birthdayEmptyState) {
                    birthdayEmptyState.classList.toggle('d-none', visibleCount !== 0);
                }
            }
        });

        if (birthdayFilter) {
            birthdayFilter.value = 'this-month';
            birthdayFilter.addEventListener('change', function () {
                birthdayPager.reset();
            });
            birthdayPager.reset();
        }

        const schedulePager = setupSimplePagination({
            rowSelector: '.hr-schedule-row',
            prevId: 'schedulePrev',
            nextId: 'scheduleNext',
            pageInfoId: 'schedulePageInfo',
            perPage: 3
        });
            const pendingLeavePrev = document.getElementById('pendingLeavePrev');
            const pendingLeaveNext = document.getElementById('pendingLeaveNext');
            const pendingLeavePageInfo = document.getElementById('pendingLeavePageInfo');
            const pendingLeavePerPage = 3;
            let pendingLeavePage = 1;

            function renderPendingLeavePage() {
                if (!pendingLeaveRows.length) {
                    return;
                }

                const totalPages = Math.max(1, Math.ceil(pendingLeaveRows.length / pendingLeavePerPage));
                pendingLeavePage = Math.min(Math.max(pendingLeavePage, 1), totalPages);

                pendingLeaveRows.forEach(function (row, index) {
                    const start = (pendingLeavePage - 1) * pendingLeavePerPage;
                    const end = start + pendingLeavePerPage;
                    row.classList.toggle('d-none', index < start || index >= end);
                });

                if (pendingLeavePageInfo) {
                    pendingLeavePageInfo.textContent = 'Page ' + pendingLeavePage + ' of ' + totalPages;
                }

                if (pendingLeavePrev) {
                    pendingLeavePrev.disabled = pendingLeavePage <= 1;
                }

                if (pendingLeaveNext) {
                    pendingLeaveNext.disabled = pendingLeavePage >= totalPages;
                }
            }

            if (pendingLeavePrev) {
                pendingLeavePrev.addEventListener('click', function () {
                    pendingLeavePage--;
                    renderPendingLeavePage();
                });
            }

            if (pendingLeaveNext) {
                pendingLeaveNext.addEventListener('click', function () {
                    pendingLeavePage++;
                    renderPendingLeavePage();
                });
            }

            renderPendingLeavePage();

            const saveEventBtn = document.getElementById('hrSaveEventBtn');
            const scheduleTimelineList = document.getElementById('hrScheduleTimelineList');
            const scheduleEmptyState = document.getElementById('hrScheduleEmptyState');

            if (saveEventBtn && scheduleTimelineList) {
                saveEventBtn.addEventListener('click', function () {
                    const titleInput = document.getElementById('hrEventTitle');
                    const dateInput = document.getElementById('hrEventDate');
                    const badgeInput = document.getElementById('hrEventBadge');

                    const title = titleInput.value.trim();
                    const date = dateInput.value;
                    const badge = badgeInput.value.trim() || 'HR';

                    if (!title || !date) {
                        alert('Please enter event title and date.');
                        return;
                    }

                    const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
                        month: 'short', day: '2-digit', year: 'numeric'
                    });

                    const badgeLower = badge.toLowerCase();

                    let iconSvg = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="5" width="16" height="15" rx="2"
                                stroke="currentColor" stroke-width="2"/>
                            <path d="M8 3V7M16 3V7M4 10H20"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    `;

                    if (badgeLower.includes('payroll')) {
                        iconSvg = `
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9"
                                        stroke="currentColor" stroke-width="2"/>
                                <path d="M12 7V17"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M15 9.5C14.3 8.7 13.2 8.3 12 8.3C10.3 8.3 9 9.1 9 10.4C9 13 15 11.4 15 14.2C15 15.5 13.7 16.3 12 16.3C10.8 16.3 9.7 15.9 9 15.1"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        `;
                    } else if (badgeLower.includes('hr')) {
                        iconSvg = `
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z"
                                    stroke="currentColor" stroke-width="2"/>
                                <path d="M4 20C4.8 16.8 7.7 15 12 15C16.3 15 19.2 16.8 20 20"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        `;
                    }

                    if (scheduleEmptyState) {
                        scheduleEmptyState.remove();
                    }

                    const item = document.createElement('div');
                    item.className = 'hr-timeline-item hr-schedule-row';
                    item.innerHTML = `
                        <div class="hr-timeline-icon">
                            ${iconSvg}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <h6 class="mb-0"></h6>
                                    <div class="hr-timeline-date"></div>
                                </div>
                                <span class="hr-timeline-badge"></span>
                            </div>
                        </div>
                    `;

                    item.querySelector('h6').textContent = title;
                    item.querySelector('.hr-timeline-date').textContent = formattedDate;
                    item.querySelector('.hr-timeline-badge').textContent = badge;

                    scheduleTimelineList.prepend(item);
                    schedulePager.reset();

                    titleInput.value = '';
                    dateInput.value = '';
                    badgeInput.value = '';

                    const modalElement = document.getElementById('hrAddEventModal');
                    if (window.bootstrap && modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modal.hide();
                    }
                });
            }
        });
    </script>
</x-app-layout>
