@csrf
@php
    $jobOrder = $jobOrder ?? null;
    $selectedCustomer = old('customer_id', optional($jobOrder)->customer_id);
    $selectedType = old('service_type_id', optional($jobOrder)->service_type_id);
    $selectedStatus = old('service_status_id', optional($jobOrder)->service_status_id);
    $selectedBranch = old('branch_id', optional($jobOrder)->branch_id);
    $selectedUser = old('assigned_to_user_id', optional($jobOrder)->assigned_to_user_id);
    $selectedVehicle = old('vehicle_id', optional($jobOrder)->vehicle_id);
@endphp
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card service-card"><div class="card-body">
            <h5 class="mb-3">Job Order Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) $selectedCustomer === (string) $customer->id ? 'selected' : '' }}>{{ $customer->customer_name ?? $customer->name ?? ('Customer #' . $customer->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Service Type</label>
                    <select name="service_type_id" class="form-select">
                        <option value="">Select Service Type</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->id }}" {{ (string) $selectedType === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', optional($jobOrder)->subject) }}" placeholder="Example: CCTV camera no display" required>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" {{ old('priority', optional($jobOrder)->priority ?? 'normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Requested Date</label>
                    <input type="date" name="requested_date" class="form-control" value="{{ old('requested_date', optional(optional($jobOrder)->requested_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', optional(optional($jobOrder)->scheduled_at)->format('Y-m-d\\TH:i')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Site Address / Location</label>
                    <textarea name="site_address" class="form-control" rows="2" placeholder="Customer site / address...">{{ old('site_address', optional($jobOrder)->site_address) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Concern / Request Details</label>
                    <textarea name="concern" class="form-control" rows="4" placeholder="Describe customer concern...">{{ old('concern', optional($jobOrder)->concern) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Internal notes...">{{ old('remarks', optional($jobOrder)->remarks) }}</textarea>
                </div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card service-card mb-3"><div class="card-body">
            <h5 class="mb-3">Assignment</h5>
            <div class="mb-3"><label class="form-label">Assigned Technician</label><select name="assigned_to_user_id" class="form-select"><option value="">Select Technician/User</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ (string) $selectedUser === (string) $user->id ? 'selected' : '' }}>{{ $user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->email ?? ('User #' . $user->id)) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Branch</label><select name="branch_id" class="form-select"><option value="">Select Branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" {{ (string) $selectedBranch === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name ?? $branch->branch_name ?? ('Branch #' . $branch->id) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Vehicle (optional)</label><select name="vehicle_id" class="form-select"><option value="">No Vehicle</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" {{ (string) $selectedVehicle === (string) $vehicle->id ? 'selected' : '' }}>{{ $vehicle->vehicle_code ?? ('Vehicle #' . $vehicle->id) }} {{ !empty($vehicle->plate_number) ? ' - ' . $vehicle->plate_number : '' }}</option>@endforeach</select></div>
            <div class="mb-0"><label class="form-label">Status</label><select name="service_status_id" class="form-select"><option value="">Select Status</option>@foreach($statuses as $status)<option value="{{ $status->id }}" {{ (string) $selectedStatus === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>@endforeach</select></div>
        </div></div>
        <div class="d-flex justify-content-end gap-2"><a href="{{ route('service.job-orders.index') }}" class="btn btn-light">Cancel</a><button class="btn btn-primary">{{ $buttonText ?? 'Save Job Order' }}</button></div>
    </div>
</div>
