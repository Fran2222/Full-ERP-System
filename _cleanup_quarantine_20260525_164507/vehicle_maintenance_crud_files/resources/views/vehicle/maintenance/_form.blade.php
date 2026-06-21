@php
    $maintenance = $maintenance ?? null;
    $selectedVehicle = old('vehicle_id', $maintenance->vehicle_id ?? '');
    $selectedType = old('maintenance_type_id', $maintenance->maintenance_type_id ?? '');
    $selectedReportedBy = old('reported_by', $maintenance->reported_by ?? '');
    $selectedPerformedBy = old('performed_by', $maintenance->performed_by ?? '');
    $selectedStatus = old('status', $maintenance->status ?? 'open');

    $displayUser = function ($user) {
        if (!$user) return '-';
        $middle = isset($user->middle_name) && $user->middle_name ? ' ' . substr($user->middle_name, 0, 1) . '.' : '';
        $name = trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . $middle);
        return $name ? $name . (!empty($user->email) ? ' - ' . $user->email : '') : ($user->email ?? ('User #' . $user->id));
    };
@endphp

<style>
    .vm-card { border:0; border-radius:16px; box-shadow:0 6px 18px rgba(31,45,61,.06); }
    .vm-control { min-height:44px; border-radius:10px; }
</style>

<div class="row">
    <div class="col-lg-8">
        <div class="card vm-card">
            <div class="card-body">
                <h5 class="mb-4">Maintenance Details</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select vm-control @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ (string)$selectedVehicle === (string)$vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->vehicle_code }}{{ $vehicle->plate_number ? ' - ' . $vehicle->plate_number : '' }}{{ $vehicle->brand || $vehicle->model ? ' (' . trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Maintenance Type</label>
                        <select name="maintenance_type_id" class="form-select vm-control @error('maintenance_type_id') is-invalid @enderror">
                            <option value="">Select Maintenance Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ (string)$selectedType === (string)$type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('maintenance_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Maintenance Date <span class="text-danger">*</span></label>
                        <input type="date" name="maintenance_date" class="form-control vm-control @error('maintenance_date') is-invalid @enderror" value="{{ old('maintenance_date', optional($maintenance->maintenance_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                        @error('maintenance_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Odometer</label>
                        <input type="number" name="odometer" class="form-control vm-control @error('odometer') is-invalid @enderror" value="{{ old('odometer', $maintenance->odometer ?? '') }}" min="0" step="1">
                        @error('odometer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Reported By</label>
                        <select name="reported_by" class="form-select vm-control @error('reported_by') is-invalid @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string)$selectedReportedBy === (string)$user->id ? 'selected' : '' }}>{{ $displayUser($user) }}</option>
                            @endforeach
                        </select>
                        @error('reported_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Performed By</label>
                        <select name="performed_by" class="form-select vm-control @error('performed_by') is-invalid @enderror">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string)$selectedPerformedBy === (string)$user->id ? 'selected' : '' }}>{{ $displayUser($user) }}</option>
                            @endforeach
                        </select>
                        @error('performed_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Issue / Concern</label>
                        <textarea name="issue_or_concern" class="form-control @error('issue_or_concern') is-invalid @enderror" rows="3" placeholder="Describe the issue or maintenance concern...">{{ old('issue_or_concern', $maintenance->issue_or_concern ?? '') }}</textarea>
                        @error('issue_or_concern')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Action Taken</label>
                        <textarea name="action_taken" class="form-control @error('action_taken') is-invalid @enderror" rows="3" placeholder="Describe the repair or action done...">{{ old('action_taken', $maintenance->action_taken ?? '') }}</textarea>
                        @error('action_taken')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Parts Replaced</label>
                        <textarea name="parts_replaced" class="form-control @error('parts_replaced') is-invalid @enderror" rows="2" placeholder="Example: oil filter, brake pads, tire...">{{ old('parts_replaced', $maintenance->parts_replaced ?? '') }}</textarea>
                        @error('parts_replaced')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Shop / Mechanic</label>
                        <input type="text" name="shop_or_mechanic" class="form-control vm-control @error('shop_or_mechanic') is-invalid @enderror" value="{{ old('shop_or_mechanic', $maintenance->shop_or_mechanic ?? '') }}">
                        @error('shop_or_mechanic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select vm-control @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Labor Cost</label>
                        <input type="number" name="labor_cost" class="form-control vm-control cost-field @error('labor_cost') is-invalid @enderror" value="{{ old('labor_cost', $maintenance->labor_cost ?? 0) }}" min="0" step="0.01">
                        @error('labor_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Parts Cost</label>
                        <input type="number" name="parts_cost" class="form-control vm-control cost-field @error('parts_cost') is-invalid @enderror" value="{{ old('parts_cost', $maintenance->parts_cost ?? 0) }}" min="0" step="0.01">
                        @error('parts_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Other Cost</label>
                        <input type="number" name="other_cost" class="form-control vm-control cost-field @error('other_cost') is-invalid @enderror" value="{{ old('other_cost', $maintenance->other_cost ?? 0) }}" min="0" step="0.01">
                        @error('other_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Next Maintenance Date</label>
                        <input type="date" name="next_maintenance_date" class="form-control vm-control @error('next_maintenance_date') is-invalid @enderror" value="{{ old('next_maintenance_date', optional($maintenance->next_maintenance_date ?? null)->format('Y-m-d')) }}">
                        @error('next_maintenance_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Next Maintenance Odometer</label>
                        <input type="number" name="next_maintenance_odometer" class="form-control vm-control @error('next_maintenance_odometer') is-invalid @enderror" value="{{ old('next_maintenance_odometer', $maintenance->next_maintenance_odometer ?? '') }}" min="0" step="1">
                        @error('next_maintenance_odometer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $maintenance->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vehicle.maintenance.index') }}" class="btn btn-light px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Save Maintenance</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card vm-card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Cost Summary</h5>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Cost</span>
                    <strong id="vmTotalCost">0.00</strong>
                </div>
            </div>
        </div>

        <div class="card vm-card">
            <div class="card-body">
                <h5 class="mb-3">Attachment / Receipt</h5>

                @if(!empty($maintenance?->attachment_path))
                    <div class="mb-3">
                        <a href="{{ asset('storage/' . $maintenance->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Current Attachment</a>
                    </div>
                @endif

                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,application/pdf">
                <small class="text-muted d-block mt-2">Accepted: JPG, PNG, WEBP, PDF. Max 5MB.</small>
                @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fields = document.querySelectorAll('.cost-field');
    const total = document.getElementById('vmTotalCost');

    function recalc() {
        let sum = 0;
        fields.forEach(field => {
            const value = parseFloat(field.value || '0');
            if (!isNaN(value)) sum += value;
        });
        if (total) total.textContent = sum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    fields.forEach(field => field.addEventListener('input', recalc));
    recalc();
})();
</script>
