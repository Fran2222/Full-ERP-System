<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <style>
            .evaluation-page-card {
                border: 0;
                border-radius: 18px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            }

            .evaluation-header-icon {
                width: 28px;
                height: 28px;
                color: #0ea5b7;
                flex-shrink: 0;
            }

            .evaluation-header-left {
                flex: 1;
            }

            .evaluation-header-actions {
                min-width: 280px;
            }

            .evaluation-total-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 32px;
                padding: 7px 16px;
                border-radius: 999px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 13px;
                font-weight: 700;
            }

            .evaluation-status-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 12px;
            }

            .evaluation-status-tab {
                border: 1px solid #dbe3f0;
                background: #ffffff;
                color: #334155;
                border-radius: 999px;
                padding: 6px 12px;
                font-size: 13px;
                font-weight: 600;
                min-height: 34px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease-in-out;
                cursor: pointer;
            }

            .evaluation-status-tab:hover {
                border-color: #3b5bdb;
                color: #3b5bdb;
            }

            .evaluation-status-tab.active {
                background: #3b5bdb;
                border-color: #3b5bdb;
                color: #ffffff;
                box-shadow: 0 5px 12px rgba(59, 91, 219, 0.16);
            }

            .evaluation-status-tab-count {
                min-width: 19px;
                height: 19px;
                padding: 0 6px;
                border-radius: 999px;
                background: #eef2ff;
                color: #3b5bdb;
                font-size: 11px;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }

            .evaluation-status-tab.active .evaluation-status-tab-count {
                background: rgba(255, 255, 255, 0.18);
                color: #ffffff;
            }

            .evaluation-search-box {
                position: relative;
                margin-top: 10px;
            }

            .evaluation-search-box input {
                width: 100%;
                min-height: 38px;
                border: 1px solid #dbe3f0;
                border-radius: 10px;
                padding: 8px 38px 8px 14px;
                font-size: 14px;
                color: #475569;
                outline: none;
                transition: all 0.2s ease-in-out;
            }

            .evaluation-search-box input:focus {
                border-color: #3b5bdb;
                box-shadow: 0 0 0 0.15rem rgba(59, 91, 219, 0.12);
            }

            .evaluation-search-box i {
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94a3b8;
                font-size: 14px;
            }

            .evaluation-form-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
            }

            .evaluation-form-card {
                position: relative;
                background: #ffffff;
                border: 1px solid #dbe3f0;
                border-radius: 16px;
                padding: 22px 20px;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
                min-height: 220px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: all 0.2s ease-in-out;
                cursor: pointer;
                overflow: hidden;
            }

            .evaluation-form-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
                border-color: #0ea5b7;
            }

            .evaluation-hover-preview {
                position: absolute;
                top: 14px;
                right: 14px;
                background: #0ea5b7;
                color: #ffffff;
                font-size: 11px;
                font-weight: 700;
                padding: 5px 9px;
                border-radius: 999px;
                opacity: 0;
                transform: translateY(-4px);
                transition: all 0.2s ease-in-out;
                pointer-events: none;
                z-index: 5;
            }

            .evaluation-form-card:hover .evaluation-hover-preview {
                opacity: 1;
                transform: translateY(0);
            }

            .evaluation-form-title {
                font-size: 16px;
                font-weight: 700;
                color: #071437;
                margin-bottom: 8px;
            }

            .evaluation-form-meta {
                font-size: 12px;
                color: #64748b;
                margin-bottom: 14px;
            }

            .evaluation-form-instruction {
                font-size: 13px;
                color: #52637a;
                margin-bottom: 18px;
                min-height: 20px;
            }

            .evaluation-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 4px 8px;
                border-radius: 5px;
                font-size: 11px;
                font-weight: 700;
                line-height: 1;
                color: #ffffff;
                margin-right: 5px;
                margin-bottom: 5px;
            }

            .evaluation-badge-question {
                background: #0ea5b7;
            }

            .evaluation-badge-section {
                background: #64748b;
            }

            .evaluation-badge-task {
                background: #3b82f6;
            }

            .evaluation-badge-status-active {
                background: #16a34a;
            }

            .evaluation-badge-status-draft {
                background: #f59e0b;
            }

            .evaluation-badge-status-archived {
                background: #6b7280;
            }

            .evaluation-card-actions {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 22px;
                position: relative;
                z-index: 10;
            }

            .evaluation-action-form {
                display: inline-flex;
                margin: 0;
            }

            .evaluation-card-action-btn {
                width: 34px !important;
                height: 34px !important;
                min-width: 34px !important;
                padding: 0 !important;
                border-radius: 10px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                line-height: 1 !important;
                text-decoration: none !important;
                border: 1px solid transparent;
                cursor: pointer;
                box-shadow: none !important;
                transition: all 0.2s ease-in-out;
            }

            .evaluation-card-action-btn:hover {
                transform: translateY(-1px);
                text-decoration: none !important;
            }

            .evaluation-card-action-btn svg {
                width: 17px !important;
                height: 17px !important;
                display: block !important;
                flex-shrink: 0 !important;
            }

            .evaluation-card-action-btn svg path,
            .evaluation-card-action-btn svg rect,
            .evaluation-card-action-btn svg circle {
                stroke: #ffffff !important;
            }

            .evaluation-action-edit {
                background: #315cf6 !important;
                border-color: #315cf6 !important;
                color: #ffffff !important;
            }

            .evaluation-action-duplicate {
                background: #64748b !important;
                border-color: #64748b !important;
                color: #ffffff !important;
            }

            .evaluation-action-assign {
                background: #0ea5e9 !important;
                border-color: #0ea5e9 !important;
                color: #ffffff !important;
            }

            .evaluation-action-delete {
                background: #dc2626 !important;
                border-color: #dc2626 !important;
                color: #ffffff !important;
            }

            .evaluation-action-edit:hover,
            .evaluation-action-edit:focus {
                background: #244be0 !important;
                border-color: #244be0 !important;
                color: #ffffff !important;
            }

            .evaluation-action-duplicate:hover,
            .evaluation-action-duplicate:focus {
                background: #475569 !important;
                border-color: #475569 !important;
                color: #ffffff !important;
            }

            .evaluation-action-assign:hover,
            .evaluation-action-assign:focus {
                background: #0284c7 !important;
                border-color: #0284c7 !important;
                color: #ffffff !important;
            }

            .evaluation-action-delete:hover,
            .evaluation-action-delete:focus {
                background: #b91c1c !important;
                border-color: #b91c1c !important;
                color: #ffffff !important;
            }

            .evaluation-empty-state {
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 50px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
            }

            .evaluation-no-search-result {
                display: none;
                border: 1px dashed #cbd5e1;
                border-radius: 16px;
                padding: 35px 20px;
                text-align: center;
                background: #f8fafc;
                color: #64748b;
                margin-top: 18px;
            }

            .swal2-popup {
                border-radius: 18px !important;
            }

            .swal2-title {
                color: #071437 !important;
            }

            .swal2-html-container {
                color: #64748b !important;
            }

            @media (max-width: 1199.98px) {
                .evaluation-form-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767.98px) {
                .evaluation-form-grid {
                    grid-template-columns: 1fr;
                }

                .evaluation-form-card {
                    min-height: auto;
                }

                .evaluation-header-actions {
                    width: 100%;
                    min-width: 100%;
                }

                .evaluation-header-actions .d-flex {
                    justify-content: flex-start !important;
                }
            }

            @media (max-width: 575.98px) {
                .evaluation-card-actions {
                    gap: 6px;
                }

                .evaluation-status-tabs {
                    gap: 8px;
                }

                .evaluation-status-tab {
                    font-size: 12px;
                    padding: 5px 10px;
                    min-height: 32px;
                }
            }
        </style>

        <div class="card evaluation-page-card">
            <div class="card-header border-0 bg-white d-flex justify-content-between align-items-start flex-wrap gap-3 pb-0">
                <div class="evaluation-header-left">
                    <div class="d-flex align-items-center gap-2">
                        <svg class="evaluation-header-icon"
                             xmlns="http://www.w3.org/2000/svg"
                             width="28"
                             height="28"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M8 13h8"/>
                            <path d="M8 17h8"/>
                            <path d="M8 9h2"/>
                        </svg>

                        <h4 class="card-title mb-0">Available Forms</h4>
                    </div>

                    <div class="mt-3">
                        <span class="evaluation-total-badge">
                            {{ $forms->total() }} Saved Forms
                        </span>
                    </div>

                    @php
                        $formsCollection = method_exists($forms, 'getCollection') ? $forms->getCollection() : collect($forms);
                        $activeCount = $formsCollection->where('status', 'active')->count();
                        $archivedCount = $formsCollection->where('status', 'archived')->count();
                        $draftCount = $formsCollection->where('status', 'draft')->count();
                    @endphp

                    <div class="evaluation-status-tabs" id="evaluationStatusTabs">
                        <button type="button" class="evaluation-status-tab active" data-status-filter="all">
                            <span>All</span>
                            <span class="evaluation-status-tab-count">{{ $formsCollection->count() }}</span>
                        </button>

                        <button type="button" class="evaluation-status-tab" data-status-filter="active">
                            <span>Active</span>
                            <span class="evaluation-status-tab-count">{{ $activeCount }}</span>
                        </button>

                        <button type="button" class="evaluation-status-tab" data-status-filter="archived">
                            <span>Archived</span>
                            <span class="evaluation-status-tab-count">{{ $archivedCount }}</span>
                        </button>

                        <button type="button" class="evaluation-status-tab" data-status-filter="draft">
                            <span>Draft</span>
                            <span class="evaluation-status-tab-count">{{ $draftCount }}</span>
                        </button>
                    </div>
                </div>

                <div class="evaluation-header-actions">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        @can('hr.evaluation.create')
                            <a href="{{ route('hr.evaluation.forms.create') }}" class="btn btn-primary btn-sm rounded-3">
                                <i class="fas fa-plus me-1"></i> Create Form
                            </a>
                        @endcan
                    </div>

                    <div class="evaluation-search-box">
                        <input type="text"
                               id="evaluationFormSearch"
                               placeholder="Search form title...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if($forms->count())
                    <div class="evaluation-form-grid" id="evaluationFormGrid">
                        @foreach($forms as $form)
                            @php
                                $status = strtolower($form->status ?? 'draft');

                                $statusClass = match ($status) {
                                    'active' => 'evaluation-badge-status-active',
                                    'archived' => 'evaluation-badge-status-archived',
                                    default => 'evaluation-badge-status-draft',
                                };
                            @endphp

                            <div class="evaluation-form-card"
                                 data-title="{{ strtolower($form->title) }}"
                                 data-status="{{ $status }}"
                                 data-preview-url="{{ route('hr.evaluation.forms.show', $form->id) }}"
                                 title="Click to preview this form">

                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <h5 class="evaluation-form-title">
                                                {{ $form->title }}
                                            </h5>

                                            <div class="evaluation-form-meta">
                                                Saved on {{ optional($form->created_at)->format('M d, Y h:i A') }}
                                            </div>
                                        </div>

                                        <span class="evaluation-badge {{ $statusClass }}">
                                            {{ strtoupper($form->status) }}
                                        </span>
                                    </div>

                                    <p class="evaluation-form-instruction">
                                        {{ $form->instructions ? \Illuminate\Support\Str::limit($form->instructions, 120) : 'No instructions' }}
                                    </p>

                                    <div>
                                        <span class="evaluation-badge evaluation-badge-question">
                                            {{ $form->questions_count ?? 0 }} Questions
                                        </span>

                                        <span class="evaluation-badge evaluation-badge-section">
                                            {{ $form->sections_count ?? 0 }} Sections
                                        </span>

                                        <span class="evaluation-badge evaluation-badge-task">
                                            {{ $form->tasks_count ?? 0 }} Assigned
                                        </span>
                                    </div>
                                </div>

                                <div class="evaluation-card-actions">
                                    @can('hr.evaluation.edit')
                                        <a href="{{ route('hr.evaluation.forms.edit', $form->id) }}"
                                           class="evaluation-card-action-btn evaluation-action-edit"
                                           title="Edit"
                                           aria-label="Edit form">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M13.747 3.41095L20.589 10.2529L7.84302 23H1.00098V16.157L13.747 3.41095Z"
                                                      stroke="currentColor"
                                                      stroke-width="1.7"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('hr.evaluation.create')
                                        <form action="{{ route('hr.evaluation.forms.duplicate', $form->id) }}"
                                              method="POST"
                                              class="evaluation-action-form duplicate-evaluation-form">
                                            @csrf

                                            <button type="submit"
                                                    class="evaluation-card-action-btn evaluation-action-duplicate"
                                                    title="Duplicate"
                                                    aria-label="Duplicate form"
                                                    data-form-title="{{ $form->title }}">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <rect x="8" y="8" width="11" height="11" rx="2"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"/>
                                                    <path d="M5 16H4C2.9 16 2 15.1 2 14V5C2 3.9 2.9 3 4 3H13C14.1 3 15 3.9 15 5V6"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <a href="{{ route('hr.evaluation.forms.assign', $form->id) }}"
                                           class="evaluation-card-action-btn evaluation-action-assign"
                                           title="Assign Form"
                                           aria-label="Assign form">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M17 21V19C17 16.8 15.2 15 13 15H6C3.8 15 2 16.8 2 19V21"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                                <path d="M9.5 11C11.7 11 13.5 9.2 13.5 7C13.5 4.8 11.7 3 9.5 3C7.3 3 5.5 4.8 5.5 7C5.5 9.2 7.3 11 9.5 11Z"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                                <path d="M18 8V14"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"/>
                                                <path d="M15 11H21"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @can('hr.evaluation.delete')
                                        <form action="{{ route('hr.evaluation.forms.destroy', $form->id) }}"
                                              method="POST"
                                              class="evaluation-action-form delete-evaluation-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="evaluation-card-action-btn evaluation-action-delete"
                                                    title="Delete"
                                                    aria-label="Delete form"
                                                    data-form-title="{{ $form->title }}">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M3 6H5H21"
                                                          stroke="currentColor"
                                                          stroke-width="1.7"
                                                          stroke-linecap="round"/>
                                                    <path d="M19 6L18.2 19C18.1 20.1 17.2 21 16.1 21H7.9C6.8 21 5.9 20.1 5.8 19L5 6"
                                                          stroke="currentColor"
                                                          stroke-width="1.7"
                                                          stroke-linecap="round"/>
                                                    <path d="M10 11V17"
                                                          stroke="currentColor"
                                                          stroke-width="1.7"
                                                          stroke-linecap="round"/>
                                                    <path d="M14 11V17"
                                                          stroke="currentColor"
                                                          stroke-width="1.7"
                                                          stroke-linecap="round"/>
                                                    <path d="M9 6V4C9 3.4 9.4 3 10 3H14C14.6 3 15 3.4 15 4V6"
                                                          stroke="currentColor"
                                                          stroke-width="1.7"
                                                          stroke-linecap="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="evaluation-no-search-result" id="evaluationNoSearchResult">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <h6 class="mb-1">No forms found.</h6>
                        <p class="mb-0">Try changing the search or selected status.</p>
                    </div>

                    <div class="mt-4" id="evaluationPagination">
                        {{ $forms->links() }}
                    </div>
                @else
                    <div class="evaluation-empty-state">
                        <i class="fas fa-folder-open fa-2x mb-3"></i>
                        <h5 class="mb-1">No evaluation forms found.</h5>
                        <p class="mb-3">Create your first evaluation form to start assigning evaluations.</p>

                        @can('hr.evaluation.create')
                            <a href="{{ route('hr.evaluation.forms.create') }}" class="btn btn-primary btn-sm rounded-3">
                                <i class="fas fa-plus me-1"></i> Create Form
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Done',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3b5bdb'
                    });
                });
            </script>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let currentStatusFilter = 'all';

                const swalDefault = {
                    confirmButtonColor: '#3b5bdb',
                    customClass: {
                        popup: 'rounded-4'
                    }
                };

                function showDone(message) {
                    return Swal.fire({
                        ...swalDefault,
                        title: 'Done',
                        text: message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }

                function showError(message) {
                    return Swal.fire({
                        ...swalDefault,
                        title: 'Error',
                        text: message || 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }

                function initEvaluationFormsPage() {
                    const formCards = document.querySelectorAll('.evaluation-form-card');
                    const searchInput = document.getElementById('evaluationFormSearch');
                    const noResult = document.getElementById('evaluationNoSearchResult');
                    const pagination = document.getElementById('evaluationPagination');
                    const statusTabs = document.querySelectorAll('.evaluation-status-tab');

                    function applyFilters() {
                        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
                        let visibleCount = 0;

                        document.querySelectorAll('.evaluation-form-card').forEach(function (card) {
                            const title = card.getAttribute('data-title') || '';
                            const status = card.getAttribute('data-status') || '';
                            const matchesSearch = title.includes(keyword);
                            const matchesStatus = currentStatusFilter === 'all' || status === currentStatusFilter;
                            const isVisible = matchesSearch && matchesStatus;

                            card.style.display = isVisible ? '' : 'none';

                            if (isVisible) {
                                visibleCount++;
                            }
                        });

                        if (noResult) {
                            noResult.style.display = visibleCount === 0 ? 'block' : 'none';
                        }

                        if (pagination) {
                            pagination.style.display = (keyword || currentStatusFilter !== 'all') ? 'none' : '';
                        }
                    }

                    formCards.forEach(function (card) {
                        if (card.dataset.evaluationCardReady === '1') {
                            return;
                        }

                        card.dataset.evaluationCardReady = '1';

                        card.addEventListener('click', function (event) {
                            const clickedAction = event.target.closest('a, button, form, input, select, textarea, label');

                            if (clickedAction) {
                                return;
                            }

                            const previewUrl = card.getAttribute('data-preview-url');

                            if (previewUrl) {
                                window.location.href = previewUrl;
                            }
                        });
                    });

                    if (searchInput && searchInput.dataset.evaluationSearchReady !== '1') {
                        searchInput.dataset.evaluationSearchReady = '1';

                        searchInput.addEventListener('input', function () {
                            applyFilters();
                        });
                    }

                    statusTabs.forEach(function (tab) {
                        if (tab.dataset.evaluationStatusReady === '1') {
                            return;
                        }

                        tab.dataset.evaluationStatusReady = '1';

                        tab.addEventListener('click', function () {
                            statusTabs.forEach(function (otherTab) {
                                otherTab.classList.remove('active');
                            });

                            this.classList.add('active');
                            currentStatusFilter = this.getAttribute('data-status-filter') || 'all';
                            applyFilters();
                        });
                    });

                    setupDuplicateForms();
                    setupDeleteForms();
                    applyFilters();
                }

                function refreshEvaluationFormsGrid() {
                    fetch(window.location.href, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                        .then(function (response) {
                            return response.text();
                        })
                        .then(function (html) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            const currentGrid = document.getElementById('evaluationFormGrid');
                            const newGrid = doc.getElementById('evaluationFormGrid');

                            const currentNoResult = document.getElementById('evaluationNoSearchResult');
                            const newNoResult = doc.getElementById('evaluationNoSearchResult');

                            const currentPagination = document.getElementById('evaluationPagination');
                            const newPagination = doc.getElementById('evaluationPagination');

                            if (currentGrid && newGrid) {
                                currentGrid.innerHTML = newGrid.innerHTML;
                            }

                            if (currentNoResult && newNoResult) {
                                currentNoResult.innerHTML = newNoResult.innerHTML;
                            }

                            if (currentPagination && newPagination) {
                                currentPagination.innerHTML = newPagination.innerHTML;
                            }

                            initEvaluationFormsPage();
                        })
                        .catch(function () {
                            // No visible preload or blocking alert. User can still manually refresh if needed.
                        });
                }

                function submitEvaluationAction(form) {
                    const formData = new FormData(form);
                    const button = form.querySelector('button[type="submit"]');

                    if (button) {
                        button.disabled = true;
                    }

                    return fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                if (!response.ok) {
                                    throw data;
                                }

                                return data;
                            });
                        })
                        .finally(function () {
                            if (button) {
                                button.disabled = false;
                            }
                        });
                }

                function setupDuplicateForms() {
                    document.querySelectorAll('.duplicate-evaluation-form').forEach(function (form) {
                        if (form.dataset.evaluationDuplicateReady === '1') {
                            return;
                        }

                        form.dataset.evaluationDuplicateReady = '1';

                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            submitEvaluationAction(form)
                                .then(function (data) {
                                    showDone(data.message || 'Evaluation form duplicated successfully.');
                                    refreshEvaluationFormsGrid();
                                })
                                .catch(function (error) {
                                    showError(error.message);
                                });
                        });
                    });
                }

                function setupDeleteForms() {
                    document.querySelectorAll('.delete-evaluation-form').forEach(function (form) {
                        if (form.dataset.evaluationDeleteReady === '1') {
                            return;
                        }

                        form.dataset.evaluationDeleteReady = '1';

                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            const deleteButton = form.querySelector('button[type="submit"]');
                            const formTitle = deleteButton ? deleteButton.getAttribute('data-form-title') : 'this form';

                            Swal.fire({
                                title: 'Are you sure?',
                                text: 'This will delete "' + formTitle + '". If this form has assigned tasks, it will be archived instead.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Delete',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#3b5bdb',
                                cancelButtonColor: '#d9dee6',
                                reverseButtons: true,
                                customClass: {
                                    popup: 'rounded-4'
                                }
                            }).then(function (result) {
                                if (!result.isConfirmed) {
                                    return;
                                }

                                submitEvaluationAction(form)
                                    .then(function (data) {
                                        showDone(data.message || 'Evaluation form deleted successfully.');
                                        refreshEvaluationFormsGrid();
                                    })
                                    .catch(function (error) {
                                        showError(error.message);
                                    });
                            });
                        });
                    });
                }

                initEvaluationFormsPage();
            });
        </script>
    </div>
</x-app-layout>