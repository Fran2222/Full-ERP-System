@php
    $document = $document ?? null;
    $selectedVehicle = old('vehicle_id', $document->vehicle_id ?? '');
    $selectedType = old('document_type', $document->document_type ?? '');
    $selectedStatus = old('status', $document->status ?? 'active');
@endphp

<style>
    .vm-card { border:0; border-radius:16px; box-shadow:0 6px 18px rgba(31,45,61,.06); }
    .vm-control { min-height:44px; border-radius:10px; }
</style>

<div class="row">
    <div class="col-lg-8">
        <div class="card vm-card">
            <div class="card-body">
                <h5 class="mb-4">Document Information</h5>

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
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select vm-control @error('document_type') is-invalid @enderror" required>
                            <option value="">Select Document Type</option>
                            @foreach($documentTypes as $value => $label)
                                <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Document No.</label>
                        <input type="text" name="document_no" class="form-control vm-control @error('document_no') is-invalid @enderror" value="{{ old('document_no', $document->document_no ?? '') }}" placeholder="Policy no., OR/CR no., certificate no...">
                        @error('document_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Issuing Agency / Provider</label>
                        <input type="text" name="issuing_agency" class="form-control vm-control @error('issuing_agency') is-invalid @enderror" value="{{ old('issuing_agency', $document->issuing_agency ?? '') }}" placeholder="LTO, insurance provider, emission center...">
                        @error('issuing_agency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control vm-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', optional($document->issue_date ?? null)->format('Y-m-d')) }}">
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control vm-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', optional($document->expiry_date ?? null)->format('Y-m-d')) }}">
                        @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Renewal Date</label>
                        <input type="date" name="renewal_date" class="form-control vm-control @error('renewal_date') is-invalid @enderror" value="{{ old('renewal_date', optional($document->renewal_date ?? null)->format('Y-m-d')) }}">
                        @error('renewal_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount / Renewal Cost</label>
                        <input type="number" name="amount" class="form-control vm-control @error('amount') is-invalid @enderror" value="{{ old('amount', $document->amount ?? 0) }}" min="0" step="0.01">
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select vm-control @error('status') is-invalid @enderror">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4" placeholder="Notes, renewal reminders, restrictions...">{{ old('remarks', $document->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vehicle.documents.index') }}" class="btn btn-light px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Save Document</button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card vm-card">
            <div class="card-body">
                <h5 class="mb-3">Attachment</h5>

                @if(!empty($document?->file_url))
                    <div class="mb-3">
                        <a href="{{ $document->file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">View Current File</a>
                    </div>
                @endif

                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,application/pdf">
                <small class="text-muted d-block mt-2">Accepted: JPG, PNG, WEBP, PDF. Max 5MB.</small>
                @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card vm-card mt-3">
            <div class="card-body">
                <h5 class="mb-3">Reminder Guide</h5>
                <p class="text-muted mb-0">Set expiry date for items like registration, insurance, and emission certificates so they appear in expiring soon reports.</p>
            </div>
        </div>
    </div>
</div>
