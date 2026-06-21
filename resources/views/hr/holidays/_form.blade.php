@csrf

<div class="row g-3">
    <div class="col-lg-5">
        <label class="form-label fw-semibold">Holiday Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $holiday->name ?? '') }}" placeholder="Example: Labor Day" required>
    </div>

    <div class="col-lg-3">
        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
        <input type="date" name="holiday_date" class="form-control" value="{{ old('holiday_date', isset($holiday) && $holiday->holiday_date ? $holiday->holiday_date->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">Holiday Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select" required>
            @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}" {{ old('type', $holiday->type ?? 'regular') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">Applicable Branch</label>
        <select name="branch_id" class="form-select">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ (string) old('branch_id', $holiday->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Use All Branches for national/company-wide holidays.</small>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold d-block">Pay / Status Settings</label>
        <div class="d-flex gap-3 flex-wrap pt-2">
            <label class="form-check mb-0">
                <input type="hidden" name="is_paid" value="0">
                <input type="checkbox" name="is_paid" value="1" class="form-check-input" {{ old('is_paid', isset($holiday) ? (bool) $holiday->is_paid : true) ? 'checked' : '' }}>
                <span class="form-check-label">Paid Holiday</span>
            </label>
            <label class="form-check mb-0">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', isset($holiday) ? (bool) $holiday->is_active : true) ? 'checked' : '' }}>
                <span class="form-check-label">Active</span>
            </label>
        </div>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $holiday->remarks ?? '') }}" placeholder="Optional notes">
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('hr.holidays.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary">Save Holiday</button>
</div>
