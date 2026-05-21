@if(!empty($ajaxTableOnly))
    @php
        $user = auth()->user();

        $canAccess = function ($permission) use ($user) {
            return $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            );
        };

        $canViewInvoices = $canAccess('sales.invoices.view');
    @endphp

    @forelse($payments as $row)
        <tr>
            <td class="text-secondary">{{ $payments->firstItem() + $loop->index }}</td>

            <td>
                <span class="fw-semibold text-primary">{{ $row->payment_no }}</span>
            </td>

            <td>
                <div class="fw-semibold text-dark">{{ $row->customer?->customer_name ?? '-' }}</div>
                <div class="small text-secondary">{{ $row->customer?->customer_code ?? '-' }}</div>
            </td>

            <td>
                @if($row->invoice && $canViewInvoices)
                    <a href="{{ route('sales.invoices.show', $row->invoice) }}" class="fw-semibold text-primary text-decoration-none">
                        {{ $row->invoice->invoice_no }}
                    </a>
                @else
                    <span class="text-secondary">
                        {{ $row->invoice?->invoice_no ?? '-' }}
                    </span>
                @endif
            </td>

            <td class="text-secondary">{{ optional($row->payment_date)->format('M d, Y') }}</td>

            <td class="text-secondary">{{ $row->payment_method }}</td>

            <td class="text-secondary">{{ $row->reference_no ?: '-' }}</td>

            <td class="text-end fw-bold text-success">
                {{ number_format((float) $row->amount, 2) }}
            </td>

            @if($canViewInvoices)
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @if($row->invoice)
                            <a href="{{ route('sales.invoices.show', $row->invoice) }}"
                               class="wmc-action-btn wmc-action-view"
                               title="View Invoice">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @else
                            <span class="wmc-action-btn wmc-action-disabled" title="No linked invoice">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                </td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="{{ $canViewInvoices ? 9 : 8 }}" class="text-center py-5">
                <div class="text-secondary">No payments found.</div>
            </td>
        </tr>
    @endforelse
