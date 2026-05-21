<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        @php
            $statusClass = match($transfer->status) {
                'draft' => 'bg-primary-subtle text-primary',
                'in_transit' => 'bg-warning-subtle text-warning',
                'received' => 'bg-success-subtle text-success',
                'cancelled' => 'bg-danger-subtle text-danger',
                default => 'bg-secondary-subtle text-secondary',
            };
        @endphp

        <div class="card rounded-4 border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">
                            Transfer {{ $transfer->transfer_no }}
                            <span class="badge rounded-pill {{ $statusClass }} ms-2">{{ $transfer->status_label }}</span>
                        </h4>
                        <p class="text-secondary mb-0">Transfer order details, item list, serial numbers, and status timeline.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('warehouse.transfer') }}" class="btn btn-outline-secondary">Back</a>
                        @if($canCancel)
                            <form method="POST" action="{{ route('warehouse.transfer.cancel', $transfer->id) }}" onsubmit="return confirm('Cancel this transfer?');">@csrf @method('PATCH')<button class="btn btn-outline-danger" type="submit">Cancel</button></form>
                        @endif
                        @if($canDispatch)
                            <form method="POST" action="{{ route('warehouse.transfer.dispatch', $transfer->id) }}" onsubmit="return confirm('Dispatch this transfer? Source stock will be deducted.');">@csrf @method('PATCH')<button class="btn btn-warning" type="submit">Dispatch / Deliver</button></form>
                        @endif
                        @if($canReceive)
                            <form method="POST" action="{{ route('warehouse.transfer.receive', $transfer->id) }}" onsubmit="return confirm('Receive this transfer? Destination stock will be added.');">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Receive Transfer</button></form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-1">From</div>
                            <h5 class="fw-bold mb-1">{{ $transfer->fromLocation?->location_name ?? $transfer->fromLocation?->name ?? '-' }}</h5>
                            <div class="text-secondary">{{ $transfer->from_branch_name }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-1">To</div>
                            <h5 class="fw-bold mb-1">{{ $transfer->toLocation?->location_name ?? $transfer->toLocation?->name ?? '-' }}</h5>
                            <div class="text-secondary">{{ $transfer->to_branch_name }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Transfer Date</div><div class="fw-semibold">{{ optional($transfer->transfer_date)->format('M d, Y') ?: '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Created By</div><div class="fw-semibold">{{ $transfer->creator?->name ?? '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Dispatched At</div><div class="fw-semibold">{{ optional($transfer->dispatched_at)->format('M d, Y h:i A') ?: '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Received At</div><div class="fw-semibold">{{ optional($transfer->received_at)->format('M d, Y h:i A') ?: '-' }}</div></div></div>
                </div>

                <h5 class="fw-bold mb-3">Transfer Items</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Item</th><th class="text-end">Qty</th><th>Serial No(s).</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @foreach($transfer->items as $line)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $line->item?->display_name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $line->item?->display_code ?? '-' }}</div>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format((float) $line->quantity, 2) }}</td>
                                    <td>
                                        @forelse($line->serials as $serial)
                                            <span class="badge bg-light text-primary border me-1 mb-1">{{ $serial->serial_number }}</span>
                                        @empty
                                            <span class="text-secondary">-</span>
                                        @endforelse
                                    </td>
                                    <td class="text-secondary">{{ $line->remarks ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border rounded-4 p-3">
                    <h6 class="fw-bold mb-2">Remarks</h6>
                    <p class="mb-0 text-secondary">{{ $transfer->remarks ?: 'No remarks added.' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
