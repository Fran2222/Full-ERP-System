<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">
                            Service Unit {{ $serviceUnit->borrow_no }}
                            @php
                                $status = strtolower($serviceUnit->status ?? 'active');
                                $badgeClass = match($status) {
                                    'active' => $serviceUnit->is_overdue ? 'bg-danger' : 'bg-primary',
                                    'returned' => 'bg-success',
                                    'for_repair', 'damaged' => 'bg-warning text-dark',
                                    'lost' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} ms-2">{{ $serviceUnit->is_overdue ? 'Overdue' : \Illuminate\Support\Str::headline($status) }}</span>
                        </h4>
                        <p class="text-secondary mb-0">Borrowed / issued service unit details.</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($serviceUnit->status === 'active')
                            <a href="{{ route('warehouse.service-units.return', $serviceUnit) }}" class="btn btn-success">Return Unit</a>
                        @endif
                        <a href="{{ route('warehouse.service-units.index') }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Employee</div>
                            <h5 class="fw-bold mb-1">{{ $serviceUnit->employee?->full_name ?: trim(($serviceUnit->employee?->first_name ?? '') . ' ' . ($serviceUnit->employee?->last_name ?? '')) ?: '-' }}</h5>
                            <div class="text-secondary small">{{ $serviceUnit->employee?->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Item / Serial</div>
                            <h5 class="fw-bold mb-1">{{ $serviceUnit->item?->name ?? $serviceUnit->item?->item_name ?? '-' }}</h5>
                            <div class="text-secondary small">{{ $serviceUnit->serial?->serial_number ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Location</div>
                            <h5 class="fw-bold mb-1">{{ $serviceUnit->location?->location_name ?? $serviceUnit->location?->name ?? '-' }}</h5>
                            <div class="text-secondary small">{{ $serviceUnit->branch?->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr><th style="width: 260px;">Borrowed Date</th><td>{{ optional($serviceUnit->borrowed_at)->format('M d, Y') }}</td></tr>
                            <tr><th>Expected Return Date</th><td>{{ optional($serviceUnit->expected_return_at)->format('M d, Y') ?? '-' }}</td></tr>
                            <tr><th>Returned At</th><td>{{ optional($serviceUnit->returned_at)->format('M d, Y h:i A') ?? '-' }}</td></tr>
                            <tr><th>Condition Out</th><td>{{ $serviceUnit->condition_out ?? '-' }}</td></tr>
                            <tr><th>Condition In</th><td>{{ $serviceUnit->condition_in ?? '-' }}</td></tr>
                            <tr><th>Released By</th><td>{{ $serviceUnit->releasedBy?->full_name ?: $serviceUnit->releasedBy?->email ?: '-' }}</td></tr>
                            <tr><th>Received By</th><td>{{ $serviceUnit->receivedBy?->full_name ?: $serviceUnit->receivedBy?->email ?: '-' }}</td></tr>
                            <tr><th>Purpose</th><td>{{ $serviceUnit->purpose ?: '-' }}</td></tr>
                            <tr><th>Remarks</th><td>{!! nl2br(e($serviceUnit->remarks ?: '-')) !!}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
