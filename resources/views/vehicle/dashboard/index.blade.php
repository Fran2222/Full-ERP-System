<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'dashboard'])

    <style>
        .vm-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(31,45,61,.06);
        }

        .vm-stat {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
        }

        .vm-table th {
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            background: #f5f7fb;
            border-bottom: 0;
            white-space: nowrap;
        }

        .vm-table td {
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .vm-code {
            color: #2f4dfd;
            font-weight: 700;
            text-decoration: none;
        }

        .vm-muted {
            color: #7b8794;
            font-size: 12px;
        }

        .vm-quick-btn {
            border-radius: 10px;
            min-height: 42px;
        }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h3 class="mb-1">Vehicle Management</h3>
            <p class="text-muted mb-0">Track company vehicles, assignments, maintenance, and document renewals.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('vehicle.vehicles.create') }}" class="btn btn-primary vm-quick-btn">Add Vehicle</a>
            <a href="{{ route('vehicle.assignments.create') }}" class="btn btn-outline-primary vm-quick-btn">Assign Vehicle</a>
            <a href="{{ route('vehicle.maintenance.create') }}" class="btn btn-outline-primary vm-quick-btn">Add Maintenance</a>
            <a href="{{ route('vehicle.documents.create') }}" class="btn btn-outline-primary vm-quick-btn">Add Document</a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('vehicle.vehicles.index') }}" class="text-decoration-none text-dark">
                <div class="card vm-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Vehicles</p>
                        <div class="vm-stat">{{ number_format($totalVehicles) }}</div>
                        <small class="text-muted">Company vehicle records</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('vehicle.vehicles.index', ['status' => 'available']) }}" class="text-decoration-none text-dark">
                <div class="card vm-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Available</p>
                        <div class="vm-stat">{{ number_format($availableVehicles) }}</div>
                        <small class="text-muted">Ready for assignment</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('vehicle.assignments.index') }}" class="text-decoration-none text-dark">
                <div class="card vm-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Assigned / In Use</p>
                        <div class="vm-stat">{{ number_format($assignedVehicles ?: $activeAssignments->count()) }}</div>
                        <small class="text-muted">Currently assigned vehicles</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('vehicle.maintenance.index') }}" class="text-decoration-none text-dark">
                <div class="card vm-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Maintenance / Repair</p>
                        <div class="vm-stat">{{ number_format($maintenanceVehicles) }}</div>
                        <small class="text-muted">Under maintenance or repair</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-7 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Vehicle Status Summary</h5>
                            <p class="text-muted mb-0">Current vehicle count by status.</p>
                        </div>
                        <a href="{{ route('vehicle.vehicles.index') }}" class="btn btn-sm btn-outline-primary">View Vehicles</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table vm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-end">Vehicles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statusCounts as $row)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $row->label ?? 'No Status' }}</span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($row->total ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No status data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Documents Expiring Soon</h5>
                            <p class="text-muted mb-0">Renewals due within 30 days.</p>
                        </div>
                        <a href="{{ route('vehicle.documents.index', ['expiry_filter' => '30_days']) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>

                    @forelse($expiringDocuments as $document)
                        <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                            <div>
                                <div class="fw-bold">{{ $document->document_type ?? '-' }}</div>
                                <div class="vm-muted">
                                    {{ $document->vehicle->vehicle_code ?? '-' }}
                                    {{ $document->vehicle->plate_number ? ' / ' . $document->vehicle->plate_number : '' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ optional($document->expiry_date)->format('M d, Y') ?? '-' }}</div>
                                <span class="{{ $document->expiry_badge_class ?? 'badge bg-warning' }}">
                                    {{ $document->expiry_status_label ?? 'Due Soon' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No document renewals due within 30 days.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-7 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Active Assignments</h5>
                            <p class="text-muted mb-0">Recently assigned vehicles and teams.</p>
                        </div>
                        <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table vm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Branch / Site</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeAssignments as $assignment)
                                    @php
                                        $driver = $assignment->driver;
                                        $driverName = $driver
                                            ? (trim(($driver->last_name ?? '') . ', ' . ($driver->first_name ?? '')) ?: ($driver->email ?? 'User #' . $driver->id))
                                            : '-';
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('vehicle.assignments.show', $assignment) }}" class="vm-code">{{ $assignment->vehicle->vehicle_code ?? '-' }}</a>
                                            <div class="vm-muted">{{ $assignment->vehicle->plate_number ?? 'No plate' }}</div>
                                        </td>
                                        <td>{{ $driverName }}</td>
                                        <td>
                                            <strong>{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</strong>
                                            <div class="vm-muted">{{ $assignment->project_site_text ?? '-' }}</div>
                                        </td>
                                        <td>{{ optional($assignment->start_date)->format('M d, Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No active assignments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Recent Maintenance</h5>
                            <p class="text-muted mb-0">Latest PMS and repair records.</p>
                        </div>
                        <a href="{{ route('vehicle.maintenance.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>

                    @forelse($recentMaintenance as $maintenance)
                        <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                            <div>
                                <div class="fw-bold">{{ $maintenance->maintenanceType->name ?? 'Maintenance' }}</div>
                                <div class="vm-muted">
                                    {{ $maintenance->vehicle->vehicle_code ?? '-' }}
                                    {{ $maintenance->vehicle->plate_number ? ' / ' . $maintenance->vehicle->plate_number : '' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format((float) ($maintenance->total_cost ?? 0), 2) }}</div>
                                <div class="vm-muted">{{ optional($maintenance->maintenance_date)->format('M d, Y') ?? '-' }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No maintenance records yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