@else
    <x-app-layout>
        <div class="container-fluid content-inner mt-n5 py-0">

            @include('sales._nav')

            @php
                $user = auth()->user();

                $canAccess = function ($permission) use ($user) {
                    return $user && (
                        $user->can($permission)
                        || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                    );
                };

                abort_unless($canAccess('sales.payments.view'), 403);

                $canCreatePayment = $canAccess('sales.payments.create');
                $canViewInvoices = $canAccess('sales.invoices.view');

                $perPage = $perPage ?? (int) request('per_page', 10);
                $search = $search ?? request('search', '');
            @endphp

            <div class="card sales-panel">
                <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1 fw-bold">Receive Payments</h4>
                            <p class="text-secondary mb-0">
                                Record customer payments and apply them to unpaid invoices.
                            </p>
                        </div>

                        @if($canCreatePayment)
                            <a href="{{ route('sales.receive-payments.create') }}" class="btn btn-primary sales-soft-btn">
                                Receive Payment
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body px-4 pb-4">
                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger rounded-3 mb-4">{{ session('error') }}</div>
                    @endif

                    <div id="paymentAjaxAlert" class="alert rounded-3 mb-4 d-none"></div>

                    <form id="paymentFilterForm"
                          method="GET"
                          action="{{ route('sales.receive-payments.index') }}"
                          class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary small fw-semibold">Show</span>
                                    <select name="per_page"
                                            id="paymentPerPage"
                                            class="form-select form-select-sm sales-entries-select">
                                        @foreach([10, 25, 50, 100] as $entry)
                                            <option value="{{ $entry }}" {{ (int) $perPage === $entry ? 'selected' : '' }}>
                                                {{ $entry }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-secondary small fw-semibold">entries</span>
                                </div>
                            </div>

                            <div class="col-xl-7 col-lg-6 col-md-8">
                                <div class="input-group sales-search-group">
                                    <span class="input-group-text bg-white">
                                        <span class="text-secondary">⌕</span>
                                    </span>
                                    <input type="text"
                                           name="search"
                                           id="paymentSearchInput"
                                           value="{{ $search }}"
                                           class="form-control"
                                           autocomplete="off"
                                           placeholder="Search payment no, customer, invoice, method, or reference...">
                                    <button type="button"
                                            id="paymentSearchClear"
                                            class="btn btn-outline-secondary {{ trim((string) $search) === '' ? 'd-none' : '' }}"
                                            title="Clear search">
                                        ×
                                    </button>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-12">
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <button type="submit" class="btn btn-primary px-4 sales-soft-btn">
                                        Filter
                                    </button>
                                    <a href="{{ route('sales.receive-payments.index') }}"
                                       id="paymentResetBtn"
                                       class="btn btn-outline-secondary px-4 sales-soft-btn">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive sales-table-wrap">
                        <table class="table table-hover align-middle mb-0 sales-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Payment No.</th>
                                    <th>Customer</th>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>

                                    @if($canViewInvoices)
                                        <th class="text-end">Actions</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody id="paymentTableBody">
                                @include('sales.receive-payments.index', [
                                    'payments' => $payments,
                                    'ajaxTableOnly' => true,
                                ])
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                        <div id="paymentShowingText" class="text-secondary small">
                            @if($payments->total() > 0)
                                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                            @else
                                Showing 0 entries
                            @endif
                        </div>

                        <div id="paymentPaginationLinks">
                            {{ $payments->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('sales.invoices._styles')

        <style>
            .sales-panel {
                border-radius: 18px !important;
                border: 1px solid #edf0f5 !important;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
                overflow: hidden;
            }

            .sales-soft-btn {
                border-radius: 10px;
                padding-top: 10px;
                padding-bottom: 10px;
                font-weight: 600;
            }

            .sales-entries-select {
                width: 82px;
                border-radius: 10px;
                font-weight: 600;
            }

            .sales-search-group .form-control,
            .sales-search-group .input-group-text,
            .sales-search-group .btn {
                min-height: 42px;
            }

            .sales-search-group .input-group-text {
                border-top-left-radius: 12px;
                border-bottom-left-radius: 12px;
            }

            .sales-search-group .btn {
                border-top-right-radius: 12px;
                border-bottom-right-radius: 12px;
                font-size: 20px;
                line-height: 1;
                padding-left: 14px;
                padding-right: 14px;
            }

            .sales-table-wrap {
                border: 1px solid #edf0f5;
                border-radius: 16px;
                overflow: hidden;
                min-height: 120px;
                position: relative;
            }

            .sales-table {
                min-width: 980px;
            }

            .sales-table tbody.is-loading {
                opacity: .45;
                pointer-events: none;
            }

            .wmc-action-btn {
                width: 32px;
                height: 32px;
                padding: 0;
                border-radius: 8px;
                background: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid transparent;
                transition: all .18s ease-in-out;
                text-decoration: none;
                line-height: 1;
            }

            .wmc-action-view {
                border-color: #3f5cff;
                color: #3f5cff;
            }

            .wmc-action-view:hover {
                background: #eef2ff;
                color: #2442d8;
            }

            .wmc-action-disabled {
                border-color: #d0d5dd;
                color: #98a2b3;
                cursor: not-allowed;
                opacity: .65;
            }

            .wmc-action-btn svg {
                display: block;
            }

            #paymentPaginationLinks nav {
                margin-bottom: 0;
            }

            #paymentPaginationLinks .pagination {
                margin-bottom: 0;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('paymentFilterForm');
                const searchInput = document.getElementById('paymentSearchInput');
                const clearSearchBtn = document.getElementById('paymentSearchClear');
                const resetBtn = document.getElementById('paymentResetBtn');
                const perPageSelect = document.getElementById('paymentPerPage');
                const tableBody = document.getElementById('paymentTableBody');
                const paginationLinks = document.getElementById('paymentPaginationLinks');
                const showingText = document.getElementById('paymentShowingText');
                const ajaxAlert = document.getElementById('paymentAjaxAlert');

                let debounceTimer = null;
                let activeController = null;

                const indexUrl = @json(route('sales.receive-payments.index'));

                function showInlineAlert(type, message) {
                    if (!ajaxAlert) return;

                    ajaxAlert.className = 'alert rounded-3 mb-4 alert-' + type;
                    ajaxAlert.textContent = message;
                    ajaxAlert.classList.remove('d-none');

                    setTimeout(function () {
                        ajaxAlert.classList.add('d-none');
                    }, 3500);
                }

                function showSwalError(message) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    } else {
                        showInlineAlert('danger', message);
                    }
                }

                function buildUrl(pageUrl = null) {
                    const url = new URL(pageUrl || indexUrl, window.location.origin);
                    const searchValue = searchInput.value.trim();
                    const perPageValue = perPageSelect.value || '10';

                    url.searchParams.set('per_page', perPageValue);

                    if (searchValue !== '') {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }

                    return url;
                }

                function syncUrl(url) {
                    window.history.replaceState({}, '', url.pathname + url.search);
                }

                function toggleClearButton() {
                    if (!clearSearchBtn) return;

                    if (searchInput.value.trim() === '') {
                        clearSearchBtn.classList.add('d-none');
                    } else {
                        clearSearchBtn.classList.remove('d-none');
                    }
                }

                function loadPayments(pageUrl = null, pushUrl = true) {
                    const url = buildUrl(pageUrl);

                    if (activeController) {
                        activeController.abort();
                    }

                    activeController = new AbortController();

                    tableBody.classList.add('is-loading');

                    fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        signal: activeController.signal
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Unable to load payments.');
                            }

                            return response.json();
                        })
                        .then(data => {
                            tableBody.innerHTML = data.tbody;
                            paginationLinks.innerHTML = data.pagination;
                            showingText.textContent = data.showing;

                            if (pushUrl) {
                                syncUrl(url);
                            }

                            toggleClearButton();
                        })
                        .catch(error => {
                            if (error.name !== 'AbortError') {
                                showSwalError(error.message || 'Something went wrong while loading payments.');
                            }
                        })
                        .finally(() => {
                            tableBody.classList.remove('is-loading');
                        });
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    loadPayments();
                });

                searchInput.addEventListener('input', function () {
                    toggleClearButton();

                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        loadPayments();
                    }, 300);
                });

                perPageSelect.addEventListener('change', function () {
                    loadPayments();
                });

                clearSearchBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    toggleClearButton();
                    loadPayments();
                    searchInput.focus();
                });

                resetBtn.addEventListener('click', function (event) {
                    event.preventDefault();

                    searchInput.value = '';
                    perPageSelect.value = '10';
                    toggleClearButton();
                    loadPayments(indexUrl);
                });

                paginationLinks.addEventListener('click', function (event) {
                    const link = event.target.closest('a');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    loadPayments(link.href);
                });
            });
        </script>
    </x-app-layout>
@endif