<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Location Code <span class="text-danger">*</span></label>
        <input type="text"
               name="location_code"
               value="{{ old('location_code', $location->location_code ?? '') }}"
               class="form-control @error('location_code') is-invalid @enderror"
               placeholder="Example: LOC-0001">
        @error('location_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label">Location Name <span class="text-danger">*</span></label>
        <input type="text"
               name="location_name"
               value="{{ old('location_name', $location->location_name ?? $location->name ?? '') }}"
               class="form-control @error('location_name') is-invalid @enderror"
               placeholder="Example: Main Stock Room">
        @error('location_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Location Type <span class="text-danger">*</span></label>
        <select name="location_type" class="form-select @error('location_type') is-invalid @enderror">
            @php
                $selectedType = old('location_type', $location->location_type ?? 'Warehouse');
                $types = ['Warehouse', 'Stock Room', 'Storage Area', 'Shelf', 'Office Storage', 'Others'];
            @endphp

            @foreach($types as $type)
                <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        @error('location_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-label">Branch</label>
        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
            <option value="">No Branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}"
                    {{ old('branch_id', $location->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ old('status', isset($location) ? (int) $location->status : 1) == 1 ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ old('status', isset($location) ? (int) $location->status : 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address"
              rows="3"
              class="form-control @error('address') is-invalid @enderror"
              placeholder="Enter location address or notes">{{ old('address', $location->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>