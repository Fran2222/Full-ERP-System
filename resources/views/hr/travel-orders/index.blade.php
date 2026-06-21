<x-app-layout>
    <style>
        .travel-orders-table {
            width: 100%;
            table-layout: fixed;
        }

        .travel-orders-table th,
        .travel-orders-table td {
            vertical-align: middle;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .travel-orders-table th {
            font-size: 0.84rem;
            white-space: nowrap;
        }

        .travel-orders-table td {
            font-size: 0.92rem;
        }

        .travel-orders-table th:nth-child(1),
        .travel-orders-table td:nth-child(1) {
            width: 6%;
            text-align: center;
            white-space: nowrap;
        }

        @if($canManageTravelOrders)
            .travel-orders-table th:nth-child(2),
            .travel-orders-table td:nth-child(2) {
                width: 20%;
                white-space: normal;
            }

            .travel-orders-table th:nth-child(3),
            .travel-orders-table td:nth-child(3) {
                width: 19%;
                white-space: normal;
            }

            .travel-orders-table th:nth-child(4),
            .travel-orders-table td:nth-child(4) {
                width: 22%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(5),
            .travel-orders-table td:nth-child(5) {
                width: 10%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(6),
            .travel-orders-table td:nth-child(6) {
                width: 13%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(7),
            .travel-orders-table td:nth-child(7) {
                width: 10%;
                text-align: center;
                white-space: nowrap;
            }
        @else
            .travel-orders-table th:nth-child(2),
            .travel-orders-table td:nth-child(2) {
                width: 28%;
                white-space: normal;
            }

            .travel-orders-table th:nth-child(3),
            .travel-orders-table td:nth-child(3) {
                width: 25%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(4),
            .travel-orders-table td:nth-child(4) {
                width: 13%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(5),
            .travel-orders-table td:nth-child(5) {
                width: 18%;
                text-align: center;
                white-space: nowrap;
            }

            .travel-orders-table th:nth-child(6),
            .travel-orders-table td:nth-child(6) {
                width: 10%;
                text-align: center;
                white-space: nowrap;
            }
        @endif

        .travel-orders-table td div,
        .travel-orders-table td small {
            max-width: 100%;
        }

        .travel-orders-table td small {
            display: block;
            overflow-wrap: anywhere;
            line-height: 1.25;
        }

        .travel-order-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .travel-order-icon-btn {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 12px !important;
            line-height: 1 !important;
            flex: 0 0 32px;
        }

        .travel-order-icon-btn svg {
            width: 16px !important;
            height: 16px !important;
            display: block !important;
            flex-shrink: 0 !important;
        }

        .travel-order-icon-btn svg path,
        .travel-order-icon-btn svg circle {
            stroke: currentColor !important;
            stroke-width: 2 !important;
            fill: none !important;
        }

        @media (max-width: 1199.98px) {
            .travel-orders-table {
                min-width: 900px;
            }
        }
    </style>
    
    <div class="container-fluid content-inner mt-n5 py-0">

        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Travel Orders</h4>
                    <p class="text-secondary mb-0">
                        {{ $canManageTravelOrders ? 'Review employee travel order requests.' : 'Submit and monitor your travel order requests.' }}
                    </p>
                </div>

                @can('hr.view')
                    <a href="{{ route('hr.travel-orders.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line me-1"></i> Apply Travel Order
                    </a>
                @endcan
            </div>

            <div class="card-body">

                <div class="alert alert-info rounded-3 mb-4">
                    <strong>Note:</strong>
                    For proper HR and Accounting preparation, especially for fund processing, please submit your Travel Order request at least
                    <strong>2–3 days before your travel date.</strong>
                </div>

                <form method="GET" action="{{ route('hr.travel-orders.index') }}" class="mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Status</label>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 travel-orders-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                @if($canManageTravelOrders)
                                    <th>Requester</th>
                                @endif
                                <th>Destination</th>
                                <th>Travel Date</th>
                                <th>Status</th>
                                <th>Attention</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($travelOrders as $travelOrder)
                                @php
                                    $startDate = $travelOrder->travel_start_date;
                                    $daysBeforeTravel = $startDate ? $today->diffInDays($startDate, false) : null;
                                    $needsAttention = $daysBeforeTravel !== null && $daysBeforeTravel >= 1 && $daysBeforeTravel <= 3;
                                @endphp

                                <tr>
                                    <td>{{ $travelOrders->firstItem() + $loop->index }}</td>

                                    @if($canManageTravelOrders)
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $travelOrder->requester?->full_name ?? $travelOrder->requester?->name ?? 'N/A' }}
                                            </div>
                                            <small class="text-secondary">{{ $travelOrder->requester?->email }}</small>
                                        </td>
                                    @endif

                                    <td>
                                        <div class="fw-semibold">{{ $travelOrder->destination }}</div>
                                    </td>

                                    <td>
                                        @if($travelOrder->travel_start_date && $travelOrder->travel_end_date)
                                            {{ $travelOrder->travel_start_date->format('M d, Y') }}
                                            -
                                            {{ $travelOrder->travel_end_date->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill {{ $travelOrder->status_badge_class }}">
                                            {{ ucfirst($travelOrder->status) }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($needsAttention)
                                            <span class="badge rounded-pill bg-info-subtle text-info">
                                                Upcoming Travel
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold">
                                                On Travel
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                    <div class="travel-order-action">
                                        <a href="{{ route('hr.travel-orders.show', $travelOrder) }}"
                                        class="btn btn-sm btn-outline-primary travel-order-icon-btn"
                                        title="View"
                                        aria-label="View Travel Order">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('hr.travel-orders.print', $travelOrder) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-dark travel-order-icon-btn"
                                        title="Print"
                                        aria-label="Print Travel Order">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M7 8V3h10v5" />
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M7 17H5a2 2 0 0 1-2-2v-4a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v4a2 2 0 0 1-2 2h-2" />
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M7 14h10v7H7z" />
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M17 11h.01" />
                                            </svg>
                                        </a>
                                    </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageTravelOrders ? 7 : 6 }}" class="text-center text-secondary py-4">
                                        No travel orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $travelOrders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>