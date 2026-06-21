<x-app-layout :assets="$assets ?? []">
<div class="container-fluid py-4">
    @include('vehicle.partials.nav')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Vehicles</h4>
            <p class="text-muted mb-0">Company vehicles, plate numbers, assigned branch, status, and current odometer.</p>
        </div>
        <a href="{{ route('vehicle.vehicles.create') }}" class="btn btn-primary">
            Add Vehicle
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label text-muted small">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, plate, brand, model...">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label text-muted small">Type</label>
                    <select name="vehicle_type_id" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected((string) request('vehicle_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label text-muted small">Status</label>
                    <select name="status_id" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected((string) request('status_id') === (string) $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label text-muted small">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                    <a href="{{ route('vehicle.vehicles.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Plate No.</th>
                            <th>Type</th>
                            <th>Branch</th>
                            <th>Default Driver</th>
                            <th>Odometer</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($vehicle->photo_url)
                                            <img src="{{ $vehicle->photo_url }}" alt="Vehicle photo" style="width:44px;height:44px;object-fit:cover;border-radius:12px;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;">
                                                <span class="fw-bold text-primary">V</span>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('vehicle.vehicles.show', $vehicle) }}" class="fw-semibold text-primary">
                                                {{ $vehicle->vehicle_code }}
                                            </a>
                                            <div class="small text-muted">{{ trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) ?: 'No brand/model' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $vehicle->plate_number ?: '-' }}</td>
                                <td>{{ $vehicle->type->name ?? '-' }}</td>
                                <td>{{ $vehicle->branch->name ?? '-' }}</td>
                                <td>{{ $vehicle->defaultDriver->full_name ?? $vehicle->defaultDriver->name ?? '-' }}</td>
                                <td>{{ number_format((int) ($vehicle->current_odometer ?? 0)) }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $vehicle->status->color ?? '#6c757d' }};">
                                        {{ $vehicle->status->name ?? 'No Status' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('vehicle.vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('vehicle.vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('vehicle.vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Archive this vehicle?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No vehicles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $vehicles->links() }}
        </div>
    </div>
</div>
</x-app-layout>
