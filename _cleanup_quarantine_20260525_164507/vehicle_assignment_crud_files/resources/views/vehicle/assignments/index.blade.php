<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Vehicle Assignments</h3>
            <p class="text-muted mb-0">Track which team, driver, branch, or site is using each vehicle.</p>
        </div>
        <a href="{{ route('vehicle.assignments.create') }}" class="btn btn-primary">
            Add Assignment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('vehicle.assignments.index') }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Vehicle, driver, site, purpose...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ (string)($filters['vehicle_id'] ?? '') === (string)$vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_code }} {{ $vehicle->plate_number ? ' - ' . $vehicle->plate_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>
                                {{ ucwords($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string)($filters['branch_id'] ?? '') === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name ?? $branch->branch_name ?? ('Branch #' . $branch->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                    <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Team Members</th>
                            <th>Branch / Site</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->vehicle->vehicle_code ?? '-' }}</strong>
                                    <div class="text-muted small">
                                        {{ $assignment->vehicle->plate_number ?? 'No plate' }}
                                    </div>
                                </td>
                                <td>{{ $assignment->driver->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $memberNames = $assignment->members
                                            ->map(fn($member) => $member->user->name ?? null)
                                            ->filter()
                                            ->values();
                                    @endphp
                                    {{ $memberNames->take(3)->join(', ') ?: '-' }}
                                    @if($memberNames->count() > 3)
                                        <span class="text-muted">+{{ $memberNames->count() - 3 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</strong>
                                    <div class="text-muted small">{{ $assignment->project_site_text ?? '-' }}</div>
                                </td>
                                <td>
                                    {{ optional($assignment->start_date)->format('M d, Y') }}
                                    <div class="text-muted small">
                                        to {{ optional($assignment->end_date)->format('M d, Y') ?? 'Present' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('vehicle.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('vehicle.assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="POST" action="{{ route('vehicle.assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No vehicle assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $assignments->links() }}
        </div>
    </div>
</x-app-layout>
