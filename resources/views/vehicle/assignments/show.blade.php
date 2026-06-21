<x-app-layout :assets="$assets ?? []">
    @include('vehicle.partials.nav', ['active' => 'assignments'])

    @php
        $displayUser = function ($user) {
            if (!$user) {
                return '-';
            }

            $middle = isset($user->middle_name) && $user->middle_name ? ' ' . substr($user->middle_name, 0, 1) . '.' : '';
            $name = trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . $middle);

            return $name ?: ($user->email ?? ('User #' . $user->id));
        };
    @endphp

    <style>
        .vm-info-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(31, 45, 61, .06);
        }

        .vm-info-label {
            color: #7b8794;
            font-size: 12px;
            margin-bottom: 3px;
        }
    </style>

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
            <div class="card vm-info-card">
                <div class="card-body">
                    <h5 class="mb-4">Assignment Information</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="vm-info-label">Vehicle</div>
                            <div class="fw-bold">{{ $assignment->vehicle->vehicle_code ?? '-' }}</div>
                            <div class="text-muted">{{ $assignment->vehicle->plate_number ?? 'No plate' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Status</div>
                            <div><span class="{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span></div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Driver / Custodian</div>
                            <div class="fw-bold">{{ $displayUser($assignment->driver) }}</div>
                            @if($assignment->driver && !empty($assignment->driver->email))
                                <div class="text-muted">{{ $assignment->driver->email }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Branch</div>
                            <div class="fw-bold">{{ $assignment->branch->name ?? $assignment->branch->branch_name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Department</div>
                            <div class="fw-bold">{{ $assignment->department->name ?? $assignment->department->department_name ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Project / Site</div>
                            <div class="fw-bold">{{ $assignment->project_site_text ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">Start Date</div>
                            <div class="fw-bold">{{ optional($assignment->start_date)->format('M d, Y') ?? '-' }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="vm-info-label">End Date</div>
                            <div class="fw-bold">{{ optional($assignment->end_date)->format('M d, Y') ?? 'Present' }}</div>
                        </div>

                        <div class="col-12">
                            <div class="vm-info-label">Purpose</div>
                            <div>{{ $assignment->purpose ?? '-' }}</div>
                        </div>

                        <div class="col-12">
                            <div class="vm-info-label">Remarks</div>
                            <div>{{ $assignment->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card vm-info-card">
                <div class="card-body">
                    <h5 class="mb-3">Team Members</h5>

                    @forelse($assignment->members as $member)
                        <div class="border-bottom py-2">
                            <div class="fw-bold">{{ $displayUser($member->user) }}</div>
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
