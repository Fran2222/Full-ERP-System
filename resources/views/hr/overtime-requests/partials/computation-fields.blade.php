@php
    $employeeProfileForRate = $overtimeRequest->requester?->employeeProfile;
    $employeeDailyRate = (float) ($employeeProfileForRate?->employee_rate ?? $employeeProfileForRate?->salary ?? 0);
    $employeeHourlyRate = $employeeDailyRate > 0 ? $employeeDailyRate / 8 : null;
    $ratePerHourValue = old('rate_per_hour', $overtimeRequest->rate_per_hour ?? $employeeHourlyRate);
    $dailyRateValue = old('daily_rate', $overtimeRequest->daily_rate ?? ($employeeDailyRate > 0 ? $employeeDailyRate : null));
    $previewDate = $overtimeRequest->overtime_date?->format('Y-m-d');
    $previewStart = $overtimeRequest->time_started ? \Carbon\Carbon::parse($overtimeRequest->time_started)->format('H:i') : null;
    $previewEnd = $overtimeRequest->time_ended ? \Carbon\Carbon::parse($overtimeRequest->time_ended)->format('H:i') : null;
@endphp

@if($employeeDailyRate <= 0)
    <div class="alert alert-warning rounded-3 mb-3">
        Please update the employee <strong>Rate per Day</strong> in the 201 File before computing overtime.
    </div>
@endif

<div class="row g-3 align-items-end">
    <div class="col-lg-4 col-md-6">
        <label class="form-label">Overtime Type <span class="text-danger">*</span></label>
        <select name="overtime_type" class="form-select" required data-ot-type>
            <option value="">Select type</option>
            @foreach($overtimeComputationTypes as $key => $type)
                <option value="{{ $key }}"
                        data-uses-daily-rate="{{ $type['uses_daily_rate'] ? '1' : '0' }}"
                        data-multiplier="{{ $type['multiplier'] }}"
                        data-formula="{{ $type['formula'] }}"
                        {{ old('overtime_type', $overtimeRequest->overtime_type) === $key ? 'selected' : '' }}>
                    {{ $type['label'] }}
                </option>
            @endforeach
        </select>
        <small class="text-secondary d-block mt-1" data-ot-selected-formula>
            Select a type to preview the computation.
        </small>
    </div>

    <div class="col-lg-4 col-md-6">
        <label class="form-label">Rate per Day <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number"
                   name="daily_rate"
                   class="form-control bg-light"
                   step="0.0001"
                   min="0"
                   value="{{ $dailyRateValue }}"
                   data-auto-daily-rate="{{ $employeeDailyRate > 0 ? $employeeDailyRate : '' }}"
                   readonly
                   required>
        </div>
        <small class="text-secondary">From employee 201 File.</small>
    </div>

    <div class="col-lg-4 col-md-6">
        <label class="form-label">Rate per Hour <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number"
                   name="rate_per_hour"
                   class="form-control bg-light"
                   step="0.0001"
                   min="0"
                   value="{{ $ratePerHourValue }}"
                   readonly
                   required>
        </div>
        <small class="text-secondary">Auto: Rate per Day ÷ 8.</small>
    </div>

    <div class="col-lg-4 col-md-6">
        <label class="form-label">Date Paid</label>
        <input type="date"
               name="date_paid"
               class="form-control"
               value="{{ old('date_paid', optional($overtimeRequest->date_paid)->format('Y-m-d')) }}">
    </div>

    <div class="col-lg-8 col-md-12">
        <label class="form-label">{{ $remarksLabel ?? 'Remarks' }}</label>
        <input type="text"
               name="{{ $remarksName ?? 'remarks' }}"
               class="form-control"
               value="{{ old($remarksName ?? 'remarks') }}"
               placeholder="Optional remarks">
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-3 bg-light" data-ot-preview
             data-overtime-date="{{ $previewDate }}"
             data-time-started="{{ $previewStart }}"
             data-time-ended="{{ $previewEnd }}">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div>
                    <h6 class="mb-1">Realtime Computation Preview</h6>
                    <small class="text-secondary">Review this first before approving.</small>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill" data-ot-preview-status>Waiting for type</span>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-3 col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Total Hours</small>
                        <div class="fw-semibold" data-ot-total-hours>0.00</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Regular Hours</small>
                        <div class="fw-semibold" data-ot-regular-hours>0.00</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Night Diff. Hours</small>
                        <div class="fw-semibold" data-ot-night-hours>0.00</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Multiplier</small>
                        <div class="fw-semibold" data-ot-multiplier>—</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Base OT Amount</small>
                        <div class="fw-semibold" data-ot-base-amount>₱0.00</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Night Diff. Amount</small>
                        <div class="fw-semibold" data-ot-night-amount>₱0.00</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-3 p-2 bg-white h-100">
                        <small class="text-secondary">Total Amount</small>
                        <div class="fw-bold text-success" data-ot-total-amount>₱0.00</div>
                    </div>
                </div>
            </div>

            <div class="small text-secondary mt-3" data-ot-preview-lines>
                Select an overtime type to generate the computation.
            </div>
        </div>
    </div>
</div>
