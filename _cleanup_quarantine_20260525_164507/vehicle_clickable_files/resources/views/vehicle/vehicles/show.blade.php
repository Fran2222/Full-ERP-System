@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    @include('vehicle.partials.nav')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">{{ $vehicle->display_name }}</h4>
            <p class="text-muted mb-0">Vehicle information, assignment status, maintenance, and documents.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vehicle.vehicles.index') }}" class="btn btn-light">Back</a>
            <a href="{{ route('vehicle.vehicles.edit', $vehicle) }}" class="btn btn-primary">Edit Vehicle</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    @if($vehicle->photo_url)
                        <img src="{{ $vehicle->photo_url }}" alt="Vehicle photo" class="img-fluid rounded-4 mb-3">
                    @else
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center mb-3" style="height:220px;">
                            <span class="display-5 fw-bold text-primary">Vehicle</span>
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge" style="background: {{ $vehicle->status->color ?? '#6c757d' }};">
                            {{ $vehicle->status->name ?? 'No Status' }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">Plate Number</span>
                        <strong>{{ $vehicle->plate_number ?: '-' }}</strong>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">Current Odometer</span>
                        <strong>{{ number_format((int) ($vehicle->current_odometer ?? 0)) }}</strong>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted">Assigned Branch</span>
                        <strong>{{ $vehicle->branch->name ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Vehicle Details</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Vehicle Code</div>
                            <div class="fw-semibold">{{ $vehicle->vehicle_code }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Type</div>
                            <div class="fw-semibold">{{ $vehicle->type->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Default Driver</div>
                            <div class="fw-semibold">{{ $vehicle->defaultDriver->full_name ?? $vehicle->defaultDriver->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Brand</div>
                            <div class="fw-semibold">{{ $vehicle->brand ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Model</div>
                            <div class="fw-semibold">{{ $vehicle->model ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Year</div>
                            <div class="fw-semibold">{{ $vehicle->year_model ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Color</div>
                            <div class="fw-semibold">{{ $vehicle->color ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Fuel Type</div>
                            <div class="fw-semibold">{{ $vehicle->fuel_type ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Acquisition Date</div>
                            <div class="fw-semibold">{{ $vehicle->acquisition_date ? $vehicle->acquisition_date->format('M d, Y') : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Engine No.</div>
                            <div class="fw-semibold">{{ $vehicle->engine_no ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Chassis No.</div>
                            <div class="fw-semibold">{{ $vehicle->chassis_no ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Remarks</div>
                            <div>{{ $vehicle->remarks ?: 'No remarks.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">Current Assignment</h5>
                            @if($vehicle->activeAssignment)
                                <div class="fw-semibold">{{ $vehicle->activeAssignment->driver->full_name ?? 'No driver name' }}</div>
                                <div class="text-muted small">{{ $vehicle->activeAssignment->purpose ?: 'No purpose listed.' }}</div>
                                <div class="text-muted small mt-2">Started: {{ optional($vehicle->activeAssignment->start_date)->format('M d, Y') ?: '-' }}</div>
                            @else
                                <div class="text-muted">No active assignment.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">Recent Maintenance</h5>
                            @forelse($vehicle->maintenanceRecords->sortByDesc('maintenance_date')->take(3) as $record)
                                <div class="mb-2">
                                    <div class="fw-semibold">{{ $record->type->name ?? 'Maintenance' }}</div>
                                    <div class="text-muted small">{{ optional($record->maintenance_date)->format('M d, Y') ?: '-' }} • ₱{{ number_format((float) ($record->total_cost ?? 0), 2) }}</div>
                                </div>
                            @empty
                                <div class="text-muted">No maintenance records yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <h5 class="mb-3">Documents / Renewals</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Document No.</th>
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->documents->sortBy('expiry_date') as $document)
                            <tr>
                                <td>{{ $document->document_type }}</td>
                                <td>{{ $document->document_no ?: '-' }}</td>
                                <td>{{ optional($document->issue_date)->format('M d, Y') ?: '-' }}</td>
                                <td>{{ optional($document->expiry_date)->format('M d, Y') ?: '-' }}</td>
                                <td>{{ ucfirst($document->status ?? 'active') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No documents yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
