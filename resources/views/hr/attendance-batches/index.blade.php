<x-app-layout>
    <style>

        /* Disable global Hope UI loader for this realtime filter page only.
           Do not use MutationObserver here because it can cause endless loading loops. */
        html.attendance-batches-no-preload #loading,
        html.attendance-batches-no-preload #loading-center,
        html.attendance-batches-no-preload .preloader,
        html.attendance-batches-no-preload .loading,
        html.attendance-batches-no-preload .loading-overlay,
        html.attendance-batches-no-preload .loader,
        html.attendance-batches-no-preload .loader-wrapper,
        html.attendance-batches-no-preload .page-loader,
        html.attendance-batches-no-preload .iq-loader,
        html.attendance-batches-no-preload .iq-loader-box,
        html.attendance-batches-no-preload .iq-preloader,
        html.attendance-batches-no-preload .pace,
        body.attendance-batches-no-preload #loading,
        body.attendance-batches-no-preload #loading-center,
        body.attendance-batches-no-preload .preloader,
        body.attendance-batches-no-preload .loading,
        body.attendance-batches-no-preload .loading-overlay,
        body.attendance-batches-no-preload .loader,
        body.attendance-batches-no-preload .loader-wrapper,
        body.attendance-batches-no-preload .page-loader,
        body.attendance-batches-no-preload .iq-loader,
        body.attendance-batches-no-preload .iq-loader-box,
        body.attendance-batches-no-preload .iq-preloader,
        body.attendance-batches-no-preload .pace {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        .attendance-batch-card { border: 0; border-radius: 20px; box-shadow: 0 14px 38px rgba(15,23,42,.07); }
        .attendance-batch-title-icon { width: 44px; height: 44px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: #3f5be8; }
        .attendance-batch-card .card-body { padding: 1.15rem !important; }

        /* WMC ATTENDANCE BATCH 125% FIT FIX
           Keep all 11 columns visible in one page at 125% browser scale.
           The table remains justified because <colgroup> and fixed layout share the same width map. */
        .attendance-batch-table-wrap {
            width: 100%;
            overflow-x: hidden;
            overflow-y: hidden;
        }
        .attendance-batch-table {
            width: 100%;
            min-width: 0 !important;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .attendance-batch-table th,
        .attendance-batch-table td {
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
            line-height: 1.15;
        }
        .attendance-batch-table th {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #edf2f7;
            padding: .48rem .24rem;
            font-weight: 800;
            text-align: left;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            line-height: 1.15;
        }
        .attendance-batch-table th .batch-head-wrap {
            display: inline-block;
            line-height: 1.15;
            white-space: normal;
        }
        .attendance-batch-table td {
            border-color: #f1f5f9;
            font-size: 11px;
            padding: .58rem .28rem;
        }
        .attendance-batch-table th.batch-col-center,
        .attendance-batch-table td.batch-col-center {
            text-align: center !important;
        }
        .attendance-batch-table th.batch-col-actions,
        .attendance-batch-table td.batch-col-actions {
            text-align: center !important;
            overflow: visible;
        }
        .attendance-batch-status { border-radius: 999px; padding: 4px 7px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 62px; max-width: 100%; }
        .attendance-batch-status.draft { background: #f1f5f9; color: #475569; }
        .attendance-batch-status.submitted { background: #fef3c7; color: #92400e; }
        .attendance-batch-status.approved { background: #dcfce7; color: #166534; }
        .attendance-batch-status.posted { background: #dbeafe; color: #1d4ed8; }
        .attendance-batch-period { border-radius: 8px; padding: 4px 7px; background: #eef2ff; color: #1d4ed8; font-weight: 700; font-size: 10px; display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; }
        .attendance-batch-action-btn { width: 24px; height: 24px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size: 10px; }
        .attendance-batch-action-btn svg { width: 13px; height: 13px; display: block; stroke: currentColor; }
        .attendance-batch-table .text-secondary { font-size: 10px; }
        .attendance-batch-filter-row { display: grid; grid-template-columns: minmax(120px, .9fr) minmax(135px, .9fr) minmax(135px, .9fr) minmax(135px, .9fr) minmax(260px, 1.35fr); gap: 12px; align-items: end; }
        .attendance-batch-autofilter-note { font-size: 11px; color: #64748b; margin-top: .35rem; }
        @media (max-width: 1399.98px) { .attendance-batch-filter-row { grid-template-columns: repeat(4, minmax(130px, 1fr)); } .attendance-batch-search-field { grid-column: 1 / -1; } }
        @media (max-width: 991.98px) { .attendance-batch-filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } .attendance-batch-search-field { grid-column: auto; } }
        @media (max-width: 575.98px) { .attendance-batch-filter-row { grid-template-columns: 1fr; } }
    </style>


    <script>
        (function () {
            document.documentElement.classList.add('attendance-batches-no-preload');
            if (document.body) {
                document.body.classList.add('attendance-batches-no-preload');
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    document.body.classList.add('attendance-batches-no-preload');
                });
            }
        })();
    </script>

    <div class="container-fluid content-inner py-0">
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

        <div class="card attendance-batch-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="attendance-batch-title-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"></path><path d="M4 15h16"></path><path d="M4 11h16"></path><path d="M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">Attendance Batches</h3>
                            <p class="mb-0 text-secondary">Manage attendance cut-off periods before reports and payroll posting.</p>
                        </div>
                    </div>
                    <a href="{{ route('hr.attendance-batches.create', request()->query()) }}" class="btn btn-primary px-4 attendance-batch-create-btn">
                        <i class="fas fa-plus me-1"></i> Create New Batch
                    </a>
                </div>

                <form method="GET" action="{{ route('hr.attendance-batches.index') }}" id="attendanceBatchAutoFilterForm" class="attendance-batch-filter-row mb-4">
                    <div class="attendance-batch-filter-field">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            <option value="all" {{ $selectedBranchId === 'all' ? 'selected' : '' }}>Company-wide Only</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="attendance-batch-filter-field">
                        <label class="form-label fw-semibold">Month</label>
                        <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
                    </div>
                    <div class="attendance-batch-filter-field">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ $selectedStatus === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="posted" {{ $selectedStatus === 'posted' ? 'selected' : '' }}>Posted</option>
                        </select>
                    </div>
                    <div class="attendance-batch-filter-field">
                        <label class="form-label fw-semibold">Period Type</label>
                        <select name="cutoff_period" class="form-select">
                            <option value="">All</option>
                            <option value="first_half" {{ $selectedPeriod === 'first_half' ? 'selected' : '' }}>1st Half</option>
                            <option value="second_half" {{ $selectedPeriod === 'second_half' ? 'selected' : '' }}>2nd Half</option>
                        </select>
                    </div>
                    <div class="attendance-batch-search-field">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search batch no. or remarks...">
                    </div>
                </form>

                <div class="table-responsive attendance-batch-table-wrap">
                    <table class="table attendance-batch-table align-middle mb-0">
                        <colgroup>
                            <col style="width: 9%;">
                            <col style="width: 11%;">
                            <col style="width: 8%;">
                            <col style="width: 9%;">
                            <col style="width: 9%;">
                            <col style="width: 9%;">
                            <col style="width: 8%;">
                            <col style="width: 8%;">
                            <col style="width: 10%;">
                            <col style="width: 9%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><span class="batch-head-wrap">Batch<br>No.</span></th>
                                <th><span class="batch-head-wrap">Branch</span></th>
                                <th><span class="batch-head-wrap">Month</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Period<br>Type</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Cut-off<br>Start</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Cut-off<br>End</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Total<br>Employees</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Status</span></th>
                                <th><span class="batch-head-wrap">Prepared<br>By</span></th>
                                <th class="batch-col-center"><span class="batch-head-wrap">Date<br>Created</span></th>
                                <th class="batch-col-actions"><span class="batch-head-wrap">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr class="attendance-batch-row"
                                    data-branch-id="{{ $batch->branch_id ?? 'all' }}"
                                    data-month="{{ $batch->month }}"
                                    data-status="{{ $batch->status }}"
                                    data-cutoff-period="{{ $batch->cutoff_period }}"
                                    data-search="{{ strtolower(($batch->batch_no ?? '') . ' ' . ($batch->remarks ?? '')) }}">
                                    <td class="fw-bold">{{ $batch->batch_no }}</td>
                                    <td title="{{ $batch->branch?->name ?? 'All Branches' }}">{{ $batch->branch?->name ?? 'All Branches' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($batch->month . '-01')->format('F Y') }}</td>
                                    <td class="batch-col-center"><span class="attendance-batch-period">{{ $batch->cutoff_period_label }}</span></td>
                                    <td class="batch-col-center">{{ $batch->cutoff_start?->format('M j, Y') }}</td>
                                    <td class="batch-col-center">{{ $batch->cutoff_end?->format('M j, Y') }}</td>
                                    <td class="batch-col-center fw-semibold">{{ number_format($batch->total_employees) }}</td>
                                    <td class="batch-col-center"><span class="attendance-batch-status {{ $batch->status }}">{{ $batch->status_label }}</span></td>
                                    @php($preparedName = trim(($batch->preparedBy->first_name ?? '') . ' ' . ($batch->preparedBy->last_name ?? '')) ?: ($batch->preparedBy->name ?? '—'))
                                    <td title="{{ $preparedName }}">{{ $preparedName }}</td>
                                    <td class="batch-col-center"><span title="{{ $batch->created_at?->format('M j, Y h:i A') }}">{{ $batch->created_at?->format('M j, Y') }}</span><br><small class="text-secondary">{{ $batch->created_at?->format('h:i A') }}</small></td>
                                    <td class="batch-col-actions">
                                        <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                            <a href="{{ route('hr.attendance.index', ['month' => $batch->month, 'cutoff_period' => $batch->cutoff_period, 'branch_id' => $batch->branch_id]) }}" class="btn btn-sm btn-outline-primary attendance-batch-action-btn" title="Open Encoding" aria-label="Open Encoding">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 5h18"></path>
                                                    <path d="M3 12h18"></path>
                                                    <path d="M3 19h18"></path>
                                                    <path d="M8 5v14"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('hr.attendance-batches.create', ['month' => $batch->month, 'cutoff_period' => $batch->cutoff_period, 'branch_id' => $batch->branch_id]) }}" class="btn btn-sm btn-outline-secondary attendance-batch-action-btn" title="Create Similar Batch" aria-label="Create Similar Batch">
                                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <rect x="8" y="8" width="11" height="11" rx="2"></rect>
                                                    <path d="M5 16V7a2 2 0 0 1 2-2h9"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="attendanceBatchNoRecordsRow">
                                    <td colspan="11" class="text-center text-secondary py-4">No attendance batches found.</td>
                                </tr>
                            @endforelse

                            <tr id="attendanceBatchFilterEmptyRow" class="d-none">
                                <td colspan="11" class="text-center text-secondary py-4">No attendance batches matched your filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $batches->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.documentElement.classList.add('attendance-batches-no-preload');
            document.body.classList.add('attendance-batches-no-preload');

            function hideGlobalLoaderOnce() {
                document.documentElement.classList.add('attendance-batches-no-preload');
                document.body.classList.add('attendance-batches-no-preload');

                [
                    '#loading', '#loading-center', '.preloader', '.loading', '.loading-overlay',
                    '.loader', '.loader-wrapper', '.page-loader', '.iq-loader', '.iq-loader-box',
                    '.iq-preloader', '.pace'
                ].forEach(function (selector) {
                    document.querySelectorAll(selector).forEach(function (loader) {
                        loader.style.setProperty('display', 'none', 'important');
                        loader.style.setProperty('opacity', '0', 'important');
                        loader.style.setProperty('visibility', 'hidden', 'important');
                        loader.style.setProperty('pointer-events', 'none', 'important');
                    });
                });
            }

            hideGlobalLoaderOnce();
            setTimeout(hideGlobalLoaderOnce, 80);

            const form = document.getElementById('attendanceBatchAutoFilterForm');
            if (!form) {
                return;
            }

            const fields = form.querySelectorAll('select, input[type="month"], input[name="search"]');
            const rows = Array.from(document.querySelectorAll('.attendance-batch-row'));

            fields.forEach(function (field) {
                ['click', 'mousedown', 'mouseup', 'touchstart'].forEach(function (eventName) {
                    field.addEventListener(eventName, function (event) {
                        event.stopPropagation();
                        hideGlobalLoaderOnce();
                    });
                });
            });
            const filterEmptyRow = document.getElementById('attendanceBatchFilterEmptyRow');
            let searchTimer = null;

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                applyAttendanceBatchFilters(true);
            });

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function applyAttendanceBatchFilters(updateUrl = false) {
                hideGlobalLoaderOnce();
                const selectedBranch = form.querySelector('[name="branch_id"]')?.value || '';
                const selectedMonth = form.querySelector('[name="month"]')?.value || '';
                const selectedStatus = form.querySelector('[name="status"]')?.value || '';
                const selectedPeriod = form.querySelector('[name="cutoff_period"]')?.value || '';
                const searchValue = normalize(form.querySelector('[name="search"]')?.value || '');
                let visibleCount = 0;

                rows.forEach(function (row) {
                    const branchMatch = selectedBranch === '' || row.dataset.branchId === selectedBranch;
                    const monthMatch = selectedMonth === '' || row.dataset.month === selectedMonth;
                    const statusMatch = selectedStatus === '' || row.dataset.status === selectedStatus;
                    const periodMatch = selectedPeriod === '' || row.dataset.cutoffPeriod === selectedPeriod;
                    const searchMatch = searchValue === '' || normalize(row.dataset.search).includes(searchValue);
                    const shouldShow = branchMatch && monthMatch && statusMatch && periodMatch && searchMatch;

                    row.classList.toggle('d-none', !shouldShow);

                    if (shouldShow) {
                        visibleCount++;
                    }
                });

                if (filterEmptyRow) {
                    filterEmptyRow.classList.toggle('d-none', visibleCount !== 0 || rows.length === 0);
                }

                if (updateUrl && window.history && window.history.replaceState) {
                    const params = new URLSearchParams();
                    const formData = new FormData(form);

                    formData.forEach(function (value, key) {
                        if (String(value || '').trim() !== '') {
                            params.set(key, value);
                        }
                    });

                    const queryString = params.toString();
                    const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
                    window.history.replaceState({}, '', newUrl);
                }
            }

            fields.forEach(function (field) {
                const eventName = field.matches('input[name="search"]') ? 'input' : 'change';

                field.addEventListener(eventName, function (event) {
                    event.stopPropagation();
                    hideGlobalLoaderOnce();
                    if (field.matches('input[name="search"]')) {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function () {
                            applyAttendanceBatchFilters(true);
                        }, 180);
                        return;
                    }

                    applyAttendanceBatchFilters(true);
                });
            });

            applyAttendanceBatchFilters(false);
            hideGlobalLoaderOnce();
        });
    </script>

</x-app-layout>
