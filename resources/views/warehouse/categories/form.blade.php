<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Category Name <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               value="{{ old('name', $category->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Enter category name">

        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ old('status', isset($category) ? (int) $category->status : 1) == 1 ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ old('status', isset($category) ? (int) $category->status : 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>
        </select>

        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description"
              rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Enter category description">{{ old('description', $category->description ?? '') }}</textarea>

    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>