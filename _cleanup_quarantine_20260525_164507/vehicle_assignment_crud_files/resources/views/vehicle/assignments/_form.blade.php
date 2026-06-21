@php
    $assignment = $assignment ?? null;
    $selectedVehicle = old('vehicle_id', $assignment->vehicle_id ?? '');
    $selectedDriver = old('driver_id', $assignment->driver_id ?? '');
    $selectedBranch = old('branch_id', $assignment->branch_id ?? '');
    $selectedDepartment = old('department_id', $assignment->department_id ?? '');
    $selectedStatus = old('status', $assignment->status ?? 'active');
    $oldMembers = old('member_ids', $selectedMembers ?? []);
    $oldMembers = is_array($oldMembers) ? array_map('strval', $oldMembers) : [];
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4">Assignment Details</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
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
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                            <option value="">Select Driver / Custodian</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ (string)$selectedDriver === (string)$driver->id ? 'selected' : '' }}>
                                    {{ $driver->name ?? $driver->email ?? ('User #' . $driver->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
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
                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
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
                        <select name="member_ids[]" class="form-select @error('member_ids') is-invalid @enderror" multiple size="6">
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ in_array((string)$member->id, $oldMembers, true) ? 'selected' : '' }}>
                                    {{ $member->name ?? $member->email ?? ('User #' . $member->id) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold CTRL to select multiple members.</small>
                        @error('member_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('member_ids.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Project / Site</label>
                        <input type="text" name="project_site_text" class="form-control @error('project_site_text') is-invalid @enderror" value="{{ old('project_site_text', $assignment->project_site_text ?? '') }}" placeholder="Example: CDO site service, Solana branch, delivery route...">
                        @error('project_site_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
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
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', optional($assignment->start_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', optional($assignment->end_date ?? null)->format('Y-m-d')) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Purpose</label>
                        <textarea name="purpose" class="form-control @error('purpose') is-invalid @enderror" rows="3" placeholder="Purpose of assignment...">{{ old('purpose', $assignment->purpose ?? '') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3" placeholder="Additional notes...">{{ old('remarks', $assignment->remarks ?? '') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vehicle.assignments.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Assignment</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
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
