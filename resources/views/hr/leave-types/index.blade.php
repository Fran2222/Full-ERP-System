<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $formatNumber = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
            };

            $cleanText = function ($value) {
                $value = (string) ($value ?? '');
                $value = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');

                if (str_contains($value, '>')) {
                    $parts = explode('>', $value);
                    $value = end($parts);
                }

                return trim($value);
            };

            $canCreate = auth()->user()->can('hr.leave-types.create');
            $canEdit = auth()->user()->can('hr.leave-types.edit');
            $canDelete = auth()->user()->can('hr.leave-types.delete');

            $allCount = $leaveTypes->count();
            $activeCount = \App\Models\LeaveType::where('status', 'active')->count();
            $inactiveCount = \App\Models\LeaveType::where('status', 'inactive')->count();
            $withPayCount = \App\Models\LeaveType::where('is_paid', true)->count();
            $withoutPayCount = \App\Models\LeaveType::where('is_paid', false)->count();
        @endphp

        <style>
            .leave-type-filter-btn {
                border-radius: 999px;
                padding: 7px 12px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #475569;
                font-size: 13px;
                font-weight: 600;
                transition: .15s ease;
            }

            .leave-type-filter-btn:hover,
            .leave-type-filter-btn.active {
                background: #2563eb;
                border-color: #2563eb;
                color: #ffffff;
            }

            .leave-type-filter-btn .badge {
                background: rgba(255, 255, 255, .25);
                color: inherit;
            }

            .leave-type-search-input {
                border-radius: 12px;
                min-height: 40px;
                font-size: 14px;
            }

            .leave-type-empty-state {
                display: none;
            }

            .leave-type-summary-card {
                border: 1px solid #edf0f5;
                border-radius: 16px;
                box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
            }

            .leave-type-summary-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .leave-type-summary-icon svg {
                width: 24px;
                height: 24px;
                display: block;
            }

            .leave-type-summary-icon.is-total {
                background: rgba(59, 77, 240, .12);
                color: #3b4df0;
            }

            .leave-type-summary-icon.is-active {
                background: rgba(22, 163, 74, .12);
                color: #16a34a;
            }

            .leave-type-summary-icon.is-with-pay {
                background: rgba(37, 99, 235, .12);
                color: #2563eb;
            }

            .leave-type-summary-icon.is-without-pay {
                background: rgba(245, 158, 11, .16);
                color: #f59e0b;
            }

            .leave-type-list-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .leave-type-table {
                table-layout: fixed;
                width: 100%;
                margin-bottom: 0;
            }

            .leave-type-table th {
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.03em;
                color: #8a94a6;
                background: #f4f6fa;
                white-space: nowrap;
                vertical-align: middle;
                font-weight: 700;
                padding: 12px 9px;
            }

            .leave-type-table td {
                vertical-align: middle;
                color: #071437;
                font-size: 14px;
                padding: 13px 9px;
            }

            .leave-type-table tbody tr {
                height: 66px;
            }

            .leave-type-table tbody tr:hover {
                background: #f8fafc;
            }

            .leave-type-cell-wrap {
                white-space: normal;
                line-height: 1.35;
                word-break: normal;
            }

            .leave-type-cell-nowrap {
                white-space: nowrap;
            }

            .leave-type-description {
                display: block;
                margin-top: 3px;
                font-size: 12px;
                color: #64748b;
                line-height: 1.35;
            }

            .leave-type-category-pill,
            .leave-type-status-pill,
            .leave-type-credits-pill {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                line-height: 1;
                white-space: nowrap;
            }

            .leave-type-category-pill {
                padding: 6px 9px;
            }

            .leave-type-category-paid {
                background: #eef2ff;
                color: #3b5bdb;
            }

            .leave-type-category-unpaid {
                background: #fff7ed;
                color: #f97316;
            }

            .leave-type-status-pill {
                border-radius: 5px;
                padding: 4px 8px;
                text-transform: uppercase;
            }

            .leave-type-status-active {
                background: #16a34a;
                color: #ffffff;
            }

            .leave-type-status-inactive {
                background: #64748b;
                color: #ffffff;
            }

            .leave-type-credits-pill {
                padding: 6px 9px;
                background: #f8fafc;
                color: #334155;
            }

            .leave-type-action-wrap {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }

            .leave-type-action-wrap form {
                margin: 0;
            }

            .leave-type-action-btn,
            .wmc-action-btn {
                width: 32px !important;
                height: 32px !important;
                padding: 0 !important;
                border-radius: 8px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                box-shadow: none !important;
                transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease !important;
            }

            .wmc-action-btn svg,
            .wmc-action-btn svg path {
                opacity: 1 !important;
                visibility: visible !important;
                stroke: #ffffff !important;
            }

            .wmc-action-edit,
            .wmc-action-edit:hover,
            .wmc-action-edit:focus,
            .wmc-action-edit:active {
                background-color: #3b4df0 !important;
                border-color: #3b4df0 !important;
                color: #ffffff !important;
            }

            .wmc-action-delete,
            .wmc-action-delete:hover,
            .wmc-action-delete:focus,
            .wmc-action-delete:active {
                background-color: #c83224 !important;
                border-color: #c83224 !important;
                color: #ffffff !important;
            }

            .wmc-action-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 10px rgba(15, 23, 42, .12) !important;
            }

            .leave-type-sort-btn {
                font-size: 12px;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .leave-type-sort-link {
                width: 100%;
                color: #8a94a6;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                font-size: 12px;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                font-weight: 700;
                background: transparent;
                border: 0;
                padding: 0;
            }

            .leave-type-sort-link:hover {
                color: #3b4df0;
            }

            .leave-type-sort-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 16px;
                height: 16px;
                flex: 0 0 16px;
            }

            .leave-type-sort-icon svg {
                width: 16px;
                height: 16px;
                overflow: visible;
                display: block;
            }

            .leave-type-sort-up,
            .leave-type-sort-down {
                fill: none;
                stroke: #d7dce6;
                stroke-width: 2.2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .leave-type-sort-icon.active.asc .leave-type-sort-up,
            .leave-type-sort-icon.active.desc .leave-type-sort-down {
                stroke: #3b4df0;
            }

            .leave-type-sort-link:hover .leave-type-sort-up,
            .leave-type-sort-link:hover .leave-type-sort-down {
                stroke: #aeb6c5;
            }

            .leave-type-sort-link:hover .leave-type-sort-icon.active.asc .leave-type-sort-up,
            .leave-type-sort-link:hover .leave-type-sort-icon.active.desc .leave-type-sort-down {
                stroke: #3b4df0;
            }

            @media (max-width: 1199.98px) {
                .leave-type-table {
                    min-width: 980px;
                }
            }

            @media (max-width: 767.98px) {
                .leave-type-search-box {
                    width: 100%;
                    min-width: 100% !important;
                }
            }
        </style>

        <div class="row g-3">
            <div class="col-xl-4 col-md-6">
                <div class="card leave-type-summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <small class="text-muted d-block mb-2">Total Leave Types</small>
                                <h3 class="mb-0">{{ $allCount }}</h3>
                            </div>

                            <div class="leave-type-summary-icon is-total">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 4H17C18.1 4 19 4.9 19 6V18C19 19.1 18.1 20 17 20H7C5.9 20 5 19.1 5 18V6C5 4.9 5.9 4 7 4Z" stroke="currentColor" stroke-width="1.7"/>
                                    <path d="M8.5 8H15.5M8.5 12H15.5M8.5 16H12.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card leave-type-summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <small class="text-muted d-block mb-2">With Pay</small>
                                <h3 class="mb-0 text-primary">{{ $withPayCount }}</h3>
                            </div>

                            <div class="leave-type-summary-icon is-with-pay">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 3V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M16.5 7.5C16.5 5.8 14.5 4.8 12 4.8C9.5 4.8 7.5 5.8 7.5 7.5C7.5 11.5 16.5 9.5 16.5 14.5C16.5 16.2 14.5 17.2 12 17.2C9.5 17.2 7.5 16.2 7.5 14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card leave-type-summary-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <small class="text-muted d-block mb-2">Without Pay</small>
                                <h3 class="mb-0 text-warning">{{ $withoutPayCount }}</h3>
                            </div>

                            <div class="leave-type-summary-icon is-without-pay">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                                    <path d="M12 5V19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                                    <path d="M7 7L17 17" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-12">
                <div class="card rounded-4 leave-type-list-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">Leave Type List</h4>
                            <p class="mb-0 text-secondary">
                                Manage active/inactive leave types and paid/unpaid categories.
                            </p>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            @if($canCreate)
                                <a href="{{ route('hr.leave-types.create') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                                    Add Leave Type
                                </a>
                            @endif

                            <a href="{{ route('hr.leave.index') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                                Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="leave-type-filter-btn active" data-leave-filter="all">
                                    All <span class="badge ms-1">{{ $allCount }}</span>
                                </button>

                                <button type="button" class="leave-type-filter-btn" data-leave-filter="active">
                                    Active <span class="badge ms-1">{{ $activeCount }}</span>
                                </button>

                                <button type="button" class="leave-type-filter-btn" data-leave-filter="inactive">
                                    Inactive <span class="badge ms-1">{{ $inactiveCount }}</span>
                                </button>

                                <button type="button" class="leave-type-filter-btn" data-leave-filter="paid">
                                    With Pay <span class="badge ms-1">{{ $withPayCount }}</span>
                                </button>

                                <button type="button" class="leave-type-filter-btn" data-leave-filter="unpaid">
                                    Without Pay <span class="badge ms-1">{{ $withoutPayCount }}</span>
                                </button>
                            </div>

                            <div class="leave-type-search-box" style="min-width: 280px;">
                                <input type="text"
                                       id="leaveTypeSearch"
                                       class="form-control leave-type-search-input"
                                       placeholder="Search leave type...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle leave-type-table" id="leaveTypesTable">
                                @if($canEdit || $canDelete)
                                    <colgroup>
                                        <col style="width: 6%;">
                                        <col style="width: 25%;">
                                        <col style="width: 14%;">
                                        <col style="width: 11%;">
                                        <col style="width: 10%;">
                                        <col style="width: 12%;">
                                        <col style="width: 12%;">
                                        <col style="width: 10%;">
                                    </colgroup>
                                @else
                                    <colgroup>
                                        <col style="width: 7%;">
                                        <col style="width: 29%;">
                                        <col style="width: 16%;">
                                        <col style="width: 12%;">
                                        <col style="width: 11%;">
                                        <col style="width: 13%;">
                                        <col style="width: 12%;">
                                    </colgroup>
                                @endif

                                <thead>
                                    <tr>
                                        <th class="text-center">
                                            <button type="button"
                                                    class="leave-type-sort-link"
                                                    data-sort-column="id">
                                                ID
                                                <span class="leave-type-sort-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path class="leave-type-sort-up" d="M8 20V5M8 5L2.5 10.5M8 5L13.5 10.5" />
                                                        <path class="leave-type-sort-down" d="M22 4V19M22 19L16.5 13.5M22 19L27.5 13.5" />
                                                    </svg>
                                                </span>
                                            </button>
                                        </th>

                                        <th>
                                            <button type="button"
                                                    class="leave-type-sort-link justify-content-start"
                                                    data-sort-column="name">
                                                Leave Type
                                                <span class="leave-type-sort-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path class="leave-type-sort-up" d="M8 20V5M8 5L2.5 10.5M8 5L13.5 10.5" />
                                                        <path class="leave-type-sort-down" d="M22 4V19M22 19L16.5 13.5M22 19L27.5 13.5" />
                                                    </svg>
                                                </span>
                                            </button>
                                        </th>

                                        <th class="text-center">Category</th>
                                        <th class="text-center">Credits</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Added On</th>
                                        <th class="text-center">Updated On</th>

                                        @if($canEdit || $canDelete)
                                            <th class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($leaveTypes as $leaveType)
                                        @php
                                            $name = $cleanText($leaveType->name);
                                            $code = $cleanText($leaveType->code ?? '');
                                            $description = $cleanText($leaveType->description ?? '');
                                            $status = $leaveType->status ?? 'active';
                                            $isPaid = (bool) $leaveType->is_paid;

                                            $filterType = $isPaid ? 'paid' : 'unpaid';
                                            $searchText = strtolower(
                                                $name . ' ' .
                                                $code . ' ' .
                                                $description . ' ' .
                                                $status . ' ' .
                                                ($isPaid ? 'with pay paid leave with pay' : 'without pay unpaid leave without pay')
                                            );
                                        @endphp

                                        <tr class="leave-type-row"
                                            data-id="{{ $leaveType->id }}"
                                            data-name="{{ strtolower($name) }}"
                                            data-status="{{ $status }}"
                                            data-type="{{ $filterType }}"
                                            data-search="{{ $searchText }}">

                                            <td class="text-center leave-type-cell-nowrap">
                                                {{ $leaveType->id }}
                                            </td>

                                            <td>
                                                <div class="fw-semibold leave-type-cell-wrap">
                                                    {{ $name }}
                                                </div>

                                                @if($description && $description !== $name)
                                                    <span class="leave-type-description">
                                                        {{ \Illuminate\Support\Str::limit($description, 58) }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                @if($isPaid)
                                                    <span class="leave-type-category-pill leave-type-category-paid">
                                                        With Pay
                                                    </span>
                                                @else
                                                    <span class="leave-type-category-pill leave-type-category-unpaid">
                                                        Without Pay
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-center leave-type-cell-nowrap">
                                                @if((float) $leaveType->default_credits > 0)
                                                    <span class="leave-type-credits-pill">
                                                        {{ $formatNumber($leaveType->default_credits) }} day(s)
                                                    </span>
                                                @else
                                                    <span class="text-secondary">No limit</span>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <span class="leave-type-status-pill leave-type-status-{{ strtolower($status) }}">
                                                    {{ strtoupper($status) }}
                                                </span>
                                            </td>

                                            <td class="text-center leave-type-cell-nowrap">
                                                {{ optional($leaveType->created_at)->format('M d, Y') }}
                                            </td>

                                            <td class="text-center leave-type-cell-nowrap">
                                                {{ optional($leaveType->updated_at)->format('M d, Y') }}
                                            </td>

                                            @if($canEdit || $canDelete)
                                                <td class="text-center">
                                                    <div class="leave-type-action-wrap">
                                                        @if($canEdit)
                                                            <a href="{{ route('hr.leave-types.edit', $leaveType->id) }}"
                                                               class="btn btn-sm wmc-action-btn wmc-action-edit"
                                                               title="Edit Leave Type">
                                                                <i class="icon">
                                                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                                                        <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                                                              stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        @endif

                                                        @if($canDelete)
                                                            <button type="button"
                                                                    class="btn btn-sm wmc-action-btn wmc-action-delete js-leave-type-delete-confirm"
                                                                    data-form-id="deleteLeaveTypeForm{{ $leaveType->id }}"
                                                                    data-name="{{ e($name) }}"
                                                                    title="Delete Leave Type">
                                                                <i class="icon">
                                                                    <svg width="18" viewBox="0 0 24 24" fill="none">
                                                                        <path d="M3 6H5H21" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                        <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6"
                                                                              stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                        <path d="M10 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                        <path d="M14 11V17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                        <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6"
                                                                              stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                                                                    </svg>
                                                                </i>
                                                            </button>

                                                            <form id="deleteLeaveTypeForm{{ $leaveType->id }}"
                                                                  action="{{ route('hr.leave-types.destroy', $leaveType->id) }}"
                                                                  method="POST"
                                                                  class="d-none">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ ($canEdit || $canDelete) ? 8 : 7 }}" class="text-center text-secondary py-4">
                                                No leave types found.
                                            </td>
                                        </tr>
                                    @endforelse

                                    <tr id="leaveTypeEmptyState" class="leave-type-empty-state">
                                        <td colspan="{{ ($canEdit || $canDelete) ? 8 : 7 }}" class="text-center text-secondary py-4">
                                            No leave types match your filter/search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('[data-leave-filter]');
            const sortButtons = document.querySelectorAll('[data-sort-column]');
            const searchInput = document.getElementById('leaveTypeSearch');
            const tbody = document.querySelector('#leaveTypesTable tbody');
            const emptyState = document.getElementById('leaveTypeEmptyState');

            let activeFilter = 'all';
            let activeSortColumn = null;
            let activeSortDirection = 'asc';

            function getRows() {
                return Array.from(document.querySelectorAll('.leave-type-row'));
            }

            function applyFilters() {
                const searchValue = (searchInput.value || '').toLowerCase().trim();
                let visibleCount = 0;

                getRows().forEach(function (row) {
                    const status = row.getAttribute('data-status');
                    const type = row.getAttribute('data-type');
                    const rowSearch = row.getAttribute('data-search') || '';

                    let filterMatch = true;

                    if (activeFilter === 'active' || activeFilter === 'inactive') {
                        filterMatch = status === activeFilter;
                    }

                    if (activeFilter === 'paid' || activeFilter === 'unpaid') {
                        filterMatch = type === activeFilter;
                    }

                    const searchMatch = searchValue === '' || rowSearch.includes(searchValue);

                    if (filterMatch && searchMatch) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? '' : 'none';
                }
            }

            function applySorting(column) {
                if (activeSortColumn === column) {
                    activeSortDirection = activeSortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    activeSortColumn = column;
                    activeSortDirection = 'asc';
                }

                const rows = getRows();

                rows.sort(function (a, b) {
                    let valueA = a.getAttribute('data-' + column) || '';
                    let valueB = b.getAttribute('data-' + column) || '';

                    if (column === 'id') {
                        valueA = parseInt(valueA, 10) || 0;
                        valueB = parseInt(valueB, 10) || 0;
                    }

                    if (valueA < valueB) {
                        return activeSortDirection === 'asc' ? -1 : 1;
                    }

                    if (valueA > valueB) {
                        return activeSortDirection === 'asc' ? 1 : -1;
                    }

                    return 0;
                });

                rows.forEach(function (row) {
                    tbody.insertBefore(row, emptyState);
                });

                updateSortIcons();
                applyFilters();
            }

            function updateSortIcons() {
                sortButtons.forEach(function (button) {
                    const icon = button.querySelector('.leave-type-sort-icon');
                    const column = button.getAttribute('data-sort-column');

                    button.classList.remove('text-primary');

                    if (!icon) {
                        return;
                    }

                    icon.classList.remove('active', 'asc', 'desc');

                    if (column === activeSortColumn) {
                        button.classList.add('text-primary');
                        icon.classList.add('active', activeSortDirection);
                    }
                });
            }

            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    filterButtons.forEach(function (btn) {
                        btn.classList.remove('active');
                    });

                    this.classList.add('active');
                    activeFilter = this.getAttribute('data-leave-filter');

                    applyFilters();
                });
            });

            sortButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applySorting(this.getAttribute('data-sort-column'));
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }

            /*
            |--------------------------------------------------------------------------
            | Leave Type Delete SweetAlert Confirmation
            |--------------------------------------------------------------------------
            | Uses a real hidden form and a type=button delete button. This prevents
            | direct delete and ensures confirmation appears before form submit.
            */
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.js-leave-type-delete-confirm');

                if (!button) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const formId = button.getAttribute('data-form-id');
                const name = button.getAttribute('data-name') || 'this leave type';
                const form = formId ? document.getElementById(formId) : null;

                if (!form) {
                    return;
                }

                const submitDelete = function () {
                    form.submit();
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Delete "' + name + '"?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger rounded-3 px-4 ms-2',
                            cancelButton: 'btn btn-light rounded-3 px-4'
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            submitDelete();
                        }
                    });

                    return;
                }

                const fallbackOverlay = document.createElement('div');
                fallbackOverlay.style.position = 'fixed';
                fallbackOverlay.style.inset = '0';
                fallbackOverlay.style.background = 'rgba(15, 23, 42, .55)';
                fallbackOverlay.style.zIndex = '99999';
                fallbackOverlay.style.display = 'flex';
                fallbackOverlay.style.alignItems = 'center';
                fallbackOverlay.style.justifyContent = 'center';
                fallbackOverlay.innerHTML = `
                    <div style="background:#fff;border-radius:18px;max-width:430px;width:92%;padding:24px;box-shadow:0 20px 60px rgba(15,23,42,.25);">
                        <h5 style="margin:0 0 8px;font-weight:800;color:#0f172a;">Are you sure?</h5>
                        <p style="margin:0 0 20px;color:#64748b;">Delete &quot;${name}&quot;?</p>
                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-cancel-delete>Cancel</button>
                            <button type="button" class="btn btn-danger rounded-3 px-4" data-confirm-delete>Yes, delete</button>
                        </div>
                    </div>
                `;

                document.body.appendChild(fallbackOverlay);

                fallbackOverlay.querySelector('[data-cancel-delete]').addEventListener('click', function () {
                    fallbackOverlay.remove();
                });

                fallbackOverlay.querySelector('[data-confirm-delete]').addEventListener('click', function () {
                    fallbackOverlay.remove();
                    submitDelete();
                });
            });

            @if(session('success'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Done',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-3 px-4'
                        }
                    });
                }
            @endif

            applyFilters();
        });
    </script>
</x-app-layout>