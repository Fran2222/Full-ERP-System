<x-app-layout>

    <style>
    .travel-order-document {
        color: #111827;
        line-height: 1.7;
        position: relative;
        padding-bottom: 92px !important;
    }

    .travel-order-document p,
    .travel-order-document li,
    .travel-order-document div {
        font-size: 15px;
    }

    .travel-order-document strong {
        font-weight: 700;
    }

    .travel-order-record-box {
        position: absolute;
        left: 18px;
        bottom: 18px;
        width: 185px;
        padding: 5px 7px;
        border: none;
        border-radius: 0;
        background: transparent;
        line-height: 1.15;
    }

    .travel-order-record-box div {
        font-size: 9px;
        color: #111827;
        display: flex;
        justify-content: space-between;
        gap: 4px;
    }

    .travel-order-record-box span:first-child {
        font-weight: 700;
        white-space: nowrap;
    }

    .travel-order-record-box span:last-child {
        text-align: right;
        font-weight: 600;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .travel-order-record-box {
            position: static;
            width: 100%;
            margin: 0 0 18px 0;
        }
    }
    </style>

    <div class="container-fluid content-inner mt-n5 py-0">

        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card rounded-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Travel Order Details</h4>
                    <p class="text-secondary mb-0">
                        Status:
                        <span class="badge rounded-pill {{ $travelOrder->status_badge_class }}">
                            {{ ucfirst($travelOrder->status) }}
                        </span>
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('hr.travel-orders.print', $travelOrder) }}"
                       target="_blank"
                       class="btn btn-dark btn-sm rounded-3">
                        Print
                    </a>

                    <a href="{{ route('hr.travel-orders.index') }}"
                       class="btn btn-light btn-sm rounded-3">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">

            <div class="border rounded-4 p-4 bg-white travel-order-document">
                <div class="travel-order-record-box">
                    <div>
                        <span>Document No.:</span>
                        <span>WMC-HR-TO-001</span>
                    </div>
                    <div>
                        <span>Revision No.:</span>
                        <span>00</span>
                    </div>
                    <div>
                        <span>Effective Date:</span>
                        <span>{{ $travelOrder->document_effective_date }}</span>
                    </div>
                    <div>
                        <span>Travel Order No.:</span>
                        <span>{{ $travelOrder->travel_order_number }}</span>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <h5 class="mb-1 fw-bold text-uppercase">
                        WIZMASTER COMPUTER SALES AND SERVICES CORPORATION
                    </h5>
                    <div class="fw-semibold">Iligan City</div>
                    <h4 class="mt-4 mb-0 fw-bold text-uppercase">TRAVEL ORDER</h4>
                </div>

                <div class="mb-4">
                    <strong>Date:</strong>
                    {{ $travelOrder->order_date?->format('F d, Y') ?? 'N/A' }}
                </div>

                <div class="mb-4">
                    <strong>Employees Authorized to Travel:</strong>

                    <ol class="mt-2 mb-0">
                        @foreach(($travelOrder->employees_authorized ?? []) as $employee)
                            <li>{{ $employee }}</li>
                        @endforeach
                    </ol>
                </div>

                <div class="mb-4">
                    <strong>Travel Date(s):</strong>
                    {{ $travelOrder->travel_start_date?->format('F d, Y') }}
                    @if(
                        $travelOrder->travel_end_date &&
                        $travelOrder->travel_start_date &&
                        ! $travelOrder->travel_start_date->isSameDay($travelOrder->travel_end_date)
                    )
                        - {{ $travelOrder->travel_end_date?->format('F d, Y') }}
                    @endif
                </div>

                <div class="mb-3">
                    <p class="mb-2">
                        <strong>1.</strong>
                        You are hereby authorized to travel to
                        <strong>{{ $travelOrder->destination }}</strong>
                        for the following purpose:
                    </p>

                    <ol type="a" class="mb-0 ps-5">
                        <li>{{ $travelOrder->purpose_a }}</li>
                    </ol>
                </div>

                <p class="mb-3">
                    <strong>2.</strong>
                    A brief Report of Accomplishment shall be submitted to this Office immediately upon your return.
                </p>

                <p class="mb-3">
                    <strong>3.</strong>
                    Travelling expenses and per diem incurred in connection with your travel shall be chargeable
                    against the Wizmaster funds, subject to its availability and the usual accounting &amp; auditing
                    rules &amp; regulations (please attach receipts upon liquidation).
                </p>

                <p class="mb-4">Please be guided accordingly.</p>

                @if($travelOrder->remarks)
                    <div class="alert alert-light border rounded-3 mt-4">
                        <strong>Remarks:</strong>
                        <div>{{ $travelOrder->remarks }}</div>
                    </div>
                @endif

                <div class="mt-5">
                    <p class="mb-5">Prepared/Approved by:</p>
                    <strong>Manager</strong>
                </div>
            </div>

                @if($canManageTravelOrders && $travelOrder->status === 'pending')
                    <div class="border-top mt-4 pt-4">
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('hr.travel-orders.approve', $travelOrder) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-success rounded-3">
                                    Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('hr.travel-orders.reject', $travelOrder) }}" class="d-flex gap-2">
                                @csrf
                                @method('PATCH')

                                <input type="text"
                                       name="rejection_reason"
                                       class="form-control"
                                       placeholder="Reason for rejection optional"
                                       style="min-width: 260px;">

                                <button type="submit" class="btn btn-danger rounded-3">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if($travelOrder->reviewer)
                    <div class="alert alert-light border rounded-3 mt-4 mb-0">
                        <strong>Reviewed by:</strong>
                        {{ $travelOrder->reviewer?->full_name ?? $travelOrder->reviewer?->name ?? 'N/A' }}
                        <br>
                        <strong>Reviewed at:</strong>
                        {{ $travelOrder->reviewed_at?->format('F d, Y h:i A') ?? 'N/A' }}

                        @if($travelOrder->rejection_reason)
                            <br>
                            <strong>Rejection reason:</strong>
                            {{ $travelOrder->rejection_reason }}
                        @endif
                    </div>
                @endif

            </div>
        </div>

    </div>
</x-app-layout>