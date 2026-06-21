
<x-app-layout>
    @php
        $profile = $employee->employeeProfile;

        $fullName = $employee->full_name ?: trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
        $employeeNo = $profile->employee_id ?? 'N/A';
        $position = optional($employee->position)->name ?: optional($profile->position)->name ?: 'No Designation';
        $department = optional($employee->department)->name ?: 'No Department';
        $branch = optional($employee->branch)->name ?: 'No Branch / Office';
        $dateHired = optional($profile->hire_date)->format('M d, Y') ?: 'N/A';
        $status = $profile->employment_status ?: $employee->status ?: 'N/A';
        $employmentType = $profile->employment_type ?: 'N/A';
        $supervisor = optional($profile->supervisor)->full_name ?: 'N/A';

        $personalComplete = $employee->first_name && $employee->last_name && $profile?->birth_date && $profile?->civil_status;
        $employmentComplete = $profile?->hire_date && $employee->department && ($employee->position || $profile?->position);
        $governmentComplete = $profile?->sss_number && $profile?->pagibig_number && $profile?->philhealth_number && $profile?->tax_id_number;
        $emergencyComplete = !empty($profile?->emergency_contact_name) && !empty($profile?->emergency_contact_number);

        $activeTab = request('tab', 'overview');

        $tabs = [
            'overview' => ['label' => 'Overview', 'icon' => 'fas fa-id-badge'],
            'personal' => ['label' => 'Personal Info', 'icon' => 'fas fa-user'],
            'employment' => ['label' => 'Employment Info', 'icon' => 'fas fa-briefcase'],
            'government' => ['label' => 'Government Info', 'icon' => 'fas fa-landmark'],
            'documents' => ['label' => 'Documents', 'icon' => 'fas fa-file-alt'],
            'leave' => ['label' => 'Leave', 'icon' => 'fas fa-calendar-check'],
            'evaluations' => ['label' => 'Evaluations', 'icon' => 'fas fa-clipboard-check'],
            'memos' => ['label' => 'Memo / Disciplinary', 'icon' => 'fas fa-exclamation-circle'],
            'movement' => ['label' => 'Movement History', 'icon' => 'fas fa-exchange-alt'],
            'training' => ['label' => 'Training', 'icon' => 'fas fa-chalkboard-teacher'],
            'exit' => ['label' => 'Exit Record', 'icon' => 'fas fa-sign-out-alt'],
        ];

        $requiredDocuments = [
            'NBI Clearance',
            'Medical Certificate',
            'SSS Registration Form',
            'PhilHealth Registration Form',
            'Pag-IBIG Membership Form',
        ];

        $uploadedDocumentTypes = $profile?->documents
            ? $profile->documents->pluck('document_type')->map(fn ($type) => strtolower(trim($type)))->toArray()
            : [];

        $missingDocumentsCount = collect($requiredDocuments)->filter(function ($doc) use ($uploadedDocumentTypes) {
            return !in_array(strtolower(trim($doc)), $uploadedDocumentTypes);
        })->count();

                $documentsComplete = $missingDocumentsCount === 0;

        $completed = collect([
            $personalComplete,
            $employmentComplete,
            $governmentComplete,
            $documentsComplete,
            $emergencyComplete,
        ])->filter()->count();

        $profilePercent = round(($completed / 5) * 100);
        
    @endphp

    <style>
    .wmc-201-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
    }

    .wmc-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        background: #eef2ff;
    }

    .wmc-tabs {
        overflow-x: auto;
        white-space: nowrap;
    }

    .wmc-tab-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 15px 18px;
        color: #64748b;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        font-size: 14px;
    }

    .wmc-tab-link.active,
    .wmc-tab-link:hover {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    .wmc-info-label {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .wmc-info-value {
        font-weight: 600;
        color: #111827;
    }

    .wmc-profile-ring {
        width: 135px;
        height: 135px;
        border-radius: 50%;
        background: conic-gradient(#22c55e {{ $profilePercent }}%, #e5e7eb 0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
        flex-shrink: 0;
    }

    .wmc-profile-ring-inner {
        width: 105px;
        height: 105px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .profile-completeness-wrap {
        display: flex;
        align-items: center;
        gap: 22px;
    }

    .profile-ring-area {
        flex: 0 0 145px;
        display: flex;
        justify-content: center;
    }

    .profile-requirements-area {
        flex: 1;
        min-width: 0;
    }

    .wmc-requirement-row {
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 9px 12px;
        margin-bottom: 9px;
        background: #fff;
        font-size: 13px;
    }

    .wmc-requirement-row span:first-child {
        color: #475569;
        font-weight: 500;
    }

    .wmc-requirement-row .badge {
        font-size: 11px;
        white-space: nowrap;
    }

    .wmc-requirement-row.is-complete {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .wmc-requirement-row.is-incomplete {
        border-color: #fecaca;
        background: #fef2f2;
    }

    .wmc-requirement-row.is-warning {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .wmc-mini-box {
        border: 1px solid #e7edf7;
        border-radius: 12px;
        padding: 18px;
        height: 100%;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .wmc-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }

    .wmc-footer-link {
        border-top: 1px solid #eef2f7;
        padding: 14px;
        text-align: center;
    }

    .action-btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    }

    .action-btn i {
        font-size: 16px;
    }

    .action-btn {
    width: 34px;
    height: 34px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    }

    .action-btn svg {
        display: block;
    }

    @media (max-width: 1500px) {
        .profile-completeness-wrap {
            flex-direction: column;
            align-items: stretch;
        }

        .profile-ring-area {
            flex: unset;
        }
    }


    .wmc-employee-show-page {
        padding-bottom: 90px !important;
    }

    .wmc-employee-show-page .wmc-overview-section,
    .wmc-employee-show-page .tab-content,
    .wmc-employee-show-page .card:last-child {
        margin-bottom: 28px;
    }

    @media (max-width: 991.98px) {
        .wmc-employee-show-page {
            padding-bottom: 110px !important;
        }
    }



    /* Profile Completeness compact icon status fix */
    .wmc-profile-completeness-card .card-body {
        padding: 22px 24px 24px !important;
    }

    .wmc-profile-completeness-card .profile-completeness-wrap {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 24px !important;
        min-height: 265px;
    }

    .wmc-profile-completeness-card .profile-ring-area {
        flex: 0 0 145px !important;
        display: flex !important;
        justify-content: center !important;
    }

    .wmc-profile-completeness-card .wmc-profile-ring {
        width: 132px !important;
        height: 132px !important;
        margin: 0 auto !important;
    }

    .wmc-profile-completeness-card .wmc-profile-ring-inner {
        width: 100px !important;
        height: 100px !important;
    }

    .wmc-profile-completeness-card .profile-requirements-area {
        flex: 1 !important;
        min-width: 0 !important;
        max-width: 205px !important;
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 9px !important;
    }

    .wmc-profile-completeness-card .wmc-requirement-row {
        width: 100% !important;
        min-height: 44px;
        margin-bottom: 0 !important;
        padding: 9px 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        border-radius: 12px !important;
    }

    .wmc-profile-completeness-card .wmc-requirement-row span:first-child {
        line-height: 1.25 !important;
        font-size: 13px !important;
    }

    .wmc-profile-status-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
    }

    .wmc-profile-status-icon svg {
        width: 15px;
        height: 15px;
        display: block;
    }

    .wmc-profile-status-icon.is-complete {
        background: #dcfce7;
        color: #16a34a;
    }

    .wmc-profile-status-icon.is-incomplete {
        background: #fee2e2;
        color: #dc2626;
    }

    .wmc-profile-status-icon.is-warning {
        background: #fef3c7;
        color: #f97316;
        font-size: 12px;
        font-weight: 800;
    }

    @media (max-width: 1600px) {
        .wmc-profile-completeness-card .profile-completeness-wrap {
            gap: 18px !important;
        }

        .wmc-profile-completeness-card .profile-ring-area {
            flex-basis: 132px !important;
        }

        .wmc-profile-completeness-card .profile-requirements-area {
            max-width: 190px !important;
        }
    }

    @media (max-width: 1399.98px) {
        .wmc-profile-completeness-card .profile-completeness-wrap {
            flex-direction: column !important;
        }

        .wmc-profile-completeness-card .profile-requirements-area {
            max-width: 100% !important;
            width: 100% !important;
        }
    }


    .wmc-profile-completeness-card .wmc-requirement-row span:first-child i {
        display: none !important;
    }

    .wmc-document-status-icon {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
    }

    .wmc-document-status-icon svg {
        width: 16px;
        height: 16px;
        display: block;
    }

    .wmc-document-status-icon.is-uploaded {
        background: #dcfce7;
        color: #16a34a;
    }

    .wmc-document-status-icon.is-missing {
        background: #fee2e2;
        color: #dc2626;
    }

    .wmc-missing-documents-card .wmc-requirement-row .d-flex {
        width: 100%;
        gap: 12px;
    }



    .wmc-expiry-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .wmc-expiry-badge.no-expiry {
        background: #e2e8f0;
        color: #475569;
    }

    .wmc-expiry-badge.valid {
        background: #dcfce7;
        color: #16a34a;
    }

    .wmc-expiry-badge.expiring-soon {
        background: #fef3c7;
        color: #d97706;
    }

    .wmc-expiry-badge.expired {
        background: #fee2e2;
        color: #dc2626;
    }



    /* Employee 201 Overview responsive fix for 100% browser scale */
    .wmc-overview-row > [class*="col-"] {
        display: flex;
    }

    .wmc-overview-row > [class*="col-"] > .card {
        width: 100%;
    }

    .wmc-overview-row .wmc-mini-box {
        padding: 14px 16px;
        min-height: 128px;
    }

    .wmc-overview-row .wmc-mini-box small {
        line-height: 1.35;
        word-break: normal;
        overflow-wrap: normal;
    }

    .wmc-overview-row .wmc-mini-box h2 {
        font-size: 28px;
        line-height: 1.1;
    }

    @media (min-width: 992px) {
        .wmc-overview-row .card-body {
            min-height: 365px;
        }
    }

    @media (min-width: 1200px) {
        .wmc-overview-row .profile-completeness-wrap {
            flex-direction: row !important;
            align-items: center !important;
        }
    }



    /* Header and tab justification for 100% browser scale with sidebar */
    .wmc-employee-header-card .card-body > .p-4 {
        padding: 24px 28px !important;
    }

    .wmc-employee-header-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 190px;
        align-items: flex-start;
        gap: 24px;
    }

    .wmc-employee-header-profile {
        display: flex;
        align-items: center;
        gap: 24px;
        min-width: 0;
    }

    .wmc-employee-header-actions {
        width: 190px;
        margin-left: auto;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 8px;
    }

    .wmc-employee-header-actions .badge,
    .wmc-employee-header-actions .btn {
        width: 100%;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
    }

    .wmc-employee-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(130px, 1fr));
        gap: 14px;
        width: 100%;
        max-width: 920px;
    }

    .wmc-employee-meta-item {
        min-width: 0;
        padding-right: 14px;
        border-right: 1px solid #e5e7eb;
    }

    .wmc-employee-meta-item:last-child {
        border-right: 0;
        padding-right: 0;
    }

    .wmc-employee-meta-label {
        display: block;
        margin-bottom: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .wmc-employee-meta-value {
        display: block;
        color: #475569;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }


    .wmc-employee-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 24px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .wmc-employee-status-pill::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 999px;
        flex: 0 0 7px;
    }

    .wmc-employee-status-pill.is-active {
        color: #15803d;
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .wmc-employee-status-pill.is-active::before {
        background: #22c55e;
    }

    .wmc-employee-status-pill.is-inactive {
        color: #b45309;
        background: #fffbeb;
        border-color: #fde68a;
    }

    .wmc-employee-status-pill.is-inactive::before {
        background: #f59e0b;
    }

    .wmc-header-info-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 18px 28px;
        align-items: start;
    }

    .wmc-header-info-item {
        min-width: 0;
    }

    .wmc-header-info-item .wmc-info-value {
        line-height: 1.35;
        overflow-wrap: normal;
        word-break: normal;
    }

    .wmc-header-info-item.is-wide {
        grid-column: span 2;
    }

    .wmc-header-info-item.is-nowrap .wmc-info-value {
        white-space: nowrap;
    }

    .wmc-tabs {
        display: flex;
        align-items: center;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: thin;
    }

    .wmc-tab-link {
        flex: 1 0 auto;
        justify-content: center;
        min-width: 124px;
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    @media (max-width: 1599.98px) {
        .wmc-employee-header-main {
            grid-template-columns: minmax(0, 1fr) 178px;
            gap: 20px;
        }

        .wmc-employee-header-actions {
            width: 178px;
        }

        .wmc-employee-meta-grid {
            grid-template-columns: repeat(4, minmax(110px, 1fr));
            gap: 12px;
        }

        .wmc-header-info-grid {
            grid-template-columns: repeat(3, minmax(190px, 1fr));
        }

        .wmc-header-info-item.is-wide {
            grid-column: span 1;
        }

        .wmc-tabs {
            justify-content: flex-start;
        }

        .wmc-tab-link {
            flex: 0 0 auto;
            min-width: 132px;
        }
    }

    @media (max-width: 1199.98px) {
        .wmc-employee-meta-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }

        .wmc-employee-meta-item:nth-child(2) {
            border-right: 0;
            padding-right: 0;
        }
    }

    @media (max-width: 991.98px) {
        .wmc-employee-header-main {
            grid-template-columns: 1fr;
        }

        .wmc-employee-header-profile {
            flex-direction: column;
            align-items: flex-start;
        }

        .wmc-employee-header-actions {
            width: 100%;
            margin-left: 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .wmc-employee-header-actions .badge {
            grid-column: 1 / -1;
        }

        .wmc-header-info-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .wmc-employee-header-actions {
            grid-template-columns: 1fr;
        }

        .wmc-header-info-grid,
        .wmc-employee-meta-grid {
            grid-template-columns: 1fr;
        }

        .wmc-employee-meta-item,
        .wmc-employee-meta-item:nth-child(2) {
            border-right: 0;
            padding-right: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .wmc-employee-meta-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }
    }



    /* Leave balance + recent leave side-by-side layout */
    .wmc-leave-overview-row > [class*="col-"] {
        display: flex;
    }

    .wmc-leave-overview-row > [class*="col-"] > .card {
        width: 100%;
    }

    .wmc-leave-balance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .wmc-leave-balance-grid .wmc-mini-box {
        min-height: 118px;
        padding: 15px 16px;
    }

    .wmc-leave-balance-grid .wmc-mini-box:last-child {
        grid-column: 1 / -1;
    }

    .wmc-leave-balance-grid.is-highlight-only {
        grid-template-columns: 1fr;
    }

    .wmc-leave-highlight-box {
        position: relative;
        overflow: hidden;
        border-width: 1.5px;
        min-height: 145px !important;
    }

    .wmc-leave-highlight-box::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        border-radius: 12px 0 0 12px;
    }

    .wmc-leave-highlight-box.sick-leave {
        border-color: #bbf7d0;
        background: linear-gradient(180deg, #ffffff, #f0fdf4);
    }

    .wmc-leave-highlight-box.sick-leave::before {
        background: #16a34a;
    }

    .wmc-leave-highlight-box.service-incentive-leave {
        border-color: #bfdbfe;
        background: linear-gradient(180deg, #ffffff, #eff6ff);
    }

    .wmc-leave-highlight-box.service-incentive-leave::before {
        background: #2563eb;
    }

    .wmc-leave-highlight-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 11px;
        font-weight: 700;
        background: #eef2ff;
        color: #2563eb;
    }

    .wmc-leave-balance-grid .wmc-mini-box h2 {
        font-size: 28px;
        line-height: 1.1;
    }

    .wmc-recent-leave-card .table-responsive {
        overflow-x: visible;
    }

    .wmc-recent-leave-card .wmc-table {
        width: 100%;
        table-layout: auto;
        margin-bottom: 0;
    }

    .wmc-recent-leave-card .wmc-table th,
    .wmc-recent-leave-card .wmc-table td {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
        padding: 10px 12px;
    }

    .wmc-recent-leave-card .wmc-table th:nth-child(1),
    .wmc-recent-leave-card .wmc-table td:nth-child(1) {
        min-width: 135px;
    }

    .wmc-recent-leave-card .wmc-table th:nth-child(4),
    .wmc-recent-leave-card .wmc-table td:nth-child(4) {
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .wmc-recent-leave-card .table-responsive {
            overflow-x: auto;
        }
    }

    @media (max-width: 575.98px) {
        .wmc-leave-balance-grid {
            grid-template-columns: 1fr;
        }

        .wmc-leave-balance-grid .wmc-mini-box:last-child {
            grid-column: auto;
        }
    }



    /* Bottom overview row balance: smaller movement, wider memo table */
    .wmc-bottom-overview-row .wmc-recent-memos-card .table-responsive {
        overflow-x: visible;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table {
        table-layout: fixed;
        width: 100%;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table th,
    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table td {
        white-space: normal;
        vertical-align: middle;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table th:nth-child(1),
    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table td:nth-child(1) {
        width: 30%;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table th:nth-child(2),
    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table td:nth-child(2) {
        width: 28%;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table th:nth-child(3),
    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table td:nth-child(3) {
        width: 24%;
    }

    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table th:nth-child(4),
    .wmc-bottom-overview-row .wmc-recent-memos-card .wmc-table td:nth-child(4) {
        width: 18%;
    }

    @media (max-width: 1399.98px) {
        .wmc-bottom-overview-row .wmc-recent-memos-card .table-responsive {
            overflow-x: auto;
        }
    }

    /* Recent Memos / Disciplinary overview table alignment */
    .wmc-recent-memos-table {
        width: 100%;
        table-layout: auto;
    }

    .wmc-recent-memos-table th,
    .wmc-recent-memos-table td {
        vertical-align: middle !important;
        text-align: left !important;
        white-space: nowrap;
    }

    .wmc-recent-memos-table th:nth-child(1),
    .wmc-recent-memos-table td:nth-child(1) {
        width: 26%;
    }

    .wmc-recent-memos-table th:nth-child(2),
    .wmc-recent-memos-table td:nth-child(2) {
        width: 26%;
    }

    .wmc-recent-memos-table th:nth-child(3),
    .wmc-recent-memos-table td:nth-child(3) {
        width: 30%;
    }

    .wmc-recent-memos-table th:nth-child(4),
    .wmc-recent-memos-table td:nth-child(4) {
        width: 18%;
    }

    .wmc-recent-memos-table td:nth-child(3) {
        font-weight: 500;
        color: #111827;
    }

    .wmc-recent-memos-table .badge {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 65px;
    }


    /* Uniform 201 File detail tabs: Personal / Employment / Government */
    .wmc-detail-sections {
        display: grid;
        gap: 18px;
    }

    .wmc-detail-section-card {
        border: 1px solid #edf0f5;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .wmc-detail-section-header {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .wmc-detail-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 700;
    }

    .wmc-detail-section-title i {
        color: #2563eb;
        font-size: 15px;
    }

    .wmc-detail-section-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .wmc-detail-section-body {
        padding: 22px;
    }

    .wmc-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .wmc-detail-grid.two-column {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .wmc-detail-item {
        min-height: 74px;
        padding: 14px 16px;
        border: 1px solid #edf0f5;
        border-radius: 13px;
        background: #fbfdff;
    }

    .wmc-detail-label {
        margin-bottom: 7px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .wmc-detail-value {
        color: #0f172a;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .wmc-detail-value.muted {
        color: #94a3b8;
        font-weight: 600;
    }

    @media (max-width: 1199.98px) {
        .wmc-detail-grid,
        .wmc-detail-grid.two-column {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .wmc-detail-grid,
        .wmc-detail-grid.two-column {
            grid-template-columns: 1fr;
        }

        .wmc-detail-section-header,
        .wmc-detail-section-body {
            padding: 18px;
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Employee 201 Tabs - Show All Tabs Without Horizontal Drag
    |--------------------------------------------------------------------------
    | Converts the tab bar into responsive compact pills so all tabs are visible
    | at 125% display scale. No horizontal scrollbar/dragging is needed.
    */
    .wmc-employee-tabs-card {
        overflow: visible !important;
    }

    .wmc-employee-tabs-card .wmc-tabs {
        display: grid !important;
        grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        gap: 8px !important;
        padding: 10px 12px !important;
        overflow-x: visible !important;
        overflow-y: visible !important;
        white-space: normal !important;
        scrollbar-width: none !important;
    }

    .wmc-employee-tabs-card .wmc-tabs::-webkit-scrollbar {
        display: none !important;
    }

    .wmc-employee-tabs-card .wmc-tab-link {
        width: 100% !important;
        min-width: 0 !important;
        min-height: 42px !important;
        flex: unset !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        padding: 9px 8px !important;
        border: 1px solid #e8eef8 !important;
        border-bottom: 1px solid #e8eef8 !important;
        border-radius: 12px !important;
        color: #64748b !important;
        background: #ffffff !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        line-height: 1.15 !important;
        text-align: center !important;
        white-space: normal !important;
        word-break: normal !important;
        overflow-wrap: anywhere !important;
    }

    .wmc-employee-tabs-card .wmc-tab-link i {
        font-size: 12px !important;
        line-height: 1 !important;
        flex: 0 0 auto !important;
    }

    .wmc-employee-tabs-card .wmc-tab-link.active,
    .wmc-employee-tabs-card .wmc-tab-link:hover {
        color: #2563eb !important;
        background: #eef4ff !important;
        border-color: #2563eb !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, .10) !important;
    }

    @media (max-width: 1599.98px) {
        .wmc-employee-tabs-card .wmc-tabs {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 1199.98px) {
        .wmc-employee-tabs-card .wmc-tabs {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 767.98px) {
        .wmc-employee-tabs-card .wmc-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 7px !important;
            padding: 9px !important;
        }

        .wmc-employee-tabs-card .wmc-tab-link {
            min-height: 40px !important;
            font-size: 12.5px !important;
            padding: 8px 6px !important;
        }
    }

    @media print {
        .btn,
        .wmc-tabs,
        .breadcrumb,
        .iq-navbar,
        .sidebar,
        .wmc-no-print {
            display: none !important;
        }

        .content-inner {
            margin-top: 0 !important;
        }
    }

    .wmc-header-info-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr 1.45fr 1.85fr 1.1fr;
    gap: 22px;
    align-items: start;
}

.wmc-header-info-grid .wmc-info-value {
    word-break: normal;
    overflow-wrap: normal;
    white-space: normal;
}

@media (max-width: 1399.98px) {
    .wmc-header-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    .wmc-header-info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

    /*
    |--------------------------------------------------------------------------
    | FINAL Employee 201 Overview Card Justification for 125% Display Scale
    |--------------------------------------------------------------------------
    | One source of spacing only: --wmc-201-row-gap.
    | This removes extra Bootstrap/Hope UI card margins and keeps each row neat.
    */
    .wmc-employee-show-page {
        --wmc-201-row-gap: 18px;
        --wmc-201-card-min-height: 350px;
        --wmc-201-leave-card-min-height: 335px;
        --wmc-201-bottom-card-min-height: 285px;
    }

    .wmc-employee-show-page .wmc-overview-row,
    .wmc-employee-show-page .wmc-leave-overview-row,
    .wmc-employee-show-page .wmc-bottom-overview-row {
        --bs-gutter-x: 1rem !important;
        --bs-gutter-y: 0 !important;
        margin-top: 0 !important;
        margin-bottom: var(--wmc-201-row-gap) !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .wmc-employee-show-page .wmc-overview-row > [class*="col-"],
    .wmc-employee-show-page .wmc-leave-overview-row > [class*="col-"],
    .wmc-employee-show-page .wmc-bottom-overview-row > [class*="col-"] {
        display: flex !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .wmc-employee-show-page .wmc-overview-row .wmc-201-card,
    .wmc-employee-show-page .wmc-leave-overview-row .wmc-201-card,
    .wmc-employee-show-page .wmc-bottom-overview-row .wmc-201-card {
        width: 100% !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden;
    }

    .wmc-employee-show-page .wmc-overview-row .card-body,
    .wmc-employee-show-page .wmc-leave-overview-row .card-body,
    .wmc-employee-show-page .wmc-bottom-overview-row .card-body {
        flex: 1 1 auto;
        min-height: 0;
    }

    .wmc-employee-show-page .wmc-overview-row .wmc-footer-link,
    .wmc-employee-show-page .wmc-leave-overview-row .wmc-footer-link,
    .wmc-employee-show-page .wmc-bottom-overview-row .wmc-footer-link {
        flex: 0 0 auto;
    }

    @media (min-width: 1200px) {
        .wmc-employee-show-page .wmc-overview-row .wmc-201-card {
            min-height: var(--wmc-201-card-min-height) !important;
        }

        .wmc-employee-show-page .wmc-leave-overview-row .wmc-201-card {
            min-height: var(--wmc-201-leave-card-min-height) !important;
        }

        .wmc-employee-show-page .wmc-bottom-overview-row .wmc-201-card {
            min-height: var(--wmc-201-bottom-card-min-height) !important;
        }
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .card-body,
    .wmc-employee-show-page .wmc-missing-documents-card .card-body {
        padding: 22px 24px !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .profile-completeness-wrap {
        min-height: 0 !important;
        height: 100%;
        align-items: center !important;
    }

    .wmc-employee-show-page .wmc-missing-documents-card .card-body {
        display: flex !important;
        flex-direction: column !important;
    }

    .wmc-employee-show-page .wmc-missing-documents-card .wmc-requirement-row,
    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row {
        margin-bottom: 10px !important;
    }

    .wmc-employee-show-page .wmc-missing-documents-card .wmc-requirement-row:last-child,
    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row:last-child {
        margin-bottom: 0 !important;
    }

    .wmc-employee-show-page .wmc-leave-balance-grid {
        gap: 14px !important;
    }

    .wmc-employee-show-page .wmc-leave-highlight-box {
        min-height: 122px !important;
    }

    .wmc-employee-show-page .wmc-recent-leave-card .card-body,
    .wmc-employee-show-page .wmc-recent-memos-card .card-body {
        overflow-x: auto;
    }

    @media (min-width: 1200px) and (max-width: 1599.98px) {
        .wmc-employee-show-page {
            --wmc-201-row-gap: 18px;
            --wmc-201-card-min-height: 345px;
            --wmc-201-leave-card-min-height: 325px;
            --wmc-201-bottom-card-min-height: 280px;
        }

        .wmc-employee-show-page .wmc-profile-ring {
            width: 120px !important;
            height: 120px !important;
        }

        .wmc-employee-show-page .wmc-profile-ring-inner {
            width: 92px !important;
            height: 92px !important;
        }

        .wmc-employee-show-page .wmc-requirement-row {
            padding: 8px 11px !important;
        }
    }

    @media (max-width: 1199.98px) {
        .wmc-employee-show-page {
            --wmc-201-row-gap: 16px;
        }

        .wmc-employee-show-page .wmc-overview-row,
        .wmc-employee-show-page .wmc-leave-overview-row,
        .wmc-employee-show-page .wmc-bottom-overview-row {
            --bs-gutter-y: 1rem !important;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Employee 201 Overview Responsive Card Spacing Fix
    |--------------------------------------------------------------------------
    | Keep desktop rows uniform, but add real row-gap when cards wrap on
    | 125% scale / narrower screens so cards never stick together.
    */
    .wmc-employee-show-page .wmc-overview-row,
    .wmc-employee-show-page .wmc-leave-overview-row,
    .wmc-employee-show-page .wmc-bottom-overview-row {
        row-gap: var(--wmc-201-row-gap) !important;
    }

    @media (max-width: 1399.98px) {
        .wmc-employee-show-page {
            --wmc-201-row-gap: 18px;
        }

        .wmc-employee-show-page .wmc-overview-row,
        .wmc-employee-show-page .wmc-leave-overview-row,
        .wmc-employee-show-page .wmc-bottom-overview-row {
            --bs-gutter-y: 0 !important;
            row-gap: var(--wmc-201-row-gap) !important;
        }
    }

    @media (max-width: 1199.98px) {
        .wmc-employee-show-page .wmc-overview-row,
        .wmc-employee-show-page .wmc-leave-overview-row,
        .wmc-employee-show-page .wmc-bottom-overview-row {
            --bs-gutter-y: 0 !important;
            row-gap: var(--wmc-201-row-gap) !important;
        }

        .wmc-employee-show-page .wmc-overview-row > [class*="col-"],
        .wmc-employee-show-page .wmc-leave-overview-row > [class*="col-"],
        .wmc-employee-show-page .wmc-bottom-overview-row > [class*="col-"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .profile-completeness-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
        }

        .wmc-employee-show-page .wmc-profile-ring-area {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-bottom: 14px;
        }
    }

    @media (max-width: 767.98px) {
        .wmc-employee-show-page {
            --wmc-201-row-gap: 14px;
        }

        .wmc-employee-show-page .wmc-overview-row .card-body,
        .wmc-employee-show-page .wmc-leave-overview-row .card-body,
        .wmc-employee-show-page .wmc-bottom-overview-row .card-body {
            padding: 18px !important;
        }

        .wmc-employee-show-page .wmc-leave-balance-grid {
            grid-template-columns: 1fr !important;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FINAL Profile Completeness Layout Fix - 125% Scale Responsive
    |--------------------------------------------------------------------------
    | Fixes the broken/uneven Profile Completeness card by removing conflicting
    | height behavior and using a controlled two-column layout on desktop.
    */
    .wmc-employee-show-page .wmc-profile-completeness-card .card-body {
        display: flex !important;
        flex-direction: column !important;
        padding: 24px 26px 22px !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .card-body > h5 {
        flex: 0 0 auto !important;
        margin-bottom: 18px !important;
        line-height: 1.2 !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .profile-completeness-wrap {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        height: auto !important;
        display: grid !important;
        grid-template-columns: minmax(150px, 0.85fr) minmax(240px, 1.15fr) !important;
        align-items: center !important;
        justify-content: center !important;
        column-gap: 24px !important;
        row-gap: 16px !important;
        width: 100% !important;
        margin: 0 !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .profile-ring-area {
        width: 100% !important;
        flex: none !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        margin: 0 !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-profile-ring {
        width: 138px !important;
        height: 138px !important;
        margin: 0 auto !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-profile-ring-inner {
        width: 104px !important;
        height: 104px !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .profile-requirements-area {
        width: 100% !important;
        max-width: none !important;
        min-width: 0 !important;
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row {
        width: 100% !important;
        min-height: 48px !important;
        margin: 0 !important;
        padding: 10px 13px !important;
        display: flex !important;
        align-items: center !important;
        border-radius: 12px !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row > .d-flex {
        width: 100% !important;
    }

    .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row span:first-child {
        min-width: 0 !important;
        line-height: 1.25 !important;
        white-space: normal !important;
    }

    @media (min-width: 1200px) and (max-width: 1499.98px) {
        .wmc-employee-show-page .wmc-profile-completeness-card .card-body {
            padding: 22px 22px 20px !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .profile-completeness-wrap {
            grid-template-columns: minmax(132px, 0.78fr) minmax(218px, 1.22fr) !important;
            column-gap: 18px !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .wmc-profile-ring {
            width: 124px !important;
            height: 124px !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .wmc-profile-ring-inner {
            width: 94px !important;
            height: 94px !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .wmc-requirement-row {
            min-height: 46px !important;
            padding: 9px 12px !important;
        }
    }

    @media (max-width: 1199.98px) {
        .wmc-employee-show-page .wmc-profile-completeness-card .profile-completeness-wrap {
            grid-template-columns: 1fr !important;
            align-items: stretch !important;
        }

        .wmc-employee-show-page .wmc-profile-completeness-card .profile-ring-area {
            margin-bottom: 4px !important;
        }
    }



    /*
    |--------------------------------------------------------------------------
    | FINAL Recent Memos / Disciplinary Clean Table Fix
    |--------------------------------------------------------------------------
    | Wider memo card + no broken table words at 125% display scale.
    | Keeps headers readable and allows the subject to truncate cleanly.
    */
    .wmc-employee-show-page .wmc-recent-memos-card .card-body {
        padding: 22px 24px !important;
        overflow-x: visible !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-card .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table {
        width: 100% !important;
        min-width: 540px;
        table-layout: fixed !important;
        border-collapse: collapse;
        margin-bottom: 0 !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table thead th {
        padding: 14px 12px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        letter-spacing: .02em;
        text-transform: uppercase;
        color: #475569 !important;
        background: #f8fafc !important;
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        hyphens: none !important;
        vertical-align: middle !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table tbody td {
        padding: 13px 12px !important;
        font-size: 14px !important;
        line-height: 1.35 !important;
        color: #1f2937 !important;
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        hyphens: none !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #edf2f7 !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table th:nth-child(1),
    .wmc-employee-show-page .wmc-recent-memos-clean-table td:nth-child(1) {
        width: 25% !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table th:nth-child(2),
    .wmc-employee-show-page .wmc-recent-memos-clean-table td:nth-child(2) {
        width: 25% !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table th:nth-child(3),
    .wmc-employee-show-page .wmc-recent-memos-clean-table td:nth-child(3) {
        width: 32% !important;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table th:nth-child(4),
    .wmc-employee-show-page .wmc-recent-memos-clean-table td:nth-child(4) {
        width: 18% !important;
        text-align: center !important;
    }

    .wmc-employee-show-page .wmc-memo-subject {
        display: block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 600;
        color: #111827;
    }

    .wmc-employee-show-page .wmc-recent-memos-clean-table .badge {
        min-width: 70px;
        justify-content: center;
        text-align: center;
        white-space: nowrap !important;
        font-size: 12px !important;
    }

    @media (max-width: 1399.98px) {
        .wmc-employee-show-page .wmc-bottom-overview-row > .col-12 {
            margin-bottom: 0 !important;
        }

        .wmc-employee-show-page .wmc-recent-memos-clean-table {
            min-width: 560px;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Employee 201 Client-Side Tabs
    |--------------------------------------------------------------------------
    | Keeps all tab contents loaded in the page and switches panels without
    | reloading the browser, while still updating the URL ?tab= value.
    */
    .wmc-tab-panel.d-none {
        display: none !important;
    }


    /*
    |--------------------------------------------------------------------------
    | Leave Balance Title No-Wrap Fix
    |--------------------------------------------------------------------------
    | Keeps "Leave Balance (as of date)" on one clean line at 125% scale,
    | while still allowing safe wrapping on very small screens.
    */
    .wmc-employee-show-page .wmc-leave-balance-title {
        display: flex !important;
        align-items: baseline !important;
        gap: 6px !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
        line-height: 1.25 !important;
        margin-bottom: 18px !important;
    }

    .wmc-employee-show-page .wmc-leave-balance-title small {
        display: inline-flex !important;
        flex: 0 0 auto !important;
        white-space: nowrap !important;
        font-size: 0.9em !important;
    }

    @media (max-width: 575.98px) {
        .wmc-employee-show-page .wmc-leave-balance-title {
            flex-wrap: wrap !important;
            white-space: normal !important;
        }
    }
</style>

    <div class="container-fluid content-inner mt-n5 py-0 wmc-employee-show-page">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3 wmc-no-print d-flex justify-content-end">
            <a href="{{ route('hr.employees.index') }}" class="btn btn-primary btn-sm px-4">
                <i class="fas fa-chevron-left me-1"></i> Back
            </a>
        </div>

        {{-- HEADER CARD --}}
        <div class="card wmc-201-card wmc-employee-header-card mb-3">
            <div class="card-body p-0">
                <div class="p-4">
                    <div class="wmc-employee-header-main">
                        <div class="wmc-employee-header-profile">
                            <img src="{{ asset('images/avatars/01.png') }}" class="wmc-avatar" alt="Employee Photo">

                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h3 class="mb-0">{{ $fullName ?: 'Employee Record' }}</h3>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                        {{ $employeeNo }}
                                    </span>

                                    <span class="wmc-employee-status-pill {{ strtolower($status) === 'active' ? 'is-active' : 'is-inactive' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <div class="wmc-employee-meta-grid mt-3">
                                    <div class="wmc-employee-meta-item">
                                        <span class="wmc-employee-meta-label">Position</span>
                                        <strong class="wmc-employee-meta-value">{{ $position }}</strong>
                                    </div>

                                    <div class="wmc-employee-meta-item">
                                        <span class="wmc-employee-meta-label">Department</span>
                                        <strong class="wmc-employee-meta-value">{{ $department }}</strong>
                                    </div>

                                    <div class="wmc-employee-meta-item">
                                        <span class="wmc-employee-meta-label">Branch</span>
                                        <strong class="wmc-employee-meta-value">{{ $branch }}</strong>
                                    </div>

                                    <div class="wmc-employee-meta-item">
                                        <span class="wmc-employee-meta-label">Date Hired</span>
                                        <strong class="wmc-employee-meta-value">{{ $dateHired }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wmc-employee-header-actions">
                            <a href="#tab-documents" class="btn btn-primary btn-sm" data-wmc-employee-tab="documents">
                                <i class="fas fa-upload me-1"></i> Upload Document
                            </a>

                            <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-pen me-1"></i> Edit Employee
                            </a>

                            <a href="{{ route('hr.employees.print-201', $employee) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-print me-1"></i> Print 201 File
                            </a>
                        </div>
                    </div>
                </div>

<div class="border-top p-4">
    <div class="wmc-header-info-grid">
        <div>
            <div class="wmc-info-label">Employment Type</div>
            <div class="wmc-info-value">{{ ucfirst($employmentType) }}</div>
        </div>

        <div>
            <div class="wmc-info-label">Probation End</div>
            <div class="wmc-info-value">N/A</div>
        </div>

        <div>
            <div class="wmc-info-label">Supervisor</div>
            <div class="wmc-info-value">{{ $supervisor }}</div>
        </div>

        <div>
            <div class="wmc-info-label">Work Schedule</div>
            <div class="wmc-info-value">Mon - Fri (8:00 AM - 5:00 PM)</div>
        </div>

        <div>
            <div class="wmc-info-label">Payroll Type</div>
            <div class="wmc-info-value">Semi-Monthly</div>
        </div>
    </div>
</div>
            </div>
        </div>

        {{-- TABS CARD --}}
        <div class="card wmc-201-card mb-3 wmc-employee-tabs-card">
            <div class="wmc-tabs">
                @foreach($tabs as $key => $tab)
                    <a href="#tab-{{ $key }}"
                       class="wmc-tab-link {{ $activeTab === $key ? 'active' : '' }}"
                       data-wmc-employee-tab="{{ $key }}">
                        <i class="{{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- OVERVIEW TAB PANEL --}}
        <div id="tab-overview" class="wmc-tab-panel {{ $activeTab === 'overview' ? '' : 'd-none' }}" data-wmc-tab-panel="overview">
        <div class="row gx-3 gy-0 mb-3 wmc-overview-row">
            <div class="col-12 col-xl-6">
                <div class="card wmc-201-card h-100 wmc-profile-completeness-card">
                    <div class="card-body">
                        <h5 class="mb-4">Profile Completeness</h5>

                        <div class="profile-completeness-wrap">
                            <div class="profile-ring-area">
                                <div class="wmc-profile-ring">
                                    <div class="wmc-profile-ring-inner">
                                        <h3 class="mb-0 text-success">{{ $profilePercent }}%</h3>
                                        <small>Complete</small>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-requirements-area">

                                <div class="wmc-requirement-row {{ $personalComplete ? 'is-complete' : 'is-incomplete' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span>
                                            {!! $personalComplete ? '<i class="fas fa-check-circle text-success me-2"></i>' : '<i class="fas fa-times-circle text-danger me-2"></i>' !!}
                                            Personal Info
                                        </span>
                                        @if($personalComplete)
                                            <span class="wmc-profile-status-icon is-complete" title="Complete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="wmc-profile-status-icon is-incomplete" title="Incomplete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="wmc-requirement-row {{ $employmentComplete ? 'is-complete' : 'is-incomplete' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span>
                                            {!! $employmentComplete ? '<i class="fas fa-check-circle text-success me-2"></i>' : '<i class="fas fa-times-circle text-danger me-2"></i>' !!}
                                            Employee Info
                                        </span>
                                        @if($employmentComplete)
                                            <span class="wmc-profile-status-icon is-complete" title="Complete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="wmc-profile-status-icon is-incomplete" title="Incomplete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="wmc-requirement-row {{ $governmentComplete ? 'is-complete' : 'is-incomplete' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span>
                                            {!! $governmentComplete ? '<i class="fas fa-check-circle text-success me-2"></i>' : '<i class="fas fa-times-circle text-danger me-2"></i>' !!}
                                            Government Info
                                        </span>
                                        @if($governmentComplete)
                                            <span class="wmc-profile-status-icon is-complete" title="Complete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="wmc-profile-status-icon is-incomplete" title="Incomplete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="wmc-requirement-row {{ $missingDocumentsCount > 0 ? 'is-warning' : 'is-complete' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span>
                                            @if($missingDocumentsCount > 0)
                                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                            @else
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                            @endif
                                            Documents
                                        </span>

                                        @if($missingDocumentsCount > 0)
                                            <span class="wmc-profile-status-icon is-warning" title="{{ $missingDocumentsCount }}">
                                                {{ $missingDocumentsCount }}
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width: 13px; height: 13px; margin-left: 2px;">
                                                    <path d="M12 8V12.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                                    <path d="M12 16H12.01" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                                    <path d="M10.3 4.3L2.8 17.3C2.2 18.3 2.9 19.5 4.1 19.5H19.9C21.1 19.5 21.8 18.3 21.2 17.3L13.7 4.3C13.1 3.3 10.9 3.3 10.3 4.3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="wmc-profile-status-icon is-complete" title="Complete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="wmc-requirement-row {{ $emergencyComplete ? 'is-complete' : 'is-incomplete' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span>
                                            {!! $emergencyComplete ? '<i class="fas fa-check-circle text-success me-2"></i>' : '<i class="fas fa-times-circle text-danger me-2"></i>' !!}
                                            Emergency Contact
                                        </span>
                                        @if($emergencyComplete)
                                            <span class="wmc-profile-status-icon is-complete" title="Complete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="wmc-profile-status-icon is-incomplete" title="Incomplete">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#" class="text-primary">View Incomplete Fields</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card wmc-201-card h-100 wmc-missing-documents-card">
                    <div class="card-body">
                        <h5 class="mb-4 d-flex align-items-center justify-content-between gap-2">
                            <span>Required Documents</span>
                            @if($missingDocumentsCount > 0)
                                <span class="wmc-profile-status-icon is-warning" title="{{ $missingDocumentsCount }} missing document(s)">
                                    {{ $missingDocumentsCount }}
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width: 13px; height: 13px; margin-left: 2px;">
                                        <path d="M12 8V12.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                        <path d="M12 16H12.01" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                                        <path d="M10.3 4.3L2.8 17.3C2.2 18.3 2.9 19.5 4.1 19.5H19.9C21.1 19.5 21.8 18.3 21.2 17.3L13.7 4.3C13.1 3.3 10.9 3.3 10.3 4.3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            @else
                                <span class="wmc-profile-status-icon is-complete" title="Complete">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            @endif
                        </h5>

@foreach($requiredDocuments as $requiredDoc)
    @php
        $isUploaded = in_array(strtolower(trim($requiredDoc)), $uploadedDocumentTypes);
    @endphp

    <div class="wmc-requirement-row {{ $isUploaded ? 'is-complete' : 'is-incomplete' }}">
        <div class="d-flex justify-content-between align-items-center">
            <span class="{{ $isUploaded ? 'text-success' : 'text-danger' }}">
                {{ $requiredDoc }}
            </span>

            @if($isUploaded)
                <span class="wmc-document-status-icon is-uploaded" title="Uploaded">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12.5L9.2 16.7L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @else
                <span class="wmc-document-status-icon is-missing" title="Missing">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 7L17 17M17 7L7 17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>
                </span>
            @endif
        </div>
    </div>
@endforeach

                    </div>
                    <div class="wmc-footer-link">
                        <a href="{{ route('hr.employees.show', $employee) }}?tab=documents" class="text-primary">Go to Documents Tab</a>
                    </div>
                </div>
            </div>

        </div>

        {{-- LEAVE OVERVIEW ROW --}}
        <div class="row gx-3 gy-0 mb-3 wmc-leave-overview-row">
            <div class="col-12 col-xl-4">
                <div class="card wmc-201-card h-100 wmc-leave-balance-card">
                    <div class="card-body">
                        <h5 class="mb-4 wmc-leave-balance-title">
                            <span>Leave Balance</span>
                            <small class="text-muted">(as of {{ now()->format('M d, Y') }})</small>
                        </h5>

                        @php
                            $leaveBalanceCollection = collect($leaveBalances ?? []);
                            $priorityLeaveNames = [
                                'Sick Leave',
                                'Service Incentive Leave',
                            ];

                            $priorityLeaveBalances = collect($priorityLeaveNames)->map(function ($priorityName) use ($leaveBalanceCollection) {
                                $matchedBalance = $leaveBalanceCollection->first(function ($balance) use ($priorityName) {
                                    return strtolower(trim(optional($balance->leaveType)->name ?? '')) === strtolower($priorityName);
                                });

                                return (object) [
                                    'name' => $priorityName,
                                    'balance' => $matchedBalance,
                                ];
                            });
                        @endphp

                        <div class="wmc-leave-balance-grid is-highlight-only">
                            @foreach($priorityLeaveBalances as $priorityLeave)
                                @php
                                    $remainingDays = (float) optional($priorityLeave->balance)->remaining;
                                    $remainingDisplay = fmod($remainingDays, 1.0) === 0.0
                                        ? number_format($remainingDays, 0)
                                        : rtrim(rtrim(number_format($remainingDays, 2), '0'), '.');
                                    $isSickLeave = strtolower($priorityLeave->name) === 'sick leave';
                                @endphp

                                <div class="wmc-mini-box wmc-leave-highlight-box {{ $isSickLeave ? 'sick-leave' : 'service-incentive-leave' }}">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <small class="fw-semibold">{{ $priorityLeave->name }}</small>
                                        <span class="wmc-leave-highlight-badge">Common</span>
                                    </div>

                                    <h2 class="{{ $isSickLeave ? 'text-success' : 'text-primary' }} mt-3 mb-1">
                                        {{ $priorityLeave->balance ? $remainingDisplay : '0' }}
                                    </h2>
                                    <small>days left</small>

                                    @unless($priorityLeave->balance)
                                        <div class="small text-muted mt-2">No credit record found for this leave type.</div>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#tab-leave" class="text-primary" data-wmc-employee-tab="leave">View Leave Summary</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card wmc-201-card h-100 wmc-recent-leave-card">
                    <div class="card-body">
                        <h5 class="mb-3">Recent Leave Requests</h5>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle wmc-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Date From</th>
                                        <th>Date To</th>
                                        <th>No. of Days</th>
                                        <th>Status</th>
                                        <th>Filed On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($leaveRequests ?? collect())->take(5) as $leaveRequest)
                                        @php
                                            $leaveStatus = strtolower((string) ($leaveRequest->status ?? 'pending'));
                                            $leaveStatusLabel = match ($leaveStatus) {
                                                'approved' => 'Approved',
                                                'rejected', 'not_approved', 'declined', 'disapproved' => 'Not Approved',
                                                'cancelled', 'canceled' => 'Cancelled',
                                                default => ucwords(str_replace('_', ' ', $leaveStatus)),
                                            };
                                            $leaveStatusClass = match ($leaveStatus) {
                                                'approved' => 'bg-success-subtle text-success',
                                                'pending', 'for_review', 'department_head_review', 'hr_review', 'admin_review' => 'bg-warning-subtle text-warning',
                                                'rejected', 'not_approved', 'declined', 'disapproved' => 'bg-danger-subtle text-danger',
                                                'cancelled', 'canceled' => 'bg-secondary-subtle text-secondary',
                                                default => 'bg-info-subtle text-info',
                                            };
                                            $leaveDays = (float) ($leaveRequest->days ?? 0);
                                            $leaveDaysDisplay = fmod($leaveDays, 1.0) === 0.0
                                                ? number_format($leaveDays, 0)
                                                : rtrim(rtrim(number_format($leaveDays, 2), '0'), '.');
                                        @endphp
                                        <tr>
                                            <td>{{ optional($leaveRequest->leaveType)->name ?? 'Leave Type' }}</td>
                                            <td>{{ optional($leaveRequest->start_datetime)->format('M d, Y') ?: '-' }}</td>
                                            <td>{{ optional($leaveRequest->end_datetime)->format('M d, Y') ?: '-' }}</td>
                                            <td>{{ $leaveDaysDisplay }}</td>
                                            <td><span class="badge {{ $leaveStatusClass }} rounded-pill px-3 py-2">{{ $leaveStatusLabel }}</span></td>
                                            <td>{{ optional($leaveRequest->created_at)->format('M d, Y') ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No leave requests recorded yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#tab-leave" class="text-primary" data-wmc-employee-tab="leave">View All Leave Requests</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARDS --}}
        <div class="row gx-3 gy-0 mb-3 wmc-bottom-overview-row">
            <div class="col-12 col-xxl-3">
                <div class="card wmc-201-card h-100">
                    <div class="card-body">
                        <h5 class="mb-4">Latest Movement</h5>

                        @if(!empty($movementSummary['latest']))
                            @php $latestMovement = $movementSummary['latest']; @endphp
                            <h5 class="fw-bold mb-3">{{ $latestMovement->movement_label }}</h5>
                            <p class="text-muted mb-1">From {{ $latestMovement->previous_value ?: '-' }}</p>
                            <p class="fw-semibold mb-3">To {{ $latestMovement->new_value ?: '-' }}</p>
                            <p class="text-muted mb-0">Effective: {{ optional($latestMovement->effective_date)->format('M d, Y') ?: '-' }}</p>
                        @else
                            <h5 class="fw-bold mb-3">No Movement Yet</h5>
                            <p class="text-muted mb-1">No promotion, transfer, or status movement has been recorded.</p>
                            <p class="text-muted mb-0">Use the Movement History tab to add a record.</p>
                        @endif
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#tab-movement" class="text-primary" data-wmc-employee-tab="movement">View Movement History</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-6">
                <div class="card wmc-201-card h-100 wmc-recent-memos-card">
                    <div class="card-body">
                        <h5 class="mb-3">Recent Memos / Disciplinary</h5>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle wmc-table wmc-recent-memos-table wmc-recent-memos-clean-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($memos ?? collect())->take(3) as $memo)
                                        <tr>
                                            <td>{{ optional($memo->issue_date)->format('M d, Y') ?: '-' }}</td>
                                            <td>{{ $memo->memo_label }}</td>
                                            <td>
                                                <span class="wmc-memo-subject" title="{{ $memo->subject }}">{{ $memo->subject }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $memo->status_badge_class }} rounded-pill px-3 py-2">
                                                    {{ $memo->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No memo records yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#tab-memos" class="text-primary" data-wmc-employee-tab="memos">View All Memos</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xxl-3">
                <div class="card wmc-201-card h-100">
                    <div class="card-body">
                        <h5 class="mb-4">Latest Evaluation</h5>

                        @php
                            $latestEvaluationTask = ($evaluationTasks ?? collect())->sortByDesc('updated_at')->first();
                        @endphp

                        @if($latestEvaluationTask)
                            <div class="mb-4">
                                <div class="wmc-info-label">Task</div>
                                <div class="wmc-info-value">{{ $latestEvaluationTask->title ?: optional($latestEvaluationTask->form)->title ?: 'Evaluation Task' }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="wmc-info-label">Form</div>
                                <div class="wmc-info-value">{{ optional($latestEvaluationTask->form)->title ?: '-' }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="wmc-info-label">Due Date</div>
                                <div class="wmc-info-value">{{ optional($latestEvaluationTask->due_date)->format('M d, Y') ?: '-' }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="wmc-info-label">Rating</div>
                                <div class="wmc-info-value text-warning">
                                    @if(! is_null($latestEvaluationTask->performance_score))
                                        {{ number_format((float) $latestEvaluationTask->performance_score, 2) }}%
                                    @else
                                        Not rated yet
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="wmc-info-label">Status</div>
                                <div class="wmc-info-value">{{ ucwords(str_replace('_', ' ', (string) $latestEvaluationTask->status)) }}</div>
                            </div>
                        @else
                            <div class="text-muted">
                                No evaluation record yet.
                            </div>
                        @endif
                    </div>
                    <div class="wmc-footer-link">
                        <a href="#tab-evaluations" class="text-primary" data-wmc-employee-tab="evaluations">View Evaluation History</a>
                    </div>
                </div>
            </div>
        </div>

        </div>

        {{-- PERSONAL INFO TAB PANEL --}}
        <div id="tab-personal" class="wmc-tab-panel {{ $activeTab === 'personal' ? '' : 'd-none' }}" data-wmc-tab-panel="personal">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['personal']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="wmc-detail-sections">
                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-user"></i> Personal Information
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Full Name</div>
                                        <div class="wmc-detail-value">{{ $fullName ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Birthday</div>
                                        <div class="wmc-detail-value">{{ optional($profile->birth_date)->format('M d, Y') ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Civil Status</div>
                                        <div class="wmc-detail-value">{{ $profile->civil_status ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Sex at Birth</div>
                                        <div class="wmc-detail-value">{{ $profile->sex_of_birth ?: $profile->gender ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Email Address</div>
                                        <div class="wmc-detail-value">{{ $employee->email ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Contact Number</div>
                                        <div class="wmc-detail-value">{{ $employee->phone_number ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Name of Spouse</div>
                                        <div class="wmc-detail-value">{{ $profile->spouse_name ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Name of Father</div>
                                        <div class="wmc-detail-value">{{ $profile->father_name ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Name of Mother</div>
                                        <div class="wmc-detail-value">{{ $profile->mother_name ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-graduation-cap"></i> Education Information
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Highest Educational Attainment</div>
                                        <div class="wmc-detail-value">{{ $profile->highest_education_attainment ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Course</div>
                                        <div class="wmc-detail-value">{{ $profile->course ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">School</div>
                                        <div class="wmc-detail-value">{{ $profile->school ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Year Graduated</div>
                                        <div class="wmc-detail-value">{{ $profile->year_graduated ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-map-marker-alt"></i> Address Information
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Province</div>
                                        <div class="wmc-detail-value">{{ $profile->province ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">City / Municipality</div>
                                        <div class="wmc-detail-value">{{ $profile->city ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Barangay</div>
                                        <div class="wmc-detail-value">{{ $profile->barangay ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-phone-alt"></i> Emergency Contact
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid two-column">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Emergency Contact Name</div>
                                        <div class="wmc-detail-value">{{ $profile->emergency_contact_name ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Emergency Contact Number</div>
                                        <div class="wmc-detail-value">{{ $profile->emergency_contact_number ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- EMPLOYMENT INFO TAB PANEL --}}
        <div id="tab-employment" class="wmc-tab-panel { $activeTab === 'employment' ? '' : 'd-none' }" data-wmc-tab-panel="employment">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['employment']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="wmc-detail-sections">
                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-briefcase"></i> Job Information
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Employee Number</div>
                                        <div class="wmc-detail-value">{{ $employeeNo }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Position</div>
                                        <div class="wmc-detail-value">{{ $position }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Department</div>
                                        <div class="wmc-detail-value">{{ $department }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Branch / Office</div>
                                        <div class="wmc-detail-value">{{ $branch }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Supervisor</div>
                                        <div class="wmc-detail-value">{{ $supervisor }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Date Hired</div>
                                        <div class="wmc-detail-value">{{ $dateHired }}</div>
                                    </div>
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Date of Regularization</div>
                                        <div class="wmc-detail-value">{{ optional($profile->regularization_date)->format('M d, Y') ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-id-card"></i> Employment Details
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Employment Status</div>
                                        <div class="wmc-detail-value">{{ ucfirst($status) }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Employment Type</div>
                                        <div class="wmc-detail-value">{{ ucfirst($employmentType) }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Probation End</div>
                                        <div class="wmc-detail-value">N/A</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Work Schedule</div>
                                        <div class="wmc-detail-value">Mon - Fri (8:00 AM - 5:00 PM)</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Payroll Type</div>
                                        <div class="wmc-detail-value">Semi-Monthly</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Salary / Rate</div>
                                        <div class="wmc-detail-value">
                                            @if(!empty($profile->employee_rate))
                                                {{ number_format((float) $profile->employee_rate, 2) }}
                                            @elseif(!empty($profile->salary))
                                                {{ number_format((float) $profile->salary, 2) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- GOVERNMENT INFO TAB PANEL --}}
        <div id="tab-government" class="wmc-tab-panel { $activeTab === 'government' ? '' : 'd-none' }" data-wmc-tab-panel="government">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['government']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="wmc-detail-sections">
                        <div class="wmc-detail-section-card">
                            <div class="wmc-detail-section-header">
                                <div>
                                    <h5 class="wmc-detail-section-title">
                                        <i class="fas fa-landmark"></i> Government Information
                                    </h5>
                                </div>
                            </div>

                            <div class="wmc-detail-section-body">
                                <div class="wmc-detail-grid">
                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">SSS Number</div>
                                        <div class="wmc-detail-value">{{ $profile->sss_number ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">Pag-IBIG Number</div>
                                        <div class="wmc-detail-value">{{ $profile->pagibig_number ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">PhilHealth Number</div>
                                        <div class="wmc-detail-value">{{ $profile->philhealth_number ?: '-' }}</div>
                                    </div>

                                    <div class="wmc-detail-item">
                                        <div class="wmc-detail-label">TIN Number</div>
                                        <div class="wmc-detail-value">{{ $profile->tax_id_number ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- DOCUMENTS TAB PANEL --}}
        <div id="tab-documents" class="wmc-tab-panel { $activeTab === 'documents' ? '' : 'd-none' }" data-wmc-tab-panel="documents">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['documents']['label'] }}</h4>
                </div>
                <div class="card-body">

                <div class="row g-3">

                    {{-- UPLOAD FORM --}}
                    <div class="col-lg-4">
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <h5 class="mb-3">Upload Document</h5>

                                <form action="{{ route('hr.employees.documents.upload', $employee) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Document Type</label>
                                        <select name="document_type" class="form-control" required>
                                            <option value="">Select Document</option>
                                            <option>NBI Clearance</option>
                                            <option>Medical Certificate</option>
                                            <option>SSS Registration Form</option>
                                            <option>PhilHealth Registration Form</option>
                                            <option>Pag-IBIG Membership Form</option>
                                            <option>Others</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">File</label>
                                        <input type="file" name="file" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Expiration Date</label>
                                        <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date') }}">
                                        <small class="text-muted">Leave blank if this document has no expiry.</small>
                                    </div>

                                    <button class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-1"></i> Upload
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- DOCUMENT LIST --}}
                    <div class="col-lg-8">
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <h5 class="mb-3">Uploaded Documents</h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead>
                                            <tr>
                                                <th>Document Type</th>
                                                <th>File Name</th>
                                                <th>Uploaded At</th>
                                                <th>Expiration Date</th>
                                                <th class="text-center">Status</th>
                                                <th width="150">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($profile->documents as $doc)
                                                <tr>
                                                    <td>{{ $doc->document_type }}</td>
                                                    <td>{{ $doc->file_name }}</td>
                                                    <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                                    <td>{{ optional($doc->expiration_date)->format('M d, Y') ?: 'No expiry' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $expiryStatus = 'No Expiry';
                                                            $expiryClass = 'no-expiry';

                                                            if ($doc->expiration_date) {
                                                                if ($doc->expiration_date->isPast() && ! $doc->expiration_date->isToday()) {
                                                                    $expiryStatus = 'Expired';
                                                                    $expiryClass = 'expired';
                                                                } elseif ($doc->expiration_date->lte(now()->addDays(30))) {
                                                                    $expiryStatus = 'Expiring Soon';
                                                                    $expiryClass = 'expiring-soon';
                                                                } else {
                                                                    $expiryStatus = 'Valid';
                                                                    $expiryClass = 'valid';
                                                                }
                                                            }
                                                        @endphp

                                                        <span class="wmc-expiry-badge {{ $expiryClass }}">
                                                            {{ $expiryStatus }}
                                                        </span>
                                                    </td>
<td>
    @php
        $fileUrl = asset('storage/' . $doc->file_path);
        $extension = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
    @endphp

    <div class="d-flex align-items-center gap-2">

        {{-- VIEW --}}
        <button type="button"
                class="btn btn-sm btn-outline-primary action-btn preview-document-btn"
                data-bs-toggle="modal"
                data-bs-target="#previewDocumentModal"
                data-file-url="{{ $fileUrl }}"
                data-file-name="{{ $doc->file_name }}"
                data-file-extension="{{ $extension }}"
                title="View Document">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
            </svg>
        </button>

        {{-- DOWNLOAD --}}
        <a href="{{ route('hr.documents.download', $doc->id) }}"
           class="btn btn-sm btn-outline-success action-btn"
           title="Download Document">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </a>

        {{-- DELETE --}}
        <form action="{{ route('hr.documents.delete', $doc->id) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Delete this document?')">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-sm btn-outline-danger action-btn"
                    title="Delete Document">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                    <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>

    </div>
</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        No documents uploaded yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>


                </div>
            </div>
        </div>

        {{-- LEAVE TAB PANEL --}}
        <div id="tab-leave" class="wmc-tab-panel { $activeTab === 'leave' ? '' : 'd-none' }" data-wmc-tab-panel="leave">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['leave']['label'] }}</h4>
                </div>
                <div class="card-body">
                    @php
                        $formatNumber = function ($value) {
                            $number = (float) $value;
                            return fmod($number, 1.0) === 0.0 ? number_format($number, 0) : number_format($number, 2);
                        };

                        $leaveBadgeClass = function ($status) {
                            return match ($status) {
                                'approved' => 'bg-success-subtle text-success',
                                'rejected' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning',
                            };
                        };
                    @endphp

                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Total Credits</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $formatNumber($leaveSummary['allocated'] ?? 0) }}</h3>
                                <small>allocated days</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Used Leave</small>
                                <h3 class="text-danger mt-3 mb-1">{{ $formatNumber($leaveSummary['used'] ?? 0) }}</h3>
                                <small>used days</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Remaining</small>
                                <h3 class="text-success mt-3 mb-1">{{ $formatNumber($leaveSummary['remaining'] ?? 0) }}</h3>
                                <small>available days</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Requests</small>
                                <h3 class="text-dark mt-3 mb-1">{{ $leaveRequests->count() }}</h3>
                                <small>total filed</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-5">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Leave Credits</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ now()->year }}</span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Leave Type</th>
                                                    <th class="text-center">Allocated</th>
                                                    <th class="text-center">Used</th>
                                                    <th class="text-center">Remaining</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leaveBalances as $balance)
                                                    <tr>
                                                        <td>{{ optional($balance->leaveType)->name ?? 'Leave Type' }}</td>
                                                        <td class="text-center">{{ $formatNumber($balance->allocated) }}</td>
                                                        <td class="text-center">{{ $formatNumber($balance->used) }}</td>
                                                        <td class="text-center fw-semibold text-success">{{ $formatNumber($balance->remaining) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            No leave credits recorded yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0">Leave History</h5>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-warning-subtle text-warning">Pending: {{ $leaveSummary['pending'] ?? 0 }}</span>
                                            <span class="badge bg-success-subtle text-success">Approved: {{ $leaveSummary['approved'] ?? 0 }}</span>
                                            <span class="badge bg-danger-subtle text-danger">Rejected: {{ $leaveSummary['rejected'] ?? 0 }}</span>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Leave Type</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th class="text-center">Days</th>
                                                    <th class="text-center">Status</th>
                                                    <th>Reviewed By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($leaveRequests as $leaveRequest)
                                                    <tr>
                                                        <td>{{ optional($leaveRequest->leaveType)->name ?? 'Leave' }}</td>
                                                        <td>{{ optional($leaveRequest->start_datetime)->format('M d, Y h:i A') ?: '-' }}</td>
                                                        <td>{{ optional($leaveRequest->end_datetime)->format('M d, Y h:i A') ?: '-' }}</td>
                                                        <td class="text-center">{{ $formatNumber($leaveRequest->days) }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $leaveBadgeClass($leaveRequest->status) }} rounded-pill px-3 py-2">
                                                                {{ ucfirst($leaveRequest->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional($leaveRequest->reviewer)->full_name ?: '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            No leave requests filed yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- EVALUATIONS TAB PANEL --}}
        <div id="tab-evaluations" class="wmc-tab-panel { $activeTab === 'evaluations' ? '' : 'd-none' }" data-wmc-tab-panel="evaluations">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['evaluations']['label'] }}</h4>
                </div>
                <div class="card-body">
                    @php
                        $evaluationBadgeClass = function ($status) {
                            return match ($status) {
                                'submitted', 'completed', 'reviewed' => 'bg-success-subtle text-success',
                                'in_progress' => 'bg-info-subtle text-info',
                                'cancelled' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning',
                            };
                        };

                        $performanceLabel = function ($score) {
                            if (is_null($score)) {
                                return '-';
                            }

                            $score = (float) $score;

                            return match (true) {
                                $score >= 90 => 'Excellent',
                                $score >= 80 => 'Good',
                                $score >= 70 => 'Satisfactory',
                                $score >= 60 => 'Needs Improvement',
                                default => 'Unsatisfactory',
                            };
                        };

                        $currentUser = auth()->user();
                        $canSeeEvaluator = $currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');
                    @endphp

                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Total Evaluations</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $evaluationSummary['total'] ?? 0 }}</h3>
                                <small>assigned records</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Pending</small>
                                <h3 class="text-warning mt-3 mb-1">{{ $evaluationSummary['pending'] ?? 0 }}</h3>
                                <small>waiting completion</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Submitted</small>
                                <h3 class="text-success mt-3 mb-1">{{ $evaluationSummary['submitted'] ?? 0 }}</h3>
                                <small>completed records</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Average Rating</small>
                                <h3 class="text-dark mt-3 mb-1">
                                    {{ ! is_null($evaluationSummary['average'] ?? null) ? number_format($evaluationSummary['average'], 2) . '%' : '-' }}
                                </h3>
                                <small>{{ $performanceLabel($evaluationSummary['average'] ?? null) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="mb-0">Evaluation History</h5>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                    Overall: {{ ! is_null($evaluationSummary['average'] ?? null) ? number_format($evaluationSummary['average'], 2) . '%' : 'No Rating Yet' }}
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Form</th>
                                            <th>Evaluator</th>
                                            <th>Due Date</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Rating</th>
                                            <th class="text-center">Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($evaluationTasks as $task)
                                            <tr>
                                                <td>{{ $task->title }}</td>
                                                <td>{{ optional($task->form)->title ?? '-' }}</td>
                                                <td>
                                                    @if($canSeeEvaluator)
                                                        {{ optional($task->evaluator)->full_name ?: '-' }}
                                                    @else
                                                        Anonymous
                                                    @endif
                                                </td>
                                                <td>{{ optional($task->due_date)->format('M d, Y') ?: '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ $evaluationBadgeClass($task->status) }} rounded-pill px-3 py-2">
                                                        {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-center fw-semibold">
                                                    {{ ! is_null($task->performance_score) ? number_format((float) $task->performance_score, 2) . '%' : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    @if(! is_null($task->performance_score))
                                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                                            {{ $performanceLabel($task->performance_score) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No evaluation records found yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- MEMO / DISCIPLINARY TAB PANEL --}}
        <div id="tab-memos" class="wmc-tab-panel { $activeTab === 'memos' ? '' : 'd-none' }" data-wmc-tab-panel="memos">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['memos']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Total Records</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $memoSummary['total'] ?? 0 }}</h3>
                                <small>memo records</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Open / Pending</small>
                                <h3 class="text-warning mt-3 mb-1">{{ $memoSummary['open'] ?? 0 }}</h3>
                                <small>needs follow-up</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Closed</small>
                                <h3 class="text-success mt-3 mb-1">{{ $memoSummary['closed'] ?? 0 }}</h3>
                                <small>completed records</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Latest Memo</small>
                                <h3 class="text-dark mt-3 mb-1">
                                    {{ !empty($memoSummary['latest']) ? optional($memoSummary['latest']->issue_date)->format('M d') : '-' }}
                                </h3>
                                <small>{{ !empty($memoSummary['latest']) ? $memoSummary['latest']->memo_label : 'no record yet' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-4">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <h5 class="mb-3">Add Memo Record</h5>

                                    <form action="{{ route('hr.employees.memos.store', $employee) }}" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label">Memo Type <span class="required-mark">*</span></label>
                                            <select name="memo_type" class="form-select" required>
                                                <option value="">Select Memo Type</option>
                                                <option value="memorandum" {{ old('memo_type') === 'memorandum' ? 'selected' : '' }}>Memorandum</option>
                                                <option value="notice_to_explain" {{ old('memo_type') === 'notice_to_explain' ? 'selected' : '' }}>Notice to Explain</option>
                                                <option value="written_warning" {{ old('memo_type') === 'written_warning' ? 'selected' : '' }}>Written Warning</option>
                                                <option value="suspension" {{ old('memo_type') === 'suspension' ? 'selected' : '' }}>Suspension</option>
                                                <option value="incident_report" {{ old('memo_type') === 'incident_report' ? 'selected' : '' }}>Incident Report</option>
                                                <option value="policy_reminder" {{ old('memo_type') === 'policy_reminder' ? 'selected' : '' }}>Policy Reminder</option>
                                                <option value="commendation" {{ old('memo_type') === 'commendation' ? 'selected' : '' }}>Commendation / Recognition</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Subject <span class="required-mark">*</span></label>
                                            <input type="text"
                                                   name="subject"
                                                   class="form-control"
                                                   value="{{ old('subject') }}"
                                                   placeholder="e.g. Policy Reminder / Late Submission"
                                                   required>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Issue Date <span class="required-mark">*</span></label>
                                                <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date') }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Status <span class="required-mark">*</span></label>
                                                <select name="status" class="form-select" required>
                                                    <option value="open" {{ old('status', 'open') === 'open' ? 'selected' : '' }}>Open</option>
                                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <label class="form-label">Attachment</label>
                                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                            <small class="text-muted">Allowed: PDF, image, DOC/DOCX. Max 5MB.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional details or notes">{{ old('remarks') }}</textarea>
                                        </div>

                                        <button class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-1"></i> Add Memo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0">Memo / Disciplinary History</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                            {{ $memoSummary['total'] ?? 0 }} record(s)
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Subject</th>
                                                    <th>Issue Date</th>
                                                    <th class="text-center">Status</th>
                                                    <th>Issued By</th>
                                                    <th>Remarks</th>
                                                    <th width="150" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($memos as $memo)
                                                    @php
                                                        $memoAttachmentUrl = $memo->attachment_url;
                                                        $memoAttachmentName = $memo->attachment_file_name ?: 'Attachment';
                                                        $memoAttachmentExtension = strtolower(pathinfo($memoAttachmentName, PATHINFO_EXTENSION));
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $memo->memo_label }}</td>
                                                        <td class="fw-semibold">{{ $memo->subject }}</td>
                                                        <td>{{ optional($memo->issue_date)->format('M d, Y') ?: '-' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $memo->status_badge_class }} rounded-pill px-3 py-2">
                                                                {{ $memo->status_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ optional($memo->issuer)->full_name ?: optional($memo->issuer)->username ?: '-' }}</td>
                                                        <td>{{ $memo->remarks ?: '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                @if($memoAttachmentUrl)
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-primary action-btn preview-document-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#previewDocumentModal"
                                                                            data-file-url="{{ $memoAttachmentUrl }}"
                                                                            data-file-name="{{ $memoAttachmentName }}"
                                                                            data-file-extension="{{ $memoAttachmentExtension }}"
                                                                            title="View Attachment">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2"/>
                                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                                        </svg>
                                                                    </button>

                                                                    <a href="{{ route('hr.memos.download', $memo) }}"
                                                                       class="btn btn-sm btn-outline-success action-btn"
                                                                       title="Download Attachment">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        </svg>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted small">No File</span>
                                                                @endif

                                                                <form action="{{ route('hr.memos.delete', $memo) }}"
                                                                      method="POST"
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Delete this memo record?')">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-danger action-btn"
                                                                            title="Delete Memo">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                            <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">
                                                            No memo / disciplinary records added yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- MOVEMENT HISTORY TAB PANEL --}}
        <div id="tab-movement" class="wmc-tab-panel { $activeTab === 'movement' ? '' : 'd-none' }" data-wmc-tab-panel="movement">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['movement']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Total Movements</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $movementSummary['total'] ?? 0 }}</h3>
                                <small>records added</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Promotions</small>
                                <h3 class="text-success mt-3 mb-1">{{ $movementSummary['promotions'] ?? 0 }}</h3>
                                <small>promotion records</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Transfers / Changes</small>
                                <h3 class="text-info mt-3 mb-1">{{ $movementSummary['transfers'] ?? 0 }}</h3>
                                <small>branch or department</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Latest Movement</small>
                                <h3 class="text-dark mt-3 mb-1">
                                    {{ !empty($movementSummary['latest']) ? optional($movementSummary['latest']->effective_date)->format('M d') : '-' }}
                                </h3>
                                <small>{{ !empty($movementSummary['latest']) ? $movementSummary['latest']->movement_label : 'no record yet' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-4">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <h5 class="mb-3">Add Movement Record</h5>

                                    <form action="{{ route('hr.employees.movements.store', $employee) }}" method="POST">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label">Movement Type <span class="required-mark">*</span></label>
                                            <select name="movement_type" class="form-select" required>
                                                <option value="">Select Movement Type</option>
                                                <option value="promotion" {{ old('movement_type') === 'promotion' ? 'selected' : '' }}>Promotion</option>
                                                <option value="transfer" {{ old('movement_type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                                <option value="department_change" {{ old('movement_type') === 'department_change' ? 'selected' : '' }}>Department Change</option>
                                                <option value="designation_change" {{ old('movement_type') === 'designation_change' ? 'selected' : '' }}>Designation Change</option>
                                                <option value="branch_change" {{ old('movement_type') === 'branch_change' ? 'selected' : '' }}>Branch Change</option>
                                                <option value="employment_status_change" {{ old('movement_type') === 'employment_status_change' ? 'selected' : '' }}>Employment Status Change</option>
                                                <option value="salary_adjustment" {{ old('movement_type') === 'salary_adjustment' ? 'selected' : '' }}>Salary Adjustment</option>
                                                <option value="regularization" {{ old('movement_type') === 'regularization' ? 'selected' : '' }}>Regularization</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Previous Value</label>
                                            <input type="text"
                                                   name="previous_value"
                                                   class="form-control"
                                                   value="{{ old('previous_value') }}"
                                                   placeholder="e.g. Junior Developer / Old Department">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">New Value</label>
                                            <input type="text"
                                                   name="new_value"
                                                   class="form-control"
                                                   value="{{ old('new_value') }}"
                                                   placeholder="e.g. Senior Developer / New Department">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Effective Date <span class="required-mark">*</span></label>
                                            <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes">{{ old('remarks') }}</textarea>
                                        </div>

                                        <button class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-1"></i> Add Movement
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0">Movement History</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                            {{ $movementSummary['total'] ?? 0 }} record(s)
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Previous</th>
                                                    <th>New</th>
                                                    <th>Effective Date</th>
                                                    <th>Encoded By</th>
                                                    <th>Remarks</th>
                                                    <th width="90" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($movements as $movement)
                                                    <tr>
                                                        <td>
                                                            <span class="badge {{ $movement->movement_badge_class }} rounded-pill px-3 py-2">
                                                                {{ $movement->movement_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $movement->previous_value ?: '-' }}</td>
                                                        <td class="fw-semibold">{{ $movement->new_value ?: '-' }}</td>
                                                        <td>{{ optional($movement->effective_date)->format('M d, Y') ?: '-' }}</td>
                                                        <td>{{ optional($movement->encoder)->full_name ?: optional($movement->encoder)->username ?: '-' }}</td>
                                                        <td>{{ $movement->remarks ?: '-' }}</td>
                                                        <td class="text-center">
                                                            <form action="{{ route('hr.movements.delete', $movement) }}"
                                                                  method="POST"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Delete this movement record?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger action-btn"
                                                                        title="Delete Movement">
                                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                        <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">
                                                            No movement records added yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- TRAINING TAB PANEL --}}
        <div id="tab-training" class="wmc-tab-panel { $activeTab === 'training' ? '' : 'd-none' }" data-wmc-tab-panel="training">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['training']['label'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Total Trainings</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $trainingSummary['total'] ?? 0 }}</h3>
                                <small>records added</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Valid</small>
                                <h3 class="text-success mt-3 mb-1">{{ $trainingSummary['valid'] ?? 0 }}</h3>
                                <small>active certificates</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Expiring Soon</small>
                                <h3 class="text-warning mt-3 mb-1">{{ $trainingSummary['expiring'] ?? 0 }}</h3>
                                <small>within 30 days</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Expired</small>
                                <h3 class="text-danger mt-3 mb-1">{{ $trainingSummary['expired'] ?? 0 }}</h3>
                                <small>needs renewal</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-4">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <h5 class="mb-3">Add Training Record</h5>

                                    <form action="{{ route('hr.employees.trainings.store', $employee) }}" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label">Training Title <span class="required-mark">*</span></label>
                                            <input type="text"
                                                   name="training_title"
                                                   class="form-control"
                                                   value="{{ old('training_title') }}"
                                                   placeholder="e.g. Basic Occupational Safety and Health"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Provider / Organizer</label>
                                            <input type="text"
                                                   name="provider"
                                                   class="form-control"
                                                   value="{{ old('provider') }}"
                                                   placeholder="e.g. DOLE / TESDA / Internal HR">
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Date Completed <span class="required-mark">*</span></label>
                                                <input type="date"
                                                       name="completed_at"
                                                       class="form-control"
                                                       value="{{ old('completed_at') }}"
                                                       required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Expiration Date</label>
                                                <input type="date"
                                                       name="expiration_date"
                                                       class="form-control"
                                                       value="{{ old('expiration_date') }}">
                                            </div>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <label class="form-label">Certificate Number</label>
                                            <input type="text"
                                                   name="certificate_number"
                                                   class="form-control"
                                                   value="{{ old('certificate_number') }}"
                                                   placeholder="Optional certificate/license number">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Certificate Attachment</label>
                                            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            <small class="text-muted">Allowed: PDF, JPG, PNG, WEBP. Max 5MB.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes">{{ old('remarks') }}</textarea>
                                        </div>

                                        <button class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-1"></i> Add Training
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0">Training & Certificates History</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                            {{ $trainingSummary['total'] ?? 0 }} record(s)
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Training</th>
                                                    <th>Provider</th>
                                                    <th>Completed</th>
                                                    <th>Certificate No.</th>
                                                    <th>Expiration</th>
                                                    <th class="text-center">Status</th>
                                                    <th width="150" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($trainings as $training)
                                                    @php
                                                        $certificateUrl = $training->certificate_url;
                                                        $certificateName = $training->certificate_file_name ?: 'Certificate';
                                                        $certificateExtension = strtolower(pathinfo($certificateName, PATHINFO_EXTENSION));
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $training->training_title }}</div>
                                                            @if($training->remarks)
                                                                <small class="text-muted">{{ $training->remarks }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $training->provider ?: '-' }}</td>
                                                        <td>{{ optional($training->completed_at)->format('M d, Y') ?: '-' }}</td>
                                                        <td>{{ $training->certificate_number ?: '-' }}</td>
                                                        <td>{{ optional($training->expiration_date)->format('M d, Y') ?: 'No Expiry' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $training->status_badge_class }} rounded-pill px-3 py-2">
                                                                {{ $training->status_label }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                @if($certificateUrl)
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-primary action-btn preview-document-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#previewDocumentModal"
                                                                            data-file-url="{{ $certificateUrl }}"
                                                                            data-file-name="{{ $certificateName }}"
                                                                            data-file-extension="{{ $certificateExtension }}"
                                                                            title="View Certificate">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2"/>
                                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                                        </svg>
                                                                    </button>

                                                                    <a href="{{ route('hr.trainings.download', $training) }}"
                                                                       class="btn btn-sm btn-outline-success action-btn"
                                                                       title="Download Certificate">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        </svg>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted small">No File</span>
                                                                @endif

                                                                <form action="{{ route('hr.trainings.delete', $training) }}"
                                                                      method="POST"
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Delete this training record?')">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-danger action-btn"
                                                                            title="Delete Training">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                            <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">
                                                            No training records added yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        {{-- EXIT RECORD TAB PANEL --}}
        <div id="tab-exit" class="wmc-tab-panel { $activeTab === 'exit' ? '' : 'd-none' }" data-wmc-tab-panel="exit">
            <div class="card wmc-201-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $tabs['exit']['label'] }}</h4>
                </div>
                <div class="card-body">
                    @php
                        $latestExit = $exitSummary['latest'] ?? null;
                    @endphp

                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Exit Status</small>
                                <h3 class="text-primary mt-3 mb-1">{{ $exitSummary['exit_status'] ?? 'No Exit Record' }}</h3>
                                <small>{{ ($exitSummary['total'] ?? 0) }} record(s)</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Clearance Status</small>
                                <h3 class="text-success mt-3 mb-1">{{ $exitSummary['clearance_status'] ?? 'Not Started' }}</h3>
                                <small>employee clearance</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Last Working Day</small>
                                <h3 class="text-dark mt-3 mb-1">{{ !empty($exitSummary['last_working_day']) ? optional($exitSummary['last_working_day'])->format('M d') : '-' }}</h3>
                                <small>{{ !empty($exitSummary['last_working_day']) ? optional($exitSummary['last_working_day'])->format('Y') : 'not recorded' }}</small>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="wmc-mini-box">
                                <small class="fw-semibold text-muted">Final Pay Status</small>
                                <h3 class="text-warning mt-3 mb-1">{{ $exitSummary['final_pay_status'] ?? 'Not Started' }}</h3>
                                <small>payroll clearance</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-4">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <h5 class="mb-3">Add Exit Record</h5>

                                    <form action="{{ route('hr.employees.exit-records.store', $employee) }}" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Resignation Date</label>
                                                <input type="date" name="resignation_date" class="form-control" value="{{ old('resignation_date') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Working Day <span class="required-mark">*</span></label>
                                                <input type="date" name="last_working_day" class="form-control" value="{{ old('last_working_day') }}" required>
                                            </div>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <label class="form-label">Exit Type <span class="required-mark">*</span></label>
                                            <select name="exit_type" class="form-select" required>
                                                <option value="">Select Exit Type</option>
                                                <option value="resignation" {{ old('exit_type') === 'resignation' ? 'selected' : '' }}>Resignation</option>
                                                <option value="termination" {{ old('exit_type') === 'termination' ? 'selected' : '' }}>Termination</option>
                                                <option value="end_of_contract" {{ old('exit_type') === 'end_of_contract' ? 'selected' : '' }}>End of Contract</option>
                                                <option value="retirement" {{ old('exit_type') === 'retirement' ? 'selected' : '' }}>Retirement</option>
                                                <option value="redundancy" {{ old('exit_type') === 'redundancy' ? 'selected' : '' }}>Redundancy</option>
                                                <option value="absconded" {{ old('exit_type') === 'absconded' ? 'selected' : '' }}>Absconded</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Reason</label>
                                            <textarea name="reason" class="form-control" rows="3" placeholder="Reason for separation">{{ old('reason') }}</textarea>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Clearance Status <span class="required-mark">*</span></label>
                                                <select name="clearance_status" class="form-select" required>
                                                    <option value="not_started" {{ old('clearance_status', 'not_started') === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                                    <option value="in_progress" {{ old('clearance_status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="cleared" {{ old('clearance_status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                                                    <option value="hold" {{ old('clearance_status') === 'hold' ? 'selected' : '' }}>On Hold</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Final Pay Status <span class="required-mark">*</span></label>
                                                <select name="final_pay_status" class="form-select" required>
                                                    <option value="not_started" {{ old('final_pay_status', 'not_started') === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                                    <option value="processing" {{ old('final_pay_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                                    <option value="released" {{ old('final_pay_status') === 'released' ? 'selected' : '' }}>Released</option>
                                                    <option value="hold" {{ old('final_pay_status') === 'hold' ? 'selected' : '' }}>On Hold</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-3 mb-3">
                                            <label class="form-label">Rehire Eligibility</label>
                                            <select name="rehire_eligibility" class="form-select">
                                                <option value="" {{ old('rehire_eligibility') === null ? 'selected' : '' }}>For Review</option>
                                                <option value="1" {{ old('rehire_eligibility') === '1' ? 'selected' : '' }}>Eligible</option>
                                                <option value="0" {{ old('rehire_eligibility') === '0' ? 'selected' : '' }}>Not Eligible</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Clearance / Exit Attachment</label>
                                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                            <small class="text-muted">Allowed: PDF, image, DOC/DOCX. Max 5MB.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes">{{ old('remarks') }}</textarea>
                                        </div>

                                        <button class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-1"></i> Add Exit Record
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="card border shadow-none h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <h5 class="mb-0">Exit Record History</h5>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                            {{ $exitSummary['total'] ?? 0 }} record(s)
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Exit Type</th>
                                                    <th>Resignation Date</th>
                                                    <th>Last Working Day</th>
                                                    <th class="text-center">Clearance</th>
                                                    <th class="text-center">Final Pay</th>
                                                    <th>Rehire</th>
                                                    <th>Encoded By</th>
                                                    <th width="150" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($exitRecords as $exitRecord)
                                                    @php
                                                        $exitAttachmentUrl = $exitRecord->attachment_url;
                                                        $exitAttachmentName = $exitRecord->attachment_file_name ?: 'Exit Attachment';
                                                        $exitAttachmentExtension = strtolower(pathinfo($exitAttachmentName, PATHINFO_EXTENSION));
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $exitRecord->exit_type_label }}</div>
                                                            @if($exitRecord->reason)
                                                                <small class="text-muted">{{ $exitRecord->reason }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($exitRecord->resignation_date)->format('M d, Y') ?: '-' }}</td>
                                                        <td>{{ optional($exitRecord->last_working_day)->format('M d, Y') ?: '-' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $exitRecord->clearance_status_badge_class }} rounded-pill px-3 py-2">
                                                                {{ $exitRecord->clearance_status_label }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $exitRecord->final_pay_status_badge_class }} rounded-pill px-3 py-2">
                                                                {{ $exitRecord->final_pay_status_label }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $exitRecord->rehire_eligibility_label }}</td>
                                                        <td>{{ optional($exitRecord->encoder)->full_name ?: optional($exitRecord->encoder)->username ?: '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                @if($exitAttachmentUrl)
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-primary action-btn preview-document-btn"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#previewDocumentModal"
                                                                            data-file-url="{{ $exitAttachmentUrl }}"
                                                                            data-file-name="{{ $exitAttachmentName }}"
                                                                            data-file-extension="{{ $exitAttachmentExtension }}"
                                                                            title="View Attachment">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12Z" stroke="currentColor" stroke-width="2"/>
                                                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                                        </svg>
                                                                    </button>

                                                                    <a href="{{ route('hr.exit-records.download', $exitRecord) }}"
                                                                       class="btn btn-sm btn-outline-success action-btn"
                                                                       title="Download Attachment">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                        </svg>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted small">No File</span>
                                                                @endif

                                                                <form action="{{ route('hr.exit-records.delete', $exitRecord) }}"
                                                                      method="POST"
                                                                      class="d-inline"
                                                                      onsubmit="return confirm('Delete this exit record?')">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-danger action-btn"
                                                                            title="Delete Exit Record">
                                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                                                            <path d="M4 7h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                                            <path d="M6 7l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                            <path d="M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-4">
                                                            No exit record added yet.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- DOCUMENT PREVIEW MODAL --}}
    <div class="modal fade" id="previewDocumentModal" tabindex="-1" aria-labelledby="previewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewDocumentModalLabel">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="min-height: 70vh;">
                    <div id="documentPreviewContent" class="w-100 h-100 text-center">
                        <p class="text-muted">Loading preview...</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" id="openDocumentNewTab" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                    </a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabLinks = document.querySelectorAll('[data-wmc-employee-tab]');
            const tabPanels = document.querySelectorAll('[data-wmc-tab-panel]');
            const validTabs = Array.from(tabPanels).map(panel => panel.dataset.wmcTabPanel);

            function showEmployeeTab(tabKey, updateUrl = true) {
                if (!validTabs.includes(tabKey)) {
                    tabKey = 'overview';
                }

                tabLinks.forEach(link => {
                    link.classList.toggle('active', link.dataset.wmcEmployeeTab === tabKey);
                });

                tabPanels.forEach(panel => {
                    panel.classList.toggle('d-none', panel.dataset.wmcTabPanel !== tabKey);
                });

                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tabKey);
                    window.history.pushState({ tab: tabKey }, '', url.toString());
                }
            }

            tabLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    showEmployeeTab(this.dataset.wmcEmployeeTab, true);
                });
            });

            window.addEventListener('popstate', function () {
                const params = new URLSearchParams(window.location.search);
                showEmployeeTab(params.get('tab') || 'overview', false);
            });

            const params = new URLSearchParams(window.location.search);
            showEmployeeTab(params.get('tab') || @json($activeTab), false);

            const previewButtons = document.querySelectorAll('.preview-document-btn');
            const previewContent = document.getElementById('documentPreviewContent');
            const modalTitle = document.getElementById('previewDocumentModalLabel');
            const openNewTab = document.getElementById('openDocumentNewTab');

            previewButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const fileUrl = this.dataset.fileUrl;
                    const fileName = this.dataset.fileName;
                    const extension = this.dataset.fileExtension;

                    modalTitle.textContent = fileName;
                    openNewTab.href = fileUrl;

                    if (extension === 'pdf') {
                        previewContent.innerHTML = `
                            <iframe src="${fileUrl}"
                                    style="width:100%; height:70vh; border:0; border-radius:12px;">
                            </iframe>
                        `;
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                        previewContent.innerHTML = `
                            <img src="${fileUrl}"
                                alt="${fileName}"
                                class="img-fluid rounded-3"
                                style="max-height:70vh; object-fit:contain;">
                        `;
                    } else {
                        previewContent.innerHTML = `
                            <div class="py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5>Preview not available</h5>
                                <p class="text-muted mb-0">
                                    This file type cannot be previewed. Please download or open it in a new tab.
                                </p>
                            </div>
                        `;
                    }
                });
            });
        });
    </script>
</x-app-layout>