<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">Vehicle Assignment</h3>
            <p class="text-muted mb-0">Assignment details, driver, team, branch, and purpose.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-light">Back</a>
            <a href="{{ route('vehicle.assignments.edit', $assignment) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4">Assignment Information</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Vehicle</small>
                            <div class="fw-bold">{{ $assignment->vehicle->vehicle_code ?? '-' }}</div>
                            <div class="text-muted">{{ $assignment->vehicle->plate_number ?? 'No plate' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Status</small>
                            <div><span class="{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Driver / Custodian</small>
                            <div class="fw-bold">{{ $assignment->driver->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Branch</small>
                            <div class="fw-bold">{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Department</small>
                            <div class="fw-bold">{{ $assignment->department->name ?? $assignment->department->department_name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Project / Site</small>
                            <div class="fw-bold">{{ $assignment->project_site_text ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Start Date</small>
                            <div class="fw-bold">{{ optional($assignment->start_date)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">End Date</small>
                            <div class="fw-bold">{{ optional($assignment->end_date)->format('M d, Y') ?? 'Present' }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Purpose</small>
                            <div>{{ $assignment->purpose ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Remarks</small>
                            <div>{{ $assignment->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Team Members</h5>
                    @forelse($assignment->members as $member)
                        <div class="border-bottom py-2">
                            <div class="fw-bold">{{ $member->user->name ?? '-' }}</div>
                            <small class="text-muted">{{ $member->role_in_vehicle ?? 'Team Member' }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No team members assigned.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
