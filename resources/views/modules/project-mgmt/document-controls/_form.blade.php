@php
    $isEdit = isset($documentControl) && $documentControl->exists;
    $action = $isEdit ? route('document-controls.update', $documentControl->id) : route('document-controls.store');
@endphp

@if ($errors->any())
    <div class="alert alert-danger rounded-3 d-none" id="serverValidationErrors">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" id="documentControlForm" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Form Name <span class="text-danger">*</span></label>
            <input type="text" name="form_name" class="form-control @error('form_name') is-invalid @enderror" value="{{ old('form_name', $documentControl->form_name ?? 'Request for Payment') }}" placeholder="Example: Request for Payment" required>
            @error('form_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <input type="text" name="type" class="form-control text-uppercase @error('type') is-invalid @enderror" value="{{ old('type', $documentControl->type ?? '') }}" placeholder="Example: TO" required>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Revision <span class="text-danger">*</span></label>
            <input type="text" class="form-control bg-light" value="{{ old('revision_no', $documentControl->revision_no ?? '00') }}" readonly>
            <small class="text-muted">Read-only. Updates only through New Revision.</small>
        </div>

        <div class="col-md-5">
            <label class="form-label">Document No. <span class="text-danger">*</span></label>
            <input type="text" name="document_no" class="form-control text-uppercase @error('document_no') is-invalid @enderror" value="{{ old('document_no', $documentControl->document_no ?? '') }}" placeholder="Example: WMC-RFP-TO-001" required>
            @error('document_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Effective Date <span class="text-danger">*</span></label>
            <div class="input-group wrap_flatpicker document-date-picker">
                <input type="text" name="effective_date" class="form-control @error('effective_date') is-invalid @enderror" value="{{ old('effective_date', optional($documentControl->effective_date ?? null)->format('Y-m-d')) }}" placeholder="Select Date.." autocomplete="off" data-input required>
                <span class="input-group-text input-button pointer-event" data-toggle>
                    <svg width="18" viewBox="0 0 24 24" fill="none"><path d="M7 2V5M17 2V5M3 9H21M5 5H19C20.1046 5 21 5.89543 21 7V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V7C3 5.89543 3.89543 5 5 5Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                @error('effective_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach(['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $documentControl->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Code Prefix</label>
            <input type="text" name="code_prefix" class="form-control text-uppercase @error('code_prefix') is-invalid @enderror" value="{{ old('code_prefix', $documentControl->code_prefix ?? '') }}" placeholder="Example: RFP-TO">
            <small class="text-muted">Used for sample code like RFP-TO-2026-0000.</small>
            @error('code_prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Revision Notes</label>
            <textarea name="revision_notes" class="form-control @error('revision_notes') is-invalid @enderror" rows="4" placeholder="Example: Initial controlled template / Added new required fields">{{ old('revision_notes', $documentControl->revision_notes ?? '') }}</textarea>
            @error('revision_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ $isEdit ? route('document-controls.show', $documentControl->id) : route('document-controls.index') }}" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Document' : 'Save Document' }}</button>
    </div>
</form>

@push('styles')
<style>
    .document-date-picker .form-control { height: 44px; border: 1px solid #e0e5f2; border-right: 0; background: #fff; }
    .document-date-picker .input-group-text { height: 44px; border: 1px solid #e0e5f2; border-left: 0; background: #fff; cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.wrap_flatpicker', {
            wrap: true,
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }

    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Please check the form',
            html: `{!! '<ul class="text-start mb-0">' . implode('', $errors->all('<li>:message</li>')) . '</ul>' !!}`,
            confirmButtonText: 'Okay'
        });
    @endif

    $('#documentControlForm').on('submit', function (e) {
        let missing = [];
        $(this).find('[required]').each(function () {
            if (!String($(this).val() || '').trim()) {
                missing.push($(this).closest('.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-12').find('.form-label').first().text().replace('*', '').trim());
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (missing.length) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Required Fields',
                html: '<div class="text-start">Please fill out:<br><strong>' + missing.join(', ') + '</strong></div>',
                confirmButtonText: 'Okay'
            });
        }
    });
});
</script>
@endpush
