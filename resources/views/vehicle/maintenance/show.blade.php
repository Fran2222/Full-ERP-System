<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'maintenance'])

    <style>
        .vm-card { border:0; border-radius:16px; box-shadow:0 6px 18px rgba(31,45,61,.06); }
        .vm-label { color:#7b8794; font-size:12px; margin-bottom:3px; }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Maintenance / Repair Details</h3>
            <p class="text-muted mb-0">Vehicle maintenance record, cost, attachment, and next maintenance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vehicle.maintenance.index') }}" class="btn btn-light">Back</a>
            <a href="{{ route('vehicle.maintenance.edit', $maintenance) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card vm-card">
                <div class="card-body">
                    <h5 class="mb-4">Record Information</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="vm-label">Vehicle</div>
                            <div class="fw-bold">{{ $maintenance->vehicle->vehicle_code ?? '-' }}</div>
                            <div class="text-muted">{{ $maintenance->vehicle->plate_number ?? 'No plate' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Status</div>
                            <span class="{{ $maintenance->status_badge_class }}">{{ $maintenance->status_label }}</span>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Maintenance Type</div>
                            <div class="fw-bold">{{ $maintenance->maintenanceType->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Maintenance Date</div>
                            <div class="fw-bold">{{ optional($maintenance->maintenance_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Odometer</div>
                            <div class="fw-bold">{{ $maintenance->odometer ? number_format($maintenance->odometer) . ' km' : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="vm-label">Shop / Mechanic</div>
                            <div class="fw-bold">{{ $maintenance->shop_or_mechanic ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="vm-label">Issue / Concern</div>
                            <div>{{ $maintenance->issue_or_concern ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="vm-label">Action Taken</div>
                            <div>{{ $maintenance->action_taken ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="vm-label">Parts Replaced</div>
                            <div>{{ $maintenance->parts_replaced ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="vm-label">Remarks</div>
                            <div>{{ $maintenance->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card vm-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Cost</h5>
                    <div class="d-flex justify-content-between mb-2"><span>Labor</span><strong>{{ number_format((float)$maintenance->labor_cost, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Parts</span><strong>{{ number_format((float)$maintenance->parts_cost, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Other</span><strong>{{ number_format((float)$maintenance->other_cost, 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total</span><strong>{{ number_format((float)$maintenance->total_cost, 2) }}</strong></div>
                </div>
            </div>

            <div class="card vm-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Next Maintenance</h5>
                    <div class="vm-label">Date</div>
                    <div class="fw-bold mb-3">{{ optional($maintenance->next_maintenance_date)->format('M d, Y') ?? '-' }}</div>
                    <div class="vm-label">Odometer</div>
                    <div class="fw-bold">{{ $maintenance->next_maintenance_odometer ? number_format($maintenance->next_maintenance_odometer) . ' km' : '-' }}</div>
                </div>
            </div>

            <div class="card vm-card">
                <div class="card-body">
                    <h5 class="mb-3">Attachment</h5>
                    @if(!empty($maintenance->attachment_path))
                        <a href="{{ asset('storage/' . $maintenance->attachment_path) }}" target="_blank" class="btn btn-outline-primary">Open Attachment</a>
                    @else
                        <p class="text-muted mb-0">No attachment uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
