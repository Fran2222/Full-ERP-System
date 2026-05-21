<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Unit Name <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               value="{{ old('name', $unit->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Example: Pieces">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Abbreviation <span class="text-danger">*</span></label>
        <input type="text"
               name="abbreviation"
               value="{{ old('abbreviation', $unit->abbreviation ?? '') }}"
               class="form-control @error('abbreviation') is-invalid @enderror"
               placeholder="Example: PCS">
        @error('abbreviation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>