<x-app-layout :assets="$assets ?? []">
    <style>
        body .iq-navbar-header {
            display: none !important;
        }

        body .content-inner.wizmaster-main-dashboard {
            padding-top: 48px !important;
            margin-top: 0 !important;
        }

        .wizmaster-main-dashboard {
            color: #0f172a;
        }

        .wiz-dashboard-shell {
            max-width: 1540px;
            margin: 0 auto;
        }

        .wiz-dashboard-top-row {
            margin-top: 0;
        }

        .wiz-card,
        .wiz-dashboard-hero,
        .wiz-announcement-spotlight,
        .wiz-stat-card,
        .wiz-panel-card,
        .wiz-module-mini,
        .wiz-quick-action {
            border: 1px solid rgba(226, 232, 240, .88);
            border-radius: 18px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 14px 38px rgba(15, 23, 42, .06);
        }

        .wiz-dashboard-hero {
            min-height: 210px;
            height: 100%;
            color: #ffffff;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 93% 8%, rgba(125, 211, 252, .34), transparent 28%),
                radial-gradient(circle at 74% 115%, rgba(56, 189, 248, .22), transparent 34%),
                linear-gradient(135deg, #315cf6 0%, #1640c4 52%, #082b88 100%);
        }

        .wiz-dashboard-hero::before,
        .wiz-dashboard-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .13);
            pointer-events: none;
        }

        .wiz-dashboard-hero::before {
            width: 360px;
            height: 360px;
            right: -88px;
            top: -160px;
        }

        .wiz-dashboard-hero::after {
            width: 230px;
            height: 230px;
            right: 145px;
            bottom: -160px;
        }

        .wiz-hero-content {
            position: relative;
            z-index: 2;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .wiz-eyebrow {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .13);
            color: rgba(255, 255, 255, .92);
            font-size: 10px;
            letter-spacing: 1.7px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .wiz-dashboard-hero h2 {
            color: #ffffff;
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.05;
            margin: 16px 0 8px;
            font-weight: 900;
            letter-spacing: -.7px;
        }

        .wiz-dashboard-hero p {
            color: rgba(255, 255, 255, .92);
            max-width: 690px;
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 600;
        }

        .wiz-hero-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .wiz-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .21);
            color: #ffffff;
            font-weight: 850;
            font-size: 12px;
        }

        .wiz-pill-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, .18);
        }

        .wiz-announcement-spotlight {
            min-height: 210px;
            height: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .wiz-announcement-art {
            position: absolute;
            right: 24px;
            top: 96px;
            width: 88px;
            height: 88px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 54px;
            opacity: .88;
            transform: rotate(-10deg);
            pointer-events: none;
            z-index: 1;
        }

        .wiz-section-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 950;
            margin: 0;
            letter-spacing: -.25px;
        }

        .wiz-section-subtitle,
        .wiz-muted,
        .wiz-stat-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 750;
        }

        .wiz-view-link {
            color: #315cf6;
            background: #eef2ff;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        .wiz-view-link:hover {
            color: #1741c8;
        }

        .wiz-announcement-list {
            flex: 1;
            margin-top: 12px;
            max-height: 108px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-left: 10px;
            padding-right: 105px;
            position: relative;
            z-index: 2;
        }

        .wiz-announcement-list::-webkit-scrollbar {
            width: 5px;
        }

        .wiz-announcement-list::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(49, 92, 246, .25);
        }

        .wiz-announcement-button {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 0;
            text-align: left;
            cursor: pointer;
        }

        .wiz-announcement-button:hover .wiz-announcement-title {
            color: #315cf6 !important;
        }

        .wiz-announcement-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 9px 0;
        }

        .wiz-dot {
            width: 10px;
            height: 10px;
            min-width: 10px;
            border-radius: 999px;
            background: #315cf6;
            box-shadow: 0 0 0 6px rgba(49, 92, 246, .10);
            margin-top: 7px;
        }

        .wiz-announcement-title {
            transition: color .18s ease;
            font-size: 14px;
        }

        .wiz-announcement-preview {
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
            margin-top: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 100%;
        }

        .wiz-announcement-footer {
            position: relative;
            z-index: 2;
            margin-top: auto;
            padding-top: 12px;
            color: #64748b;
            font-size: 12px;
            font-weight: 850;
        }

        .wiz-stat-card {
            height: 100%;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .wiz-stat-card:hover,
        .wiz-panel-card:hover,
        .wiz-module-mini:hover,
        .wiz-quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 44px rgba(15, 23, 42, .09);
        }

        .wiz-stat-card .card-body {
            padding: 18px;
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 16px;
            align-items: center;
        }

        .wiz-stat-icon,
        .wiz-module-icon,
        .wiz-action-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 950;
            color: #315cf6;
            background: rgba(49, 92, 246, .09);
            font-size: 19px;
        }

        .wiz-module-icon {
            font-size: 0;
        }

        .wiz-module-icon svg {
            width: 23px;
            height: 23px;
            stroke-width: 2;
        }

        .wiz-module-icon.icon-warehouse {
            color: #059669;
            background: rgba(5, 150, 105, .10);
        }

        .wiz-module-icon.icon-sales {
            color: #2563eb;
            background: rgba(37, 99, 235, .10);
        }

        .wiz-module-icon.icon-purchasing {
            color: #ea580c;
            background: rgba(234, 88, 12, .11);
        }

        .wiz-module-icon.icon-human-resource {
            color: #7c3aed;
            background: rgba(124, 58, 237, .10);
        }

        .tone-primary { color: #315cf6; background: rgba(49, 92, 246, .10); }
        .tone-success { color: #079455; background: rgba(7, 148, 85, .10); }
        .tone-warning { color: #e56a00; background: rgba(229, 106, 0, .12); }
        .tone-danger { color: #d92d5c; background: rgba(217, 45, 92, .10); }
        .tone-info { color: #0284c7; background: rgba(2, 132, 199, .10); }
        .tone-purple { color: #7c3aed; background: rgba(124, 58, 237, .11); }
        .tone-muted { color: #475569; background: rgba(71, 85, 105, .10); }

        .wiz-stat-value {
            color: #0f172a;
            font-size: 25px;
            line-height: 1.05;
            font-weight: 950;
            margin: 6px 0 4px;
            letter-spacing: -.4px;
        }

        .wiz-mini-progress {
            height: 6px;
            border-radius: 999px;
            background: #eef2ff;
            overflow: hidden;
            margin-top: 13px;
        }

        .wiz-mini-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #315cf6, #06b6d4);
        }

        .wiz-panel-card .card-body {
            padding: 20px;
        }

        .wiz-panel-header,
        .wiz-module-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid #eef2f7;
            padding-bottom: 14px;
            margin-bottom: 12px;
        }

        .wiz-chart-card {
            min-height: 100%;
        }

        .wiz-bar-chart {
            display: flex;
            align-items: end;
            gap: 24px;
            height: 165px;
            padding: 14px 8px 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        .wiz-bar-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            min-width: 0;
            position: relative;
        }

        .wiz-bar-value {
            color: #0f172a;
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 6px;
        }

        .wiz-bar {
            width: 100%;
            max-width: 28px;
            min-height: 12px;
            border-radius: 12px 12px 5px 5px;
            background: linear-gradient(180deg, #315cf6, #06b6d4);
            box-shadow: 0 12px 20px rgba(49, 92, 246, .18);
        }

        .wiz-bar-label {
            color: #475569;
            font-size: 11px;
            font-weight: 850;
            margin-top: 10px;
            text-align: center;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .wiz-bar-chart-wide {
            height: 185px;
            gap: 34px;
            padding-left: 18px;
            padding-right: 18px;
        }

        .wiz-donut-wrap-balanced {
            min-height: 185px;
        }

        .wiz-donut-wrap {
            display: grid;
            grid-template-columns: 154px 1fr;
            gap: 22px;
            align-items: center;
            padding-top: 8px;
        }

        .wiz-donut {
            width: 154px;
            height: 154px;
            border-radius: 50%;
            background: conic-gradient(var(--seg1, #315cf6) 0 var(--p1, 25%), var(--seg2, #7c3aed) var(--p1, 25%) var(--p2, 48%), var(--seg3, #10b981) var(--p2, 48%) var(--p3, 62%), var(--seg4, #f59e0b) var(--p3, 62%) var(--p4, 72%), #e2e8f0 var(--p4, 72%) 100%);
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .04), 0 18px 35px rgba(15, 23, 42, .08);
        }

        .wiz-donut::after {
            content: '';
            position: absolute;
            inset: 34px;
            border-radius: inherit;
            background: #ffffff;
        }

        .wiz-donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 2;
            font-weight: 950;
            color: #0f172a;
        }

        .wiz-donut-center div {
            font-size: 24px;
            line-height: 1;
        }

        .wiz-legend-item,
        .wiz-link-row,
        .wiz-list-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .wiz-legend-item:last-child,
        .wiz-link-row:last-child,
        .wiz-list-row:last-child {
            border-bottom: 0;
        }

        .wiz-legend-left {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #475569;
            font-weight: 800;
            font-size: 12px;
        }

        .wiz-legend-color {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .wiz-module-mini {
            height: 100%;
            padding: 16px;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .wiz-module-mini .wiz-section-title {
            font-size: 15px;
        }

        .wiz-module-mini .wiz-section-subtitle {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wiz-module-mini .wiz-module-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 13px;
            font-size: 12px;
        }

        .wiz-link-row a,
        .wiz-link-row span,
        .wiz-list-row strong,
        .wiz-quick-action strong {
            color: #315cf6;
            font-weight: 900;
            text-decoration: none;
        }

        .wiz-link-row strong,
        .wiz-legend-item strong {
            color: #0f172a;
            font-weight: 950;
            font-size: 12px;
        }

        .wiz-quick-panel .card-body {
            padding: 16px 20px;
        }

        .wiz-quick-actions {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .wiz-quick-action {
            min-height: 66px;
            padding: 12px;
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            align-items: center;
            color: #315cf6;
            text-decoration: none;
            transition: all .18s ease;
        }

        .wiz-action-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            font-size: 18px;
            border-radius: 13px;
        }

        .wiz-quick-action span {
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
            display: block;
            margin-top: 2px;
        }

        .wiz-list-row {
            color: #475569;
        }

        .wiz-list-row .wiz-list-main {
            min-width: 0;
        }

        .wiz-list-row .wiz-list-amount {
            white-space: nowrap;
            text-align: right;
        }

        .wiz-empty {
            padding: 24px 0;
            color: #94a3b8;
            font-weight: 750;
            text-align: center;
        }

        .wiz-empty-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 10px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #64748b;
            font-size: 22px;
        }

        .wiz-announcement-modal .modal-content {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
        }

        .wiz-announcement-modal .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 22px 24px;
        }

        .wiz-announcement-modal .modal-body {
            padding: 24px;
        }

        .wiz-announcement-modal-title {
            color: #0f172a;
            font-size: 22px;
            font-weight: 950;
            margin: 0;
        }

        .wiz-announcement-modal-date {
            color: #64748b;
            font-size: 13px;
            font-weight: 750;
            margin-top: 4px;
        }

        .wiz-announcement-modal-content {
            color: #334155;
            font-size: 15px;
            line-height: 1.7;
            white-space: pre-line;
        }

        @media (max-width: 1399.98px) {
            .wiz-bar-chart {
                gap: 16px;
            }

            .wiz-quick-actions {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1199.98px) {
            .wiz-donut-wrap {
                grid-template-columns: 1fr;
            }

            .wiz-quick-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            body .content-inner.wizmaster-main-dashboard {
                padding-top: 28px !important;
            }

            .wiz-dashboard-top-row {}

            .wiz-dashboard-hero,
            .wiz-announcement-spotlight {
                min-height: auto;
                padding: 20px;
            }

            .wiz-hero-content {
                min-height: auto;
            }

            .wiz-stat-card .card-body,
            .wiz-quick-action {
                grid-template-columns: 1fr;
            }

            .wiz-quick-actions {
                grid-template-columns: 1fr;
            }

            .wiz-announcement-art {
                display: none;
            }

            .wiz-announcement-list {
                padding-right: 4px;
            }
        }
    </style>

    @php
        $currentUser = $user ?? auth()->user();

        $canAccess = function (?string $permission = null) use ($currentUser) {
            return $currentUser && (
                !$permission
                || $currentUser->can($permission)
                || $currentUser->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])
            );
        };

        $displayValue = function (array $row, array $keys, ?string $default = '-') {
            foreach ($keys as $key) {
                if (array_key_exists($key, $row) && filled($row[$key])) {
                    return $row[$key];
                }
            }

            return $default;
        };

        $displayMoney = function (array $row, array $keys) use ($displayValue) {
            $value = $displayValue($row, $keys, null);
            return $value === null ? '-' : '₱ ' . number_format((float) $value, 2);
        };

        $displayDate = function ($value) {
            if (!$value) {
                return '-';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y');
            } catch (\Throwable $e) {
                return '-';
            }
        };

        $numberOnly = function ($value) {
            if (is_numeric($value)) {
                return (float) $value;
            }

            return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
        };

        $shortLabel = function ($label) {
            $label = (string) $label;

            return match ($label) {
                'Total Users' => 'TU',
                'Active Employees' => 'AE',
                'Warehouse Items' => 'WI',
                'Pending Purchase Orders' => 'PO',
                'Sales This Month' => 'SALES',
                'Receivables' => 'AR',
                'Travel Orders' => 'TRAVEL',
                'Overtime Requests' => 'OT',
                default => collect(explode(' ', $label))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('') ?: 'M',
            };
        };

        $statIcon = function ($label, $fallback = '•') {
            return match ((string) $label) {
                'Total Users' => '👥',
                'Active Employees' => '👤',
                'Warehouse Items' => '▣',
                'Pending Purchase Orders' => '🛒',
                'Sales This Month' => '₱',
                'Receivables' => '▤',
                'Travel Orders' => '✈',
                'Overtime Requests' => '◔',
                default => $fallback ?: '•',
            };
        };

        $actionIcon = function ($label) {
            return match ((string) $label) {
                'Add Employee' => '👤',
                'Create Sales Receipt' => '▤',
                'Create Purchase Order' => '🛒',
                'Stock In' => '⬇',
                'Add Customer' => '👥',
                default => '➕',
            };
        };

        $announcementCount = collect($announcements ?? [])->count();

        $chartRows = collect($metrics ?? [])->take(6)->map(function ($metric) use ($numberOnly, $shortLabel) {
            $label = (string) ($metric['label'] ?? 'Metric');

            return [
                'label' => $shortLabel($label),
                'full' => $label,
                'value' => max(0, $numberOnly($metric['value'] ?? 0)),
            ];
        })->values();

        $maxChartValue = max(1, $chartRows->max('value') ?: 1);
        $totalPulse = max(1, $chartRows->sum('value'));
        $donutRows = $chartRows->take(4)->values();
        $donutOthers = max(0, $totalPulse - $donutRows->sum('value'));
        $donutColors = ['#315cf6', '#7c3aed', '#10b981', '#f59e0b'];

        $p1 = min(100, ($donutRows->get(0)['value'] ?? 0) / $totalPulse * 100);
        $p2 = min(100, $p1 + (($donutRows->get(1)['value'] ?? 0) / $totalPulse * 100));
        $p3 = min(100, $p2 + (($donutRows->get(2)['value'] ?? 0) / $totalPulse * 100));
        $p4 = min(100, $p3 + (($donutRows->get(3)['value'] ?? 0) / $totalPulse * 100));

        $viewAllRoutes = [
            'announcements' => \Illuminate\Support\Facades\Route::has('announcements.index') ? route('announcements.index') : (\Illuminate\Support\Facades\Route::has('hr.announcements.index') ? route('hr.announcements.index') : null),
            'invoices' => \Illuminate\Support\Facades\Route::has('sales.invoices.index') ? route('sales.invoices.index') : null,
            'purchase_orders' => \Illuminate\Support\Facades\Route::has('purchasing.purchase-orders.index') ? route('purchasing.purchase-orders.index') : null,
            'warehouse_activity' => \Illuminate\Support\Facades\Route::has('warehouse.ledger.index') ? route('warehouse.ledger.index') : (\Illuminate\Support\Facades\Route::has('warehouse.inventory-ledger.index') ? route('warehouse.inventory-ledger.index') : null),
        ];
    @endphp

    <div class="container-fluid content-inner wizmaster-main-dashboard py-0">
        <div class="wiz-dashboard-shell">
            <div class="row g-4 mb-4 align-items-stretch wiz-dashboard-top-row">
                <div class="col-xl-8 col-lg-7">
                    <div class="wiz-dashboard-hero">
                        <div class="wiz-hero-content">
                            <div>
                                <h2>System Overview</h2>
                                <p>Track daily activity across Sales, Warehouse, Purchasing, Human Resource, and User Access in one clean dashboard.</p>
                            </div>

                            <div class="wiz-hero-footer">
                                <div class="wiz-hero-pill">▣ {{ now()->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="wiz-announcement-spotlight">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-1">
                            <div>
                                <h5 class="wiz-section-title">Announcements</h5>
                                <div class="wiz-section-subtitle mt-1">Latest company updates</div>
                            </div>

                            @if($viewAllRoutes['announcements'])
                                <a href="{{ $viewAllRoutes['announcements'] }}" class="wiz-view-link">View all</a>
                            @endif
                        </div>

                        <div class="wiz-announcement-art">📣</div>

                        <div class="wiz-announcement-list">
                            @forelse(($announcements ?? []) as $index => $announcement)
                                @php
                                    $announcementTitle = $displayValue($announcement, ['title'], 'Announcement');
                                    $announcementDate = $displayDate($displayValue($announcement, ['published_at', 'created_at'], null));
                                    $announcementBody = $displayValue($announcement, ['body', 'content', 'message', 'description'], 'No announcement details available.');
                                @endphp

                                <button type="button"
                                        class="wiz-announcement-button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#dashboardAnnouncementModal{{ $index }}">
                                    <div class="wiz-announcement-item">
                                        <span class="wiz-dot"></span>
                                        <div class="flex-grow-1">
                                            <strong class="d-block text-dark wiz-announcement-title">{{ $announcementTitle }}</strong>
                                            <span class="wiz-muted">{{ $announcementDate }}</span>
                                            <div class="wiz-announcement-preview">{{ $announcementBody }}</div>
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <div class="wiz-empty text-start py-3">No announcements yet.</div>
                            @endforelse
                        </div>

                        <div class="wiz-announcement-footer">
                            {{ $announcementCount }} update{{ $announcementCount === 1 ? '' : 's' }} available
                        </div>
                    </div>
                </div>
            </div>

            @foreach(($announcements ?? []) as $index => $announcement)
                @php
                    $announcementTitle = $displayValue($announcement, ['title'], 'Announcement');
                    $announcementDate = $displayDate($displayValue($announcement, ['published_at', 'created_at'], null));
                    $announcementBody = $displayValue($announcement, ['body', 'content', 'message', 'description'], 'No announcement details available.');
                @endphp

                <div class="modal fade wiz-announcement-modal"
                     id="dashboardAnnouncementModal{{ $index }}"
                     tabindex="-1"
                     aria-labelledby="dashboardAnnouncementModalLabel{{ $index }}"
                     aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="wiz-announcement-modal-title" id="dashboardAnnouncementModalLabel{{ $index }}">
                                        {{ $announcementTitle }}
                                    </h5>
                                    <div class="wiz-announcement-modal-date">
                                        {{ $announcementDate }}
                                    </div>
                                </div>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="wiz-announcement-modal-content">
                                    {{ $announcementBody }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="row g-3 mb-3">
                @foreach(($metrics ?? []) as $metric)
                    @php
                        $progressValue = min(100, max(6, ($numberOnly($metric['value'] ?? 0) / max(1, $maxChartValue)) * 100));
                    @endphp
                    <div class="col-xxl-3 col-lg-4 col-md-6">
                        <div class="card wiz-stat-card">
                            <div class="card-body">
                                <div class="wiz-stat-icon tone-{{ $metric['tone'] ?? 'primary' }}">{{ $statIcon($metric['label'] ?? '', $metric['icon'] ?? '•') }}</div>
                                <div>
                                    <div class="wiz-stat-label">{{ $metric['label'] ?? '-' }}</div>
                                    <div class="wiz-stat-value">{{ $metric['value'] ?? '0' }}</div>
                                    <div class="wiz-muted">{{ $metric['sub'] ?? '' }}</div>
                                    <div class="wiz-mini-progress"><span style="width: {{ $progressValue }}%;"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mb-3 align-items-stretch">
                <div class="col-xl-8">
                    <div class="card wiz-panel-card wiz-chart-card h-100">
                        <div class="card-body">
                            <div class="wiz-panel-header">
                                <div>
                                    <h5 class="wiz-section-title">Operations Trend</h5>
                                    <div class="wiz-section-subtitle mt-1">A quick visual pulse of your active system counts</div>
                                </div>
                                <span class="wiz-muted">Overview graph</span>
                            </div>
                            <div class="wiz-bar-chart wiz-bar-chart-wide">
                                @forelse($chartRows as $row)
                                    @php
                                        $barHeight = min(100, max(9, ($row['value'] / $maxChartValue) * 100));
                                    @endphp
                                    <div class="wiz-bar-wrap" title="{{ $row['full'] }}: {{ number_format($row['value'], 2) }}">
                                        <div class="wiz-bar-value">{{ number_format($row['value']) }}</div>
                                        <div class="wiz-bar" style="height: {{ $barHeight }}%;"></div>
                                        <div class="wiz-bar-label">{{ $row['label'] }}</div>
                                    </div>
                                @empty
                                    <div class="wiz-empty">No graph data yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card wiz-panel-card wiz-chart-card h-100">
                        <div class="card-body">
                            <div class="wiz-panel-header">
                                <div>
                                    <h5 class="wiz-section-title">Module Mix</h5>
                                    <div class="wiz-section-subtitle mt-1">Balanced view of active modules</div>
                                </div>
                            </div>
                            <div class="wiz-donut-wrap wiz-donut-wrap-balanced">
                                <div class="wiz-donut" style="--p1: {{ $p1 }}%; --p2: {{ $p2 }}%; --p3: {{ $p3 }}%; --p4: {{ $p4 }}%;">
                                    <div class="wiz-donut-center">
                                        <div>{{ number_format($totalPulse) }}</div>
                                        <small class="wiz-muted">Total</small>
                                    </div>
                                </div>
                                <div>
                                    @foreach($donutRows as $index => $row)
                                        @php
                                            $percent = $totalPulse > 0 ? round(($row['value'] / $totalPulse) * 100, 1) : 0;
                                        @endphp
                                        <div class="wiz-legend-item">
                                            <span class="wiz-legend-left">
                                                <span class="wiz-legend-color" style="background: {{ $donutColors[$index] ?? '#94a3b8' }}"></span>
                                                {{ $row['full'] }}
                                            </span>
                                            <strong>{{ number_format($row['value']) }} ({{ $percent }}%)</strong>
                                        </div>
                                    @endforeach
                                    @if($donutOthers > 0)
                                        @php
                                            $percent = round(($donutOthers / $totalPulse) * 100, 1);
                                        @endphp
                                        <div class="wiz-legend-item">
                                            <span class="wiz-legend-left">
                                                <span class="wiz-legend-color" style="background: #e2e8f0"></span>
                                                Others
                                            </span>
                                            <strong>{{ number_format($donutOthers) }} ({{ $percent }}%)</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                @foreach(($moduleCards ?? []) as $module)
                    <div class="col-xxl-3 col-lg-6">
                        <div class="wiz-module-mini h-100">
                            <div class="wiz-module-head">
                                <div>
                                    <h5 class="wiz-section-title">{{ $module['title'] ?? '-' }}</h5>
                                    <div class="wiz-section-subtitle mt-1">{{ $module['description'] ?? '' }}</div>
                                </div>
                                <div class="wiz-module-icon icon-{{ \Illuminate\Support\Str::slug($module['title'] ?? 'module') }}">
                                @switch(strtolower($module['title'] ?? ''))
                                    @case('warehouse')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21V8l8-4 8 4v13M8 21v-7h8v7M8 10h8" />
                                        </svg>
                                        @break

                                    @case('sales')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 7-8M14 7h6v6" />
                                        </svg>
                                        @break

                                    @case('purchasing')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h15l-2 8H8L6 6ZM6 6 5 3H3M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                                        </svg>
                                        @break

                                    @case('human resource')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a4 4 0 1 0-8 0M4 21a8 8 0 0 1 16 0M17 8h4M19 6v4" />
                                        </svg>
                                        @break

                                    @default
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                @endswitch
                            </div>
                            </div>

                            @foreach(($module['links'] ?? []) as $link)
                                <div class="wiz-link-row">
                                    @if(!empty($link['route']))
                                        <a href="{{ route($link['route']) }}">{{ $link['label'] ?? '-' }}</a>
                                    @else
                                        <span>{{ $link['label'] ?? '-' }}</span>
                                    @endif
                                    <strong>{{ number_format((float) ($link['value'] ?? 0)) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-12">
                    <div class="card wiz-panel-card wiz-quick-panel h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <div>
                                    <h5 class="wiz-section-title">Quick Actions</h5>
                                    <div class="wiz-section-subtitle mt-1">Create records and jump into common workflows</div>
                                </div>
                                <span class="wiz-muted">Shortcuts</span>
                            </div>
                            <div class="wiz-quick-actions">
                                @foreach(($quickActions ?? []) as $action)
                                    @if(!empty($action['route']) && $canAccess($action['permission'] ?? null))
                                        <a class="wiz-quick-action" href="{{ route($action['route']) }}">
                                            <div class="wiz-action-icon tone-{{ $loop->iteration === 1 ? 'purple' : ($loop->iteration === 3 ? 'warning' : ($loop->iteration === 4 ? 'success' : 'primary')) }}">{{ $actionIcon($action['label'] ?? '') }}</div>
                                            <div>
                                                <strong>{{ $action['label'] }}</strong>
                                                <span>Open workflow</span>
                                            </div>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-4">
                    <div class="card wiz-panel-card h-100">
                        <div class="card-body">
                            <div class="wiz-panel-header">
                                <div>
                                    <h5 class="wiz-section-title">Recent Invoices</h5>
                                    <div class="wiz-section-subtitle mt-1">Sales activity</div>
                                </div>
                                @if($viewAllRoutes['invoices'])
                                    <a href="{{ $viewAllRoutes['invoices'] }}" class="wiz-muted text-decoration-none">View all</a>
                                @endif
                            </div>
                            @forelse(($recentInvoices ?? []) as $invoice)
                                <div class="wiz-list-row">
                                    <div class="wiz-list-main">
                                        <strong class="d-block text-dark">{{ $displayValue($invoice, ['invoice_no', 'invoice_number', 'reference_no'], 'Invoice') }}</strong>
                                        <span class="wiz-muted">{{ $displayValue($invoice, ['status'], 'No status') }}</span>
                                    </div>
                                    <div class="wiz-list-amount">
                                        <strong>{{ $displayMoney($invoice, ['balance_due', 'total_amount', 'amount', 'grand_total']) }}</strong>
                                        <div class="wiz-muted">{{ $displayDate($displayValue($invoice, ['due_date', 'created_at'], null)) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="wiz-empty">No invoices found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card wiz-panel-card h-100">
                        <div class="card-body">
                            <div class="wiz-panel-header">
                                <div>
                                    <h5 class="wiz-section-title">Recent Purchase Orders</h5>
                                    <div class="wiz-section-subtitle mt-1">Purchasing updates</div>
                                </div>
                                @if($viewAllRoutes['purchase_orders'])
                                    <a href="{{ $viewAllRoutes['purchase_orders'] }}" class="wiz-muted text-decoration-none">View all</a>
                                @endif
                            </div>
                            @forelse(($recentPurchaseOrders ?? []) as $po)
                                <div class="wiz-list-row">
                                    <div class="wiz-list-main">
                                        <strong class="d-block text-dark">{{ $displayValue($po, ['po_no', 'po_number', 'reference_no'], 'Purchase Order') }}</strong>
                                        <span class="wiz-muted">{{ $displayValue($po, ['status'], 'No status') }}</span>
                                    </div>
                                    <div class="wiz-list-amount">
                                        <strong>{{ $displayMoney($po, ['total_amount', 'amount', 'grand_total']) }}</strong>
                                        <div class="wiz-muted">{{ $displayDate($displayValue($po, ['created_at'], null)) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="wiz-empty">No purchase orders found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card wiz-panel-card h-100">
                        <div class="card-body">
                            <div class="wiz-panel-header">
                                <div>
                                    <h5 class="wiz-section-title">Warehouse Activity</h5>
                                    <div class="wiz-section-subtitle mt-1">Latest stock movement</div>
                                </div>
                                @if($viewAllRoutes['warehouse_activity'])
                                    <a href="{{ $viewAllRoutes['warehouse_activity'] }}" class="wiz-muted text-decoration-none">View all</a>
                                @endif
                            </div>
                            @forelse(($recentLedger ?? []) as $ledger)
                                <div class="wiz-list-row">
                                    <div class="wiz-list-main">
                                        <strong class="d-block text-dark">{{ $displayValue($ledger, ['reference_no', 'type', 'movement_type'], 'Movement') }}</strong>
                                        <span class="wiz-muted">{{ $displayDate($displayValue($ledger, ['created_at'], null)) }}</span>
                                    </div>
                                    <strong>{{ $displayValue($ledger, ['quantity'], '-') }}</strong>
                                </div>
                            @empty
                                <div class="wiz-empty">
                                    <div class="wiz-empty-icon">□</div>
                                    No warehouse activity found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
