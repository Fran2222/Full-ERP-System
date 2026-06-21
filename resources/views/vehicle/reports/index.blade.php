<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'reports'])

    <style>
        .vm-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(31,45,61,.06);
        }

        .vm-stat-value {
            font-size: 28px;
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

        .vm-muted {
            color: #7b8794;
            font-size: 12px;
        }

        .vm-code {
            color: #2f4dfd;
            font-weight: 700;
            text-decoration: none;
        }

        .vm-report-section {
            margin-bottom: 24px;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Vehicle Reports</h3>
            <p class="text-muted mb-0">Summary of vehicles, active assignments, maintenance costs, and renewal reminders.</p>
        </div>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">Print Report</button>
    </div>

    <div class="row vm-report-section">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Vehicles</p>
                    <div class="vm-stat-value">{{ number_format($totalVehicles) }}</div>
                    <small class="text-muted">Company vehicle records</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Active Assignments</p>
                    <div class="vm-stat-value">{{ number_format($activeAssignments->count()) }}</div>
                    <small class="text-muted">Currently assigned vehicles</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Maintenance This Month</p>
                    <div class="vm-stat-value">{{ number_format((float) $maintenanceCostSummary['thisMonth'], 2) }}</div>
                    <small class="text-muted">Current month cost</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Expiring Documents</p>
                    <div class="vm-stat-value">{{ number_format($documentsExpiring->count()) }}</div>
                    <small class="text-muted">Due within 60 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row vm-report-section">
        <div class="col-lg-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Vehicle Status Summary</h5>
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
                                        <td>{{ $row->label ?? '-' }}</td>
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

        <div class="col-lg-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Maintenance Cost Summary</h5>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>This Month</span>
                        <strong>{{ number_format((float) $maintenanceCostSummary['thisMonth'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>This Year</span>
                        <strong>{{ number_format((float) $maintenanceCostSummary['thisYear'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>All Time</span>
                        <strong>{{ number_format((float) $maintenanceCostSummary['allTime'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card vm-card vm-report-section">
        <div class="card-body">
            <h5 class="mb-3">Active Assignments</h5>
            <div class="table-responsive">
                <table class="table vm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Team Members</th>
                            <th>Branch / Site</th>
                            <th>Date Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeAssignments as $assignment)
                            @php
                                $driver = $assignment->driver;
                                $driverName = $driver
                                    ? (trim(($driver->last_name ?? '') . ', ' . ($driver->first_name ?? '')) ?: ($driver->email ?? 'User #' . $driver->id))
                                    : '-';

                                $members = $assignment->members
                                    ->map(function ($member) {
                                        $user = $member->user;
                                        if (!$user) return null;
                                        return trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '')) ?: ($user->email ?? null);
                                    })
                                    ->filter()
                                    ->values();
                            @endphp
                            <tr>
                                <td>
                                    <span class="vm-code">{{ $assignment->vehicle->vehicle_code ?? '-' }}</span>
                                    <div class="vm-muted">{{ $assignment->vehicle->plate_number ?? 'No plate' }}</div>
                                </td>
                                <td>{{ $driverName }}</td>
                                <td>
                                    {{ $members->take(3)->join(', ') ?: '-' }}
                                    @if($members->count() > 3)
                                        <span class="vm-muted">+{{ $members->count() - 3 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</strong>
                                    <div class="vm-muted">{{ $assignment->project_site_text ?? '-' }}</div>
                                </td>
                                <td>{{ optional($assignment->start_date)->format('M d, Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row vm-report-section">
        <div class="col-lg-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Documents Expiring Soon</h5>
                    <div class="table-responsive">
                        <table class="table vm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Document</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentsExpiring as $document)
                                    <tr>
                                        <td>
                                            <span class="vm-code">{{ $document->vehicle->vehicle_code ?? '-' }}</span>
                                            <div class="vm-muted">{{ $document->vehicle->plate_number ?? 'No plate' }}</div>
                                        </td>
                                        <td>{{ $document->document_type ?? '-' }}</td>
                                        <td>
                                            {{ optional($document->expiry_date)->format('M d, Y') ?? '-' }}
                                            <div class="vm-muted">{{ $document->document_no ?? '' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No documents expiring within 60 days.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card vm-card h-100">
                <div class="card-body">
                    <h5 class="mb-3">Maintenance Cost by Vehicle</h5>
                    <div class="table-responsive">
                        <table class="table vm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th class="text-end">Records</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($maintenanceByVehicle as $row)
                                    <tr>
                                        <td>
                                            <span class="vm-code">{{ $row->vehicle_code ?? '-' }}</span>
                                            <div class="vm-muted">{{ $row->plate_number ?? 'No plate' }}</div>
                                        </td>
                                        <td class="text-end">{{ number_format($row->records_count ?? 0) }}</td>
                                        <td class="text-end fw-bold">{{ number_format((float) ($row->total_cost ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No maintenance cost data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
