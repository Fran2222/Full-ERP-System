<x-app-layout :assets="$assets ?? []">
    <style>
        /* Users-card style sorting icon */
        .wmc-sort-link {
            width: 100%;
            color: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.35rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .wmc-sort-link:hover {
            color: #3b4df0;
        }

        .wmc-sort-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            margin-left: auto;
        }

        .wmc-sort-icon svg {
            width: 16px;
            height: 16px;
            overflow: visible;
            display: block;
        }

        .wmc-sort-up,
        .wmc-sort-down {
            fill: none;
            stroke: #d7dce6;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .wmc-sort-icon.active.asc .wmc-sort-up,
        .wmc-sort-icon.active.desc .wmc-sort-down {
            stroke: #8b95a7;
        }

        .wmc-sort-link:hover .wmc-sort-up,
        .wmc-sort-link:hover .wmc-sort-down {
            stroke: #aeb6c5;
        }

        .wmc-sort-link:hover .wmc-sort-icon.active.asc .wmc-sort-up,
        .wmc-sort-link:hover .wmc-sort-icon.active.desc .wmc-sort-down {
            stroke: #687386;
        }

        .wmc-designation-action-cell {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .wmc-designation-action-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .wmc-designation-action-wrap > div {
            justify-content: center !important;
        }

        .wmc-swal-actions-gap {
            gap: 14px !important;
        }

        .wmc-swal-actions-gap .btn,
        .wmc-swal-actions-gap .swal2-styled {
            margin: 0 !important;
            min-width: 120px;
        }

        .wmc-designation-footer {
            width: 100%;
        }

        .wmc-designation-pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .wmc-designation-pagination nav {
            display: flex;
            align-items: center;
            margin: 0 !important;
        }

        .wmc-designation-pagination .pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0;
            margin: 0 !important;
            padding-left: 0 !important;
            list-style: none !important;
        }

        .wmc-designation-pagination .page-item {
            margin: 0 !important;
        }

        .wmc-designation-pagination .page-link {
            min-width: 42px;
            height: 38px;
            padding: 8px 13px;
            border-radius: 0 !important;
            border: 1px solid #e5e7eb;
            color: #315cf6;
            background: #ffffff;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
            text-decoration: none;
        }

        .wmc-designation-pagination .page-item:first-child .page-link {
            border-top-left-radius: 4px !important;
            border-bottom-left-radius: 4px !important;
        }

        .wmc-designation-pagination .page-item:last-child .page-link {
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }

        .wmc-designation-pagination .page-item + .page-item .page-link {
            margin-left: -1px;
        }

        .wmc-designation-pagination .page-link:hover {
            color: #315cf6;
            background: #f8fafc;
            border-color: #dbe3ef;
            z-index: 2;
        }

        .wmc-designation-pagination .page-item.active .page-link {
            color: #ffffff;
            background: #315cf6;
            border-color: #315cf6;
            z-index: 3;
        }

        .wmc-designation-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #ffffff;
            border-color: #e5e7eb;
            cursor: not-allowed;
            pointer-events: none;
        }

        .wmc-designation-pagination svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
        }

        @media (max-width: 767.98px) {
            .wmc-designation-footer {
                align-items: flex-start !important;
            }

            .wmc-designation-pagination {
                width: 100%;
                justify-content: flex-start;
            }

            .wmc-designation-pagination .pagination {
                justify-content: flex-start;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="row">
            <div class="col-sm-12">
                <div class="card rounded-4 wmc-designation-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="header-title">
                            <h4 class="card-title mb-1">Designations</h4>
                            <p class="mb-0 text-secondary">Manage designations per department.</p>
                        </div>

                        <a href="{{ route('designations.create') }}" class="btn btn-primary btn-sm">
                            Add Designation
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                            <form method="GET" class="d-flex align-items-center gap-2">
                                <label class="mb-0 text-secondary small">Show</label>

                                <select name="per_page"
                                        class="form-select form-select-sm w-auto"
                                        data-wmc-per-page="true">
                                    @foreach([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="mb-0 text-secondary small">entries</label>

                                @if(!empty($search))
                                    <input type="hidden" name="search" value="{{ $search }}">
                                @endif

                                <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">
                            </form>

                            <form method="GET" class="d-flex align-items-center gap-2 ms-md-auto" id="designation-search-form">
                                <input id="designation-search"
                                       type="text"
                                       value="{{ $search ?? '' }}"
                                       class="form-control form-control-sm"
                                       style="min-width: 240px;"
                                       placeholder="Search"
                                       autocomplete="off">

                                <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                                <input type="hidden" name="sort" value="{{ $sort ?? request('sort', 'id') }}">
                                <input type="hidden" name="direction" value="{{ $direction ?? request('direction', 'asc') }}">
                            </form>
                        </div>

                        @php
                            $sort = $sort ?? request('sort', 'id');
                            $direction = $direction ?? request('direction', 'asc');

                            $sortIconClass = fn ($column) => $sort === $column
                                ? 'wmc-sort-icon active ' . $direction
                                : 'wmc-sort-icon';

                            $sortIconSvg = function ($column) use ($sortIconClass) {
                                return '<span class="' . $sortIconClass($column) . '" aria-hidden="true">'
                                    . '<svg viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">'
                                    . '<path class="wmc-sort-up" d="M8 20V5M8 5L2.5 10.5M8 5L13.5 10.5" />'
                                    . '<path class="wmc-sort-down" d="M22 4V19M22 19L16.5 13.5M22 19L27.5 13.5" />'
                                    . '</svg>'
                                    . '</span>';
                            };

                            $sortUrl = function ($column) use ($sort, $direction) {
                                $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

                                return route('designations.index', array_merge(request()->except('page'), [
                                    'sort' => $column,
                                    'direction' => $nextDirection,
                                ]));
                            };
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle mb-0 wmc-designation-table">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">
                                            <a href="{{ $sortUrl('id') }}" class="wmc-sort-link">
                                                ID {!! $sortIconSvg('id') !!}
                                            </a>
                                        </th>

                                        <th>
                                            <a href="{{ $sortUrl('name') }}" class="wmc-sort-link">
                                                Name {!! $sortIconSvg('name') !!}
                                            </a>
                                        </th>

                                        <th>
                                            <a href="{{ $sortUrl('department') }}" class="wmc-sort-link">
                                                Department {!! $sortIconSvg('department') !!}
                                            </a>
                                        </th>

                                        <th style="width: 150px;">Status</th>
                                        <th style="width: 120px;" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($designations as $designation)
                                        <tr>
                                            <td>{{ $designation->id }}</td>
                                            <td>{{ $designation->name }}</td>
                                            <td>{{ optional($designation->department)->name ?: '—' }}</td>
                                            <td>
                                                <span class="badge {{ $designation->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($designation->status) }}
                                                </span>
                                            </td>
                                            <td class="wmc-designation-action-cell">
                                                <div class="wmc-designation-action-wrap">
                                                    {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                        route('designations.edit', $designation->id),
                                                        route('designations.destroy', $designation->id),
                                                        $designation->name,
                                                        'delete-designation-btn',
                                                        'Edit Designation',
                                                        'Delete Designation'
                                                    ) !!}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No designations found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3 wmc-designation-footer">
                            <div class="text-secondary small">
                                Showing {{ $designations->firstItem() ?? 0 }}
                                to {{ $designations->lastItem() ?? 0 }}
                                of {{ $designations->total() }}
                                entries
                            </div>

                            <div class="wmc-designation-pagination">
                                @if ($designations->hasPages())
                                    <nav aria-label="Designation pagination">
                                        <ul class="pagination mb-0">
                                            {{-- Previous Page Link --}}
                                            @if ($designations->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link" tabindex="-1">Previous</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $designations->previousPageUrl() }}" rel="prev">Previous</a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements --}}
                                            @foreach ($designations->getUrlRange(1, $designations->lastPage()) as $page => $url)
                                                @if ($page == $designations->currentPage())
                                                    <li class="page-item active" aria-current="page">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
                                            @if ($designations->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $designations->nextPageUrl() }}" rel="next">Next</a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">Next</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Server-side realtime search without whole-card refresh/preload effect --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let designationSearchTimer = null;
            let designationSearchController = null;

            function getCurrentSearchValue() {
                const input = document.getElementById('designation-search');
                return input ? input.value.trim() : '';
            }

            function getCurrentPerPageValue() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');
                return select ? select.value : '10';
            }

            function buildDesignationUrl(pageUrl = null) {
                const url = new URL(pageUrl || window.location.href);
                const searchValue = getCurrentSearchValue();
                const perPageValue = getCurrentPerPageValue();

                url.searchParams.set('per_page', perPageValue);

                if (searchValue !== '') {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }

                if (!pageUrl) {
                    url.searchParams.delete('page');
                }

                return url;
            }

            function updateDesignationListFromHtml(html, url, keepFocus = true) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const currentThead = document.querySelector('.wmc-designation-table thead');
                const newThead = doc.querySelector('.wmc-designation-table thead');

                const currentTbody = document.querySelector('.wmc-designation-table tbody');
                const newTbody = doc.querySelector('.wmc-designation-table tbody');

                const currentFooter = document.querySelector('.wmc-designation-footer');
                const newFooter = doc.querySelector('.wmc-designation-footer');

                const currentInput = document.getElementById('designation-search');
                const currentValue = currentInput ? currentInput.value : '';
                const currentCursor = currentInput ? currentInput.selectionStart : currentValue.length;

                if (currentThead && newThead) {
                    currentThead.innerHTML = newThead.innerHTML;
                }

                if (currentTbody && newTbody) {
                    currentTbody.innerHTML = newTbody.innerHTML;
                }

                if (currentFooter && newFooter) {
                    currentFooter.innerHTML = newFooter.innerHTML;
                }

                window.history.replaceState({}, '', url.toString());

                initWmcDesignationControls();

                const refreshedInput = document.getElementById('designation-search');

                if (refreshedInput) {
                    refreshedInput.value = currentValue;

                    if (keepFocus) {
                        refreshedInput.focus();

                        try {
                            refreshedInput.setSelectionRange(currentCursor, currentCursor);
                        } catch (e) {}
                    }
                }
            }

            function fetchDesignationList(url, keepFocus = true) {
                if (designationSearchController) {
                    designationSearchController.abort();
                }

                designationSearchController = new AbortController();

                fetch(url.toString(), {
                    method: 'GET',
                    signal: designationSearchController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {
                    updateDesignationListFromHtml(html, url, keepFocus);
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Designation list update failed:', error);
                    }
                });
            }

            function setupRealtimeServerSearch() {
                const input = document.getElementById('designation-search');
                const form = document.getElementById('designation-search-form');

                if (!input || input.dataset.wmcSearchReady === '1') {
                    return;
                }

                input.dataset.wmcSearchReady = '1';
                input.removeAttribute('name');
                input.setAttribute('autocomplete', 'off');

                input.addEventListener('input', function () {
                    clearTimeout(designationSearchTimer);

                    designationSearchTimer = setTimeout(function () {
                        const url = buildDesignationUrl();
                        fetchDesignationList(url, true);
                    }, 250);
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();

                        clearTimeout(designationSearchTimer);

                        const url = buildDesignationUrl();
                        fetchDesignationList(url, true);
                    }
                });

                if (form && form.dataset.wmcSubmitReady !== '1') {
                    form.dataset.wmcSubmitReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        clearTimeout(designationSearchTimer);

                        const url = buildDesignationUrl();
                        fetchDesignationList(url, true);
                    });
                }
            }

            function setupAjaxPerPage() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');

                if (!select || select.dataset.wmcPerPageReady === '1') {
                    return;
                }

                select.dataset.wmcPerPageReady = '1';
                select.dataset.wmcPerPage = 'true';
                select.removeAttribute('onchange');

                const form = select.closest('form');

                if (form && form.dataset.wmcPerPageFormReady !== '1') {
                    form.dataset.wmcPerPageFormReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                    });
                }

                select.addEventListener('change', function () {
                    const url = buildDesignationUrl();
                    fetchDesignationList(url, false);
                });
            }

            function setupAjaxPagination() {
                const footer = document.querySelector('.wmc-designation-footer');

                if (!footer || footer.dataset.wmcPaginationReady === '1') {
                    return;
                }

                footer.dataset.wmcPaginationReady = '1';

                footer.addEventListener('click', function (event) {
                    const link = event.target.closest('.pagination a');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();

                    const url = buildDesignationUrl(link.href);
                    fetchDesignationList(url, false);
                });
            }

            function setupDeleteConfirm() {
                document.querySelectorAll('.delete-form').forEach(function (form) {
                    if (form.dataset.wmcDeleteReady === '1') {
                        return;
                    }

                    form.dataset.wmcDeleteReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const button = form.querySelector('.delete-designation-btn, button[type="submit"]');
                        const deleteUrl = button ? (button.getAttribute('data-url') || form.getAttribute('action')) : form.getAttribute('action');
                        const designationName = button ? (button.getAttribute('data-name') || 'this designation') : 'this designation';

                        confirmAndDeleteDesignation(deleteUrl, designationName, button);
                    });
                });

                document.querySelectorAll('.delete-designation-btn').forEach(function (button) {
                    if (button.dataset.wmcDeleteClickReady === '1') {
                        return;
                    }

                    button.dataset.wmcDeleteClickReady = '1';

                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const form = button.closest('form');
                        const deleteUrl = button.getAttribute('data-url') || (form ? form.getAttribute('action') : '');
                        const designationName = button.getAttribute('data-name') || 'this designation';

                        confirmAndDeleteDesignation(deleteUrl, designationName, button);
                    });
                });

                function confirmAndDeleteDesignation(deleteUrl, designationName, button) {
                    const csrfToken = '{{ csrf_token() }}';

                    if (!deleteUrl) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Delete URL not found.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-4',
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });

                        return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Delete ' + designationName + '?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete It',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-4',
                            actions: 'wmc-swal-actions-gap',
                            confirmButton: 'btn btn-danger px-4',
                            cancelButton: 'btn btn-light px-4'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        if (button) {
                            button.disabled = true;
                        }

                        fetch(deleteUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: '_method=DELETE'
                        })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Delete failed.');
                            }

                            return response.json().catch(function () {
                                return {
                                    success: true,
                                    message: 'Designation deleted successfully.'
                                };
                            });
                        })
                        .then(function (data) {
                            const row = button ? button.closest('tr') : null;

                            if (row) {
                                row.style.transition = 'opacity 0.2s ease';
                                row.style.opacity = '0';

                                setTimeout(function () {
                                    row.remove();
                                }, 200);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Done',
                                text: data.message || 'Designation deleted successfully.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-4',
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        })
                        .catch(function () {
                            if (button) {
                                button.disabled = false;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to delete designation. Please try again.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-4',
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        });
                    });
                }
            }

            function setupAjaxSorting() {
                document.querySelectorAll('.wmc-sort-link').forEach(function (link) {
                    if (link.dataset.wmcSortReady === '1') {
                        return;
                    }

                    link.dataset.wmcSortReady = '1';

                    link.addEventListener('click', function (event) {
                        event.preventDefault();

                        const url = buildDesignationUrl(link.href);
                        fetchDesignationList(url, false);
                    });
                });
            }

            function initWmcDesignationControls() {
                setupRealtimeServerSearch();
                setupAjaxPerPage();
                setupAjaxPagination();
                setupAjaxSorting();
                setupDeleteConfirm();
            }

            initWmcDesignationControls();
        });
    </script>
</x-app-layout>