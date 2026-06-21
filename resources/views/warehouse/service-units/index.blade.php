<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Service Units</h4>
                        <p class="text-secondary mb-0">Track borrowed / issued company service units by employee and serial number.</p>
                    </div>

                    <a href="{{ route('warehouse.service-units.create') }}" class="btn btn-primary px-4">
                        Borrow / Issue Unit
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="{{ route('warehouse.service-units.index', ['status' => 'active', 'per_page' => $perPage ?? request('per_page', 10)]) }}" class="text-decoration-none text-reset">
                            <div class="border rounded-3 p-3 h-100 service-unit-summary-card {{ request('status') === 'active' ? 'border-primary bg-primary-subtle' : '' }}">
                                <div class="text-secondary small">Active Borrowed Units</div>
                                <div class="h4 mb-0 fw-bold text-primary">{{ $activeCount }}</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('warehouse.service-units.index', ['status' => 'returned', 'per_page' => $perPage ?? request('per_page', 10)]) }}" class="text-decoration-none text-reset">
                            <div class="border rounded-3 p-3 h-100 service-unit-summary-card {{ request('status') === 'returned' ? 'border-success bg-success-subtle' : '' }}">
                                <div class="text-secondary small">Returned Units</div>
                                <div class="h4 mb-0 fw-bold text-success">{{ $returnedCount }}</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('warehouse.service-units.index', ['status' => 'overdue', 'per_page' => $perPage ?? request('per_page', 10)]) }}" class="text-decoration-none text-reset">
                            <div class="border rounded-3 p-3 h-100 service-unit-summary-card {{ request('status') === 'overdue' ? 'border-danger bg-danger-subtle' : '' }}">
                                <div class="text-secondary small">Overdue Units</div>
                                <div class="h4 mb-0 fw-bold text-danger">{{ $overdueCount }}</div>
                            </div>
                        </a>
                    </div>
                </div>

                <form id="serviceUnitFilterForm" method="GET" action="{{ route('warehouse.service-units.index') }}" class="mb-3 service-units-filter-form">
                    <div class="service-units-filter-grid align-items-end">
                        <div class="service-units-filter-item service-units-filter-show">
                            <label class="form-label small text-secondary mb-1">Show</label>
                            <select name="per_page" id="serviceUnitPerPage" class="form-select">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int)($perPage ?? request('per_page', 10)) === $size ? 'selected' : '' }}>{{ $size }} entries</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="service-units-filter-item service-units-filter-status">
                            <label class="form-label small text-secondary mb-1">Status</label>
                            <select name="status" id="serviceUnitStatus" class="form-select">
                                <option value="">All Status</option>
                                @foreach(['active' => 'Active', 'returned' => 'Returned', 'overdue' => 'Overdue', 'for_repair' => 'For Repair', 'damaged' => 'Damaged', 'lost' => 'Lost'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="service-units-filter-item service-units-filter-search">
                            <label class="form-label small text-secondary mb-1">Search</label>
                            <input type="text"
                                   name="search"
                                   id="serviceUnitSearch"
                                   value="{{ request('search') }}"
                                   class="form-control"
                                   placeholder="Search borrow no, employee, item, serial, location, remarks..."
                                   autocomplete="off">
                        </div>
                        <div class="service-units-filter-actions d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">Search</button>
                            <a href="{{ route('warehouse.service-units.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive service-units-table-wrap">
                    <table class="table align-middle mb-0 service-units-table">
                        <thead class="table-light">
                            <tr>
                                <th class="service-units-col-count">#</th>
                                <th>Borrow No.</th>
                                <th>Employee</th>
                                <th>Item / Serial</th>
                                <th>Location</th>
                                <th>Borrowed</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($borrows as $borrow)
                                @php
                                    $status = strtolower($borrow->status ?? 'active');
                                    $isOverdue = $status === 'active'
                                        && $borrow->expected_return_at
                                        && $borrow->expected_return_at->lt(now()->startOfDay());

                                    $badgeClass = match($status) {
                                        'active' => $isOverdue ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary',
                                        'returned' => 'bg-success-subtle text-success',
                                        'for_repair', 'damaged' => 'bg-warning-subtle text-warning',
                                        'lost' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($borrows->currentPage() - 1) * $borrows->perPage() }}</td>
                                    <td>
                                        <a href="{{ route('warehouse.service-units.show', $borrow) }}" class="fw-semibold text-primary">{{ $borrow->borrow_no }}</a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $borrow->employee?->full_name ?: trim(($borrow->employee?->first_name ?? '') . ' ' . ($borrow->employee?->last_name ?? '')) ?: '-' }}</div>
                                        <div class="small text-secondary">{{ $borrow->employee?->email }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $borrow->item?->name ?? $borrow->item?->item_name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $borrow->serial?->serial_number ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $borrow->location?->location_name ?? $borrow->location?->name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $borrow->branch?->name ?? '-' }}</div>
                                    </td>
                                    <td>{{ optional($borrow->borrowed_at)->format('M d, Y') }}</td>
                                    <td>{{ optional($borrow->expected_return_at)->format('M d, Y') ?? '-' }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                                            {{ $isOverdue ? 'Overdue' : \Illuminate\Support\Str::headline($status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('warehouse.service-units.show', $borrow) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @if($borrow->status === 'active')
                                                <a href="{{ route('warehouse.service-units.return', $borrow) }}" class="btn btn-sm btn-success">Return</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-secondary py-5">No borrowed service units found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                    <div class="small text-secondary">
                        Showing {{ $borrows->firstItem() ?? 0 }} to {{ $borrows->lastItem() ?? 0 }} of {{ $borrows->total() }} entries
                    </div>
                    <div>
                        {{ $borrows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .service-unit-summary-card {
            transition: 0.15s ease-in-out;
        }

        .service-unit-summary-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.06);
        }

        .service-units-filter-grid {
            display: grid;
            grid-template-columns: minmax(160px, 180px) minmax(220px, 280px) minmax(320px, 1fr) minmax(190px, 230px);
            gap: 0.75rem;
            width: 100%;
        }

        .service-units-filter-grid .form-label {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .service-units-filter-grid .form-select,
        .service-units-filter-grid .form-control,
        .service-units-filter-grid .btn {
            min-height: 46px;
            border-radius: 0.55rem;
        }

        .service-units-filter-actions {
            min-width: 0;
        }

        .service-units-filter-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .service-units-table-wrap {
            min-height: 240px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(107, 114, 128, 0.55) rgba(241, 245, 249, 0.95);
        }

        .service-units-table-wrap::-webkit-scrollbar {
            height: 9px;
        }

        .service-units-table-wrap::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.95);
            border-radius: 999px;
        }

        .service-units-table-wrap::-webkit-scrollbar-thumb {
            background: rgba(107, 114, 128, 0.5);
            border-radius: 999px;
        }

        .service-units-table {
            min-width: 1260px;
            table-layout: auto;
        }

        .service-units-table thead th {
            background: #f4f6fa;
            color: #7f8a9d;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            white-space: nowrap;
            border-bottom: 0;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .service-units-table tbody td {
            color: #334155;
            font-size: 0.93rem;
            vertical-align: middle;
            white-space: nowrap;
            border-color: #eef2f7;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .service-units-table tbody tr:hover {
            background: #f8fafc;
        }

        .service-units-col-count {
            width: 60px;
        }

        @media (max-width: 1199.98px) {
            .service-units-filter-grid {
                grid-template-columns: minmax(150px, 0.8fr) minmax(210px, 1fr) minmax(280px, 1.4fr);
            }

            .service-units-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }

            .service-units-filter-actions .btn {
                max-width: 150px;
            }
        }

        @media (max-width: 767.98px) {
            .service-units-filter-grid {
                grid-template-columns: 1fr;
            }

            .service-units-filter-actions {
                justify-content: stretch;
            }

            .service-units-filter-actions .btn {
                max-width: none;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('serviceUnitFilterForm');
            const search = document.getElementById('serviceUnitSearch');
            const status = document.getElementById('serviceUnitStatus');
            const perPage = document.getElementById('serviceUnitPerPage');
            let timer = null;

            function submitFilter(delay = 0) {
                if (!form) return;
                clearTimeout(timer);
                timer = setTimeout(() => form.submit(), delay);
            }

            if (search) {
                search.addEventListener('input', function () {
                    submitFilter(450);
                });
            }

            if (status) {
                status.addEventListener('change', function () {
                    submitFilter(0);
                });
            }

            if (perPage) {
                perPage.addEventListener('change', function () {
                    submitFilter(0);
                });
            }
        });
    </script>
</x-app-layout>
