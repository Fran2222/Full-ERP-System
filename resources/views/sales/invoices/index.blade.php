@if(!empty($ajaxTableOnly))
    @php
        $user = auth()->user();

        $canAccess = function ($permission) use ($user) {
            return $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            );
        };

        $canViewInvoice = $canAccess('sales.invoices.view');
        $canEditInvoice = $canAccess('sales.invoices.edit');
        $canDeleteInvoice = $canAccess('sales.invoices.delete');
        $canManageInvoiceActions = $canViewInvoice || $canEditInvoice || $canDeleteInvoice;
    @endphp

    @forelse($invoices as $row)
        @php
            $statusClass = match ($row->status) {
                'paid' => 'sales-badge sales-badge-success',
                'partially_paid' => 'sales-badge sales-badge-info',
                'unpaid' => 'sales-badge sales-badge-warning',
                'void' => 'sales-badge sales-badge-muted',
                default => 'sales-badge sales-badge-primary',
            };
        @endphp

        <tr>
            <td class="text-secondary">{{ $invoices->firstItem() + $loop->index }}</td>

            <td>
                @if($canViewInvoice)
                    <a href="{{ route('sales.invoices.show', $row) }}" class="fw-semibold text-primary text-decoration-none">
                        {{ $row->invoice_no }}
                    </a>
                @else
                    <span class="fw-semibold text-dark">{{ $row->invoice_no }}</span>
                @endif

                @if($row->reference_no)
                    <div class="small text-secondary">Ref: {{ $row->reference_no }}</div>
                @endif
            </td>

            <td>
                <div class="fw-semibold text-dark">{{ $row->customer?->customer_name ?? '-' }}</div>
                <div class="small text-secondary">{{ $row->customer?->customer_code ?? '-' }}</div>
            </td>

            <td class="text-secondary">{{ optional($row->invoice_date)->format('M d, Y') }}</td>
            <td class="text-secondary">{{ optional($row->due_date)->format('M d, Y') ?: '-' }}</td>

            <td class="text-end fw-semibold">{{ number_format((float) $row->total_amount, 2) }}</td>
            <td class="text-end fw-semibold text-success">{{ number_format((float) $row->paid_amount, 2) }}</td>
            <td class="text-end fw-bold {{ (float) $row->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format((float) $row->balance_due, 2) }}
            </td>

            <td>
                <span class="{{ $statusClass }}">
                    {{ ucwords(str_replace('_', ' ', $row->status)) }}
                </span>
            </td>

            @if($canManageInvoiceActions)
                <td class="text-end">
                    <div class="d-inline-flex gap-1">
                        @if($canViewInvoice)
                            <a href="{{ route('sales.invoices.show', $row) }}"
                               class="wmc-action-btn wmc-action-view"
                               title="View">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif

                        @if($canEditInvoice && (float) $row->paid_amount <= 0)
                            <a href="{{ route('sales.invoices.edit', $row) }}"
                               class="wmc-action-btn wmc-action-edit"
                               title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"
                                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endif

                        @if($canDeleteInvoice && (float) $row->paid_amount <= 0)
                            <form method="POST"
                                  action="{{ route('sales.invoices.destroy', $row) }}"
                                  class="invoice-delete-form">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="wmc-action-btn wmc-action-delete"
                                        title="Delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="{{ $canManageInvoiceActions ? 10 : 9 }}" class="text-center py-5">
                <div class="text-secondary">No invoices found.</div>
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

                abort_unless($canAccess('sales.invoices.view'), 403);

                $canViewInvoice = $canAccess('sales.invoices.view');
                $canCreateInvoice = $canAccess('sales.invoices.create');
                $canEditInvoice = $canAccess('sales.invoices.edit');
                $canDeleteInvoice = $canAccess('sales.invoices.delete');
                $canManageInvoiceActions = $canViewInvoice || $canEditInvoice || $canDeleteInvoice;

                $perPage = $perPage ?? (int) request('per_page', 10);
                $search = $search ?? request('search', '');
            @endphp

            <div class="card sales-panel">
                <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1 fw-bold">Invoices</h4>
                            <p class="text-secondary mb-0">
                                Manage customer invoices, due dates, and balances.
                            </p>
                        </div>

                        @if($canCreateInvoice)
                            <a href="{{ route('sales.invoices.create') }}" class="btn btn-primary sales-soft-btn">
                                New Invoice
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

                    <div id="invoiceAjaxAlert" class="alert rounded-3 mb-4 d-none"></div>

                    <form id="invoiceFilterForm"
                          method="GET"
                          action="{{ route('sales.invoices.index') }}"
                          class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary small fw-semibold">Show</span>
                                    <select name="per_page"
                                            id="invoicePerPage"
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
                                           id="invoiceSearchInput"
                                           value="{{ $search }}"
                                           class="form-control"
                                           autocomplete="off"
                                           placeholder="Search invoice no, customer, reference, or status...">
                                    <button type="button"
                                            id="invoiceSearchClear"
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
                                    <a href="{{ route('sales.invoices.index') }}"
                                       id="invoiceResetBtn"
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
                                    <th>Invoice No.</th>
                                    <th>Customer</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>

                                    @if($canManageInvoiceActions)
                                        <th class="text-end" style="width: 130px;">Actions</th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody id="invoiceTableBody">
                                @include('sales.invoices.index', [
                                    'invoices' => $invoices,
                                    'ajaxTableOnly' => true,
                                ])
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                        <div id="invoiceShowingText" class="text-secondary small">
                            @if($invoices->total() > 0)
                                Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
                            @else
                                Showing 0 entries
                            @endif
                        </div>

                        <div id="invoicePaginationLinks">
                            {{ $invoices->withQueryString()->links() }}
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
                min-width: 1050px;
            }

            .sales-table tbody.is-loading {
                opacity: .45;
                pointer-events: none;
            }

            .sales-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                padding: 5px 10px;
                font-size: 11px;
                font-weight: 800;
                line-height: 1;
                white-space: nowrap;
            }

            .sales-badge-success {
                background: #eaf8f0;
                color: #078642;
            }

            .sales-badge-info {
                background: #eef4ff;
                color: #315cf6;
            }

            .sales-badge-warning {
                background: #fff7e6;
                color: #b45309;
            }

            .sales-badge-primary {
                background: #eef4ff;
                color: #315cf6;
            }

            .sales-badge-muted {
                background: #f3f4f6;
                color: #6b7280;
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

            .wmc-action-view,
            .wmc-action-edit {
                border-color: #3f5cff;
                color: #3f5cff;
            }

            .wmc-action-view:hover,
            .wmc-action-edit:hover {
                background: #eef2ff;
                color: #2442d8;
            }

            .wmc-action-delete {
                border-color: #f04438;
                color: #f04438;
            }

            .wmc-action-delete:hover {
                background: #fff1f0;
                color: #d92d20;
            }

            .wmc-action-btn svg {
                display: block;
            }

            #invoicePaginationLinks nav {
                margin-bottom: 0;
            }

            #invoicePaginationLinks .pagination {
                margin-bottom: 0;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('invoiceFilterForm');
                const searchInput = document.getElementById('invoiceSearchInput');
                const clearSearchBtn = document.getElementById('invoiceSearchClear');
                const resetBtn = document.getElementById('invoiceResetBtn');
                const perPageSelect = document.getElementById('invoicePerPage');
                const tableBody = document.getElementById('invoiceTableBody');
                const paginationLinks = document.getElementById('invoicePaginationLinks');
                const showingText = document.getElementById('invoiceShowingText');
                const ajaxAlert = document.getElementById('invoiceAjaxAlert');

                let debounceTimer = null;
                let activeController = null;

                const indexUrl = @json(route('sales.invoices.index'));
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

                function showInlineAlert(type, message) {
                    if (!ajaxAlert) return;

                    ajaxAlert.className = 'alert rounded-3 mb-4 alert-' + type;
                    ajaxAlert.textContent = message;
                    ajaxAlert.classList.remove('d-none');

                    setTimeout(function () {
                        ajaxAlert.classList.add('d-none');
                    }, 3500);
                }

                function showSwalSuccess(message) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Done',
                            text: message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        showInlineAlert('success', message);
                    }
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

                function confirmDelete() {
                    if (window.Swal) {
                        return Swal.fire({
                            icon: 'warning',
                            title: 'Are you sure?',
                            text: 'This invoice will be deleted permanently.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            reverseButtons: true
                        }).then(result => result.isConfirmed);
                    }

                    return Promise.resolve(confirm('Delete this invoice? This action cannot be undone.'));
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

                function loadInvoices(pageUrl = null, pushUrl = true) {
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
                                throw new Error('Unable to load invoices.');
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
                                showSwalError(error.message || 'Something went wrong while loading invoices.');
                            }
                        })
                        .finally(() => {
                            tableBody.classList.remove('is-loading');
                        });
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    loadInvoices();
                });

                searchInput.addEventListener('input', function () {
                    toggleClearButton();

                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        loadInvoices();
                    }, 300);
                });

                perPageSelect.addEventListener('change', function () {
                    loadInvoices();
                });

                clearSearchBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    toggleClearButton();
                    loadInvoices();
                    searchInput.focus();
                });

                resetBtn.addEventListener('click', function (event) {
                    event.preventDefault();

                    searchInput.value = '';
                    perPageSelect.value = '10';
                    toggleClearButton();
                    loadInvoices(indexUrl);
                });

                paginationLinks.addEventListener('click', function (event) {
                    const link = event.target.closest('a');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    loadInvoices(link.href);
                });

                document.addEventListener('submit', function (event) {
                    const deleteForm = event.target.closest('.invoice-delete-form');

                    if (!deleteForm) {
                        return;
                    }

                    event.preventDefault();

                    confirmDelete().then(function (confirmed) {
                        if (!confirmed) {
                            return;
                        }

                        fetch(deleteForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: new FormData(deleteForm)
                        })
                            .then(async response => {
                                const data = await response.json().catch(() => ({}));

                                if (!response.ok) {
                                    throw new Error(data.message || 'Unable to delete invoice.');
                                }

                                return data;
                            })
                            .then(data => {
                                showSwalSuccess(data.message || 'Invoice deleted successfully.');
                                loadInvoices();
                            })
                            .catch(error => {
                                showSwalError(error.message || 'Something went wrong while deleting invoice.');
                            });
                    });
                });
            });
        </script>
    </x-app-layout>
@endif