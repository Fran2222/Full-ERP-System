@php
    $assignment = $assignment ?? null;
    $selectedVehicle = old('vehicle_id', $assignment->vehicle_id ?? '');
    $selectedDriver = old('driver_id', $assignment->driver_id ?? '');
    $selectedBranch = old('branch_id', $assignment->branch_id ?? '');
    $selectedDepartment = old('department_id', $assignment->department_id ?? '');
    $selectedStatus = old('status', $assignment->status ?? 'active');
    $oldMembers = old('member_ids', $selectedMembers ?? []);
    $oldMembers = is_array($oldMembers) ? array_map('strval', $oldMembers) : [];

    $displayUser = function ($user) {
        if (!$user) {
            return '-';
        }

        $middle = isset($user->middle_name) && $user->middle_name ? ' ' . substr($user->middle_name, 0, 1) . '.' : '';
        $name = trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . $middle);

        if ($name) {
            return $name . (!empty($user->email) ? ' - ' . $user->email : '');
        }

        return $user->email ?? ('User #' . $user->id);
    };
@endphp

<style>
    .vm-form-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 6px 18px rgba(31, 45, 61, .06);
    }

    .vm-form-control {
        min-height: 44px;
        border-radius: 10px;
    }

    .vm-team-select {
        min-height: 180px;
        border-radius: 10px;
    }

    .vm-help-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 6px 18px rgba(31, 45, 61, .06);
    }
</style>

<div class="row">
    <div class="col-lg-8">
        <div class="card vm-form-card">
            <div class="card-body">
                <h5 class="mb-4">Assignment Details</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select vm-form-control @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string)$selectedVehicle === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_code }}{{ $vehicle->plate_number ? ' - ' . $vehicle->plate_number : '' }}{{ $vehicle->brand || $vehicle->model ? ' (' . trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!$assignment)
                            <div class="form-check mt-2">
                                <input type="checkbox" name="end_existing_assignment" value="1" class="form-check-input" id="endExistingAssignment" checked>
                                <label class="form-check-label" for="endExistingAssignment">
                                    End existing active assignment for this vehicle
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Driver / Custodian</label>
                        <select name="driver_id" class="form-select vm-form-control @error('driver_id') is-invalid @enderror">
                            <option value="">Select Driver / Custodian</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ (string)$selectedDriver === (string)$driver->id ? 'selected' : '' }}>
                                    {{ $displayUser($driver) }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select vm-form-control @error('branch_id') is-invalid @enderror">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string)$selectedBranch === (string)$branch->id ? 'selected' : '' }}>
                                    {{ $branch->name ?? $branch->branch_name ?? ('Branch #' . $branch->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select vm-form-control @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string)$selectedDepartment === (string)$department->id ? 'selected' : '' }}>
                                    {{ $department->name ?? $department->department_name ?? ('Department #' . $department->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Assigned Team Members</label>
                        <input type="text"
                               id="vmMemberSearch"
                               class="form-control vm-form-control mb-2"
                               placeholder="Search team member by name or email...">
                        <select name="member_ids[]"
                                id="vmMemberSelect"
                                class="form-select vm-team-select @error('member_ids') is-invalid @enderror"
                                multiple
                                size="7">
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ in_array((string)$member->id, $oldMembers, true) ? 'selected' : '' }}>
                                    {{ $displayUser($member) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold CTRL to select multiple members. Use the search box above to filter the list.</small>
                        @error('member_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('member_ids.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Project / Site</label>
                        <input type="text"
                               name="project_site_text"
                               class="form-control vm-form-control @error('project_site_text') is-invalid @enderror"
                               value="{{ old('project_site_text', $assignment->project_site_text ?? '') }}"
                               placeholder="Example: CDO site service, Solana branch, delivery route...">
                        @error('project_site_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select vm-form-control @error('status') is-invalid @enderror" required>
                            @foreach(['active' => 'Active', 'ended' => 'Ended', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date"
                               name="start_date"
                               class="form-control vm-form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', optional($assignment->start_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"
                               required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date"
                               name="end_date"
                               class="form-control vm-form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', optional($assignment->end_date ?? null)->format('Y-m-d')) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Purpose</label>
                        <textarea name="purpose"
                                  class="form-control @error('purpose') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Purpose of assignment...">{{ old('purpose', $assignment->purpose ?? '') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Additional notes...">{{ old('remarks', $assignment->remarks ?? '') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-light px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Save Assignment</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card vm-help-card">
            <div class="card-body">
                <h5 class="mb-3">Assignment Guide</h5>
                <p class="text-muted mb-2">Use this page to record who is currently responsible for a vehicle.</p>
                <ul class="text-muted mb-0">
                    <li>Only one active assignment per vehicle is recommended.</li>
                    <li>Team members are optional.</li>
                    <li>Project/Site is text for now; project link can be added later.</li>
                    <li>Active assignment updates vehicle branch and default driver.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('vmMemberSearch');
    const select = document.getElementById('vmMemberSelect');

    if (!searchInput || !select) {
        return;
    }

    const options = Array.from(select.options).map(option => ({
        option,
        text: option.text.toLowerCase()
    }));

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        options.forEach(({ option, text }) => {
            const match = !keyword || text.includes(keyword);
            option.hidden = !match;
        });
    });
})();
</script>
