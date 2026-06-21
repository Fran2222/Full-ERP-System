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

        .wmc-branch-action-cell {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .wmc-branch-action-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .wmc-branch-action-wrap > div {
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

        /* Fit Branches table at 125% browser / display scale */
        .wmc-branch-table {
            table-layout: fixed;
            width: 100% !important;
            min-width: 0 !important;
            font-size: 0.92rem;
        }

        .wmc-branch-table th,
        .wmc-branch-table td {
            vertical-align: middle !important;
            padding: 0.8rem 0.85rem;
        }

        .wmc-branch-table thead th {
            white-space: nowrap;
        }

        .wmc-branch-table .wmc-col-id { width: 7%; }
        .wmc-branch-table .wmc-col-name { width: 21%; }
        .wmc-branch-table .wmc-col-code { width: 10%; }
        .wmc-branch-table .wmc-col-address { width: 38%; }
        .wmc-branch-table .wmc-col-status { width: 11%; }
        .wmc-branch-table .wmc-col-action { width: 13%; }

        .wmc-branch-table .wmc-branch-name,
        .wmc-branch-table .wmc-branch-code {
            overflow-wrap: anywhere;
            word-break: normal;
            line-height: 1.35;
        }

        .wmc-branch-table .wmc-branch-address {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.35;
        }

        .wmc-branch-table .wmc-branch-code,
        .wmc-branch-table .wmc-branch-status-cell,
        .wmc-branch-table .wmc-branch-action-cell {
            text-align: center !important;
        }

        .wmc-branch-table .badge {
            white-space: nowrap;
        }

        @media (min-width: 1200px) {
            .wmc-branch-table-wrap.table-responsive {
                overflow-x: visible;
            }
        }

        @media (max-width: 1199.98px) {
            .wmc-branch-table {
                min-width: 980px !important;
            }
        }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="row">
            <div class="col-sm-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="header-title">
                            <h4 class="card-title mb-1">Branches</h4>
                            <p class="mb-0 text-secondary">Manage company branch records.</p>
                        </div>

                        @php
                            $currentUser = auth()->user();

                            $canCreateBranch = $currentUser && (
                                $currentUser->hasAnyRole([
                                    'super-admin',
                                    'super admin',
                                    'superadmin',
                                    'hr',
                                ])
                                || $currentUser->can('branches.create')
                                || $currentUser->can('hr.branches.create')
                            );
                        @endphp

                        @if($canCreateBranch)
                            <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">
                                Add Branch
                            </a>
                        @endif
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

                            <form method="GET" class="d-flex align-items-center gap-2 ms-md-auto">
                                <input id="branch-search"
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

                                return route('branches.index', array_merge(request()->except('page'), [
                                    'sort' => $column,
                                    'direction' => $nextDirection,
                                ]));
                            };
                        @endphp

                        <div class="table-responsive wmc-branch-table-wrap">
                            <table class="table table-striped table-bordered align-middle mb-0 wmc-branch-table">
                                <colgroup>
                                    <col class="wmc-col-id">
                                    <col class="wmc-col-name">
                                    <col class="wmc-col-code">
                                    <col class="wmc-col-address">
                                    <col class="wmc-col-status">
                                    <col class="wmc-col-action">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="{{ $sortUrl('id') }}" class="wmc-sort-link">
                                                ID {!! $sortIconSvg('id') !!}
                                            </a>
                                        </th>

                                        <th>
                                            <a href="{{ $sortUrl('name') }}" class="wmc-sort-link">
                                                Name {!! $sortIconSvg('name') !!}
                                            </a>
                                        </th>

                                        <th class="text-center">
                                            <a href="{{ $sortUrl('code') }}" class="wmc-sort-link">
                                                Code {!! $sortIconSvg('code') !!}
                                            </a>
                                        </th>

                                        <th>
                                            <a href="{{ $sortUrl('address') }}" class="wmc-sort-link">
                                                Address {!! $sortIconSvg('address') !!}
                                            </a>
                                        </th>

                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($branches as $branch)
                                        <tr>
                                            <td class="text-center">{{ $branch->id }}</td>
                                            <td class="wmc-branch-name">{{ $branch->name }}</td>
                                            <td class="wmc-branch-code">{{ $branch->code }}</td>
                                            <td><span class="wmc-branch-address" title="{{ $branch->address ?: '—' }}">{{ $branch->address ?: '—' }}</span></td>
                                            <td class="wmc-branch-status-cell">
                                                <span class="badge {{ $branch->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($branch->status) }}
                                                </span>
                                            </td>

<td class="wmc-branch-action-cell">
    @php
        $currentUser = auth()->user();

        $roleNames = $currentUser
            ? $currentUser->roles->pluck('name')->map(fn ($role) => strtolower(trim($role)))->toArray()
            : [];

        $isSuperAdmin = in_array('super-admin', $roleNames, true)
            || in_array('super admin', $roleNames, true)
            || in_array('superadmin', $roleNames, true)
            || ($currentUser && strtolower((string) $currentUser->user_type) === 'admin');

        $isHr = in_array('hr', $roleNames, true)
            || in_array('human resource', $roleNames, true)
            || in_array('human resources', $roleNames, true);

        $canEditBranch = $isSuperAdmin
            || $isHr
            || ($currentUser && (
                $currentUser->can('branches.edit')
                || $currentUser->can('hr.branches.edit')
            ));

        /*
            Rule:
            - Super Admin/Admin = Edit + Delete
            - HR = Edit only
            - Other users = based on permission, but HR delete is intentionally blocked here
        */
        $canDeleteBranch = $isSuperAdmin
            || ($currentUser && ! $isHr && $currentUser->can('branches.delete'));
    @endphp

    @if($canEditBranch || $canDeleteBranch)
        <div class="wmc-branch-action-wrap">
            {!! \App\Helpers\ActionButtonHelper::editDelete(
                $canEditBranch ? route('branches.edit', $branch->id) : null,
                $canDeleteBranch ? route('branches.destroy', $branch->id) : null,
                $branch->name,
                'delete-branch-btn',
                'Edit Branch',
                'Delete Branch'
            ) !!}
        </div>
    @else
        <span class="text-muted">—</span>
    @endif
</td>                                        
</tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No branches found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($branches, 'total'))
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                                <div class="text-secondary small">
                                    Showing {{ $branches->firstItem() ?? 0 }}
                                    to {{ $branches->lastItem() ?? 0 }}
                                    of {{ $branches->total() }}
                                    entries
                                </div>

                                <div>
                                    {{ $branches->onEachSide(1)->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */
            function getMainCard() {
                const select = document.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');

                if (select) {
                    return select.closest('.card');
                }

                return document.querySelector('.container-fluid.content-inner .card');
            }

            function getDataRows(tbody) {
                return Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                    return !row.classList.contains('wmc-no-result-row');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Realtime Search
            |--------------------------------------------------------------------------
            */
            function setupRealtimeSearch() {
                const input = document.getElementById('branch-search');
                const table = document.querySelector('table');

                if (!input || !table || input.dataset.wmcSearchReady === '1') {
                    return;
                }

                input.dataset.wmcSearchReady = '1';
                input.removeAttribute('name');
                input.setAttribute('autocomplete', 'off');

                const form = input.closest('form');
                const tbody = table.querySelector('tbody');

                if (!tbody) {
                    return;
                }

                let emptyRow = tbody.querySelector('tr.wmc-no-result-row');

                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.className = 'wmc-no-result-row d-none';
                    emptyRow.innerHTML = '<td colspan="6" class="text-center text-muted py-4">No branches found.</td>';
                    tbody.appendChild(emptyRow);
                }

                function filterRows() {
                    const keyword = input.value.toLowerCase().trim();
                    const rows = getDataRows(tbody);
                    let visibleCount = 0;

                    rows.forEach(function (row) {
                        const matched = keyword === '' || row.innerText.toLowerCase().includes(keyword);

                        row.style.display = matched ? '' : 'none';

                        if (matched) {
                            visibleCount++;
                        }
                    });

                    emptyRow.classList.toggle('d-none', visibleCount > 0);
                }

                ['input', 'keyup', 'search', 'paste', 'cut', 'compositionend'].forEach(function (eventName) {
                    input.addEventListener(eventName, function () {
                        setTimeout(filterRows, 0);
                    });
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        filterRows();
                    }
                });

                if (form && form.dataset.wmcSubmitReady !== '1') {
                    form.dataset.wmcSubmitReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        filterRows();
                    });
                }

                filterRows();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Confirmation + AJAX Delete
            |--------------------------------------------------------------------------
            | Flow:
            | Delete icon -> Are you sure? -> AJAX delete/no preload -> Done Swal
            */
            function setupDeleteConfirm() {
                document.querySelectorAll('.delete-form').forEach(function (form) {
                    if (form.dataset.wmcDeleteReady === '1') {
                        return;
                    }

                    form.dataset.wmcDeleteReady = '1';

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const button = form.querySelector('.delete-branch-btn, button[type="submit"]');
                        const deleteUrl = button ? (button.getAttribute('data-url') || form.getAttribute('action')) : form.getAttribute('action');
                        const branchName = button ? (button.getAttribute('data-name') || 'this branch') : 'this branch';

                        confirmAndDeleteBranch(deleteUrl, branchName, button);
                    });
                });

                document.querySelectorAll('.delete-branch-btn').forEach(function (button) {
                    if (button.dataset.wmcDeleteClickReady === '1') {
                        return;
                    }

                    button.dataset.wmcDeleteClickReady = '1';

                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const form = button.closest('form');
                        const deleteUrl = button.getAttribute('data-url') || (form ? form.getAttribute('action') : '');
                        const branchName = button.getAttribute('data-name') || 'this branch';

                        confirmAndDeleteBranch(deleteUrl, branchName, button);
                    });
                });

                function confirmAndDeleteBranch(deleteUrl, branchName, button) {
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
                        text: 'Delete ' + branchName + '?',
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
                                    message: 'Branch deleted successfully.'
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
                                text: data.message || 'Branch deleted successfully.',
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
                                text: 'Unable to delete branch. Please try again.',
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

            /*
            |--------------------------------------------------------------------------
            | Show Entries AJAX
            |--------------------------------------------------------------------------
            */
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
                    const oldCard = getMainCard();

                    if (!oldCard) {
                        return;
                    }

                    const searchInput = document.getElementById('branch-search');
                    const currentSearch = searchInput ? searchInput.value : '';

                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', select.value);
                    url.searchParams.delete('page');

                    if (currentSearch.trim() !== '') {
                        url.searchParams.set('search', currentSearch.trim());
                    } else {
                        url.searchParams.delete('search');
                    }

                    select.disabled = true;

                    fetch(url.toString(), {
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

                        const newSelect = doc.querySelector('select[name="per_page"], select[data-wmc-per-page="true"]');
                        const newCard = newSelect
                            ? newSelect.closest('.card')
                            : doc.querySelector('.container-fluid.content-inner .card');

                        if (!newCard) {
                            select.disabled = false;
                            return;
                        }

                        oldCard.replaceWith(newCard);
                        window.history.replaceState({}, '', url.toString());

                        const refreshedSearch = document.getElementById('branch-search');

                        if (refreshedSearch && currentSearch) {
                            refreshedSearch.value = currentSearch;
                        }

                        initWmcTableControls();
                    })
                    .catch(function () {
                        select.disabled = false;
                    });
                });
            }

            function setupAjaxSorting() {
                document.querySelectorAll('.wmc-sort-link').forEach(function (link) {
                    if (link.dataset.wmcSortReady === '1') {
                        return;
                    }

                    link.dataset.wmcSortReady = '1';

                    link.addEventListener('click', function (event) {
                        event.preventDefault();

                        const oldCard = getMainCard();

                        if (!oldCard) {
                            window.location.href = link.href;
                            return;
                        }

                        fetch(link.href, {
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
                            const newCard = doc.querySelector('.container-fluid.content-inner .card');

                            if (!newCard) {
                                window.location.href = link.href;
                                return;
                            }

                            oldCard.replaceWith(newCard);
                            window.history.replaceState({}, '', link.href);
                            initWmcTableControls();
                        })
                        .catch(function () {
                            window.location.href = link.href;
                        });
                    });
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Init
            |--------------------------------------------------------------------------
            */
            function initWmcTableControls() {
                document.querySelectorAll('select[name="per_page"]').forEach(function (select) {
                    select.removeAttribute('onchange');
                    select.dataset.wmcPerPage = 'true';
                });

                setupRealtimeSearch();
                setupDeleteConfirm();
                setupAjaxPerPage();
                setupAjaxSorting();
            }

            initWmcTableControls();
        });
    </script>
</x-app-layout>