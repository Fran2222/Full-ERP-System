<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('vehicle.partials.nav')

        @if(session('success'))
            <div class="alert alert-success rounded-3">{{ session('success') }}</div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1">Vehicle Management</h3>
                <p class="text-secondary mb-0">Track company vehicles, assignments, maintenance, and renewals.</p>
            </div>
            <a href="{{ route('vehicle.setup.index') }}" class="btn btn-primary rounded-pill px-4">
                Setup Vehicle Module
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Total Vehicles</p>
                        <h2 class="fw-bold mb-0">{{ $totalVehicles }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Available</p>
                        <h2 class="fw-bold mb-0">{{ $availableCount }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Assigned / In Use</p>
                        <h2 class="fw-bold mb-0">{{ $assignedCount }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Maintenance / Repair</p>
                        <h2 class="fw-bold mb-0">{{ $maintenanceCount }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card rounded-4 border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                        <h5 class="fw-bold mb-0">Status Summary</h5>
                    </div>
                    <div class="card-body px-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-end">Vehicles</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($statusCounts as $status)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $status->color ?: 'secondary' }}">
                                                {{ $status->name }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold">{{ $status->vehicles_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary py-4">No statuses configured.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card rounded-4 border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                        <h5 class="fw-bold mb-0">Documents Expiring Soon</h5>
                    </div>
                    <div class="card-body px-4">
                        @forelse($expiringDocuments as $document)
                            <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                                <div>
                                    <div class="fw-semibold">{{ optional($document->vehicle)->vehicle_code ?? 'Vehicle' }}</div>
                                    <small class="text-secondary">{{ $document->document_type }}</small>
                                </div>
                                <span class="badge bg-warning text-dark">{{ optional($document->expiry_date)->format('M d, Y') }}</span>
                            </div>
                        @empty
                            <p class="text-secondary mb-0">No document renewals due within 30 days.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card rounded-4 border-0 shadow-sm">
                    <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                        <h5 class="fw-bold mb-0">Recent Maintenance</h5>
                    </div>
                    <div class="card-body px-4">
                        @forelse($recentMaintenance as $record)
                            <div class="border-bottom py-2">
                                <div class="fw-semibold">{{ optional($record->vehicle)->vehicle_code ?? 'Vehicle' }}</div>
                                <small class="text-secondary">
                                    {{ optional($record->type)->name ?? 'Maintenance' }}
                                    · {{ optional($record->maintenance_date)->format('M d, Y') }}
                                </small>
                            </div>
                        @empty
                            <p class="text-secondary mb-0">No maintenance records yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
