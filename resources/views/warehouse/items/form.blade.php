<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text"
               name="code"
               value="{{ old('code', $item->code ?? '') }}"
               class="form-control @error('code') is-invalid @enderror"
               placeholder="Enter item code">
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label">Item Name <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               value="{{ old('name', $item->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Enter item name">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">None</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name ?? $category->category_name ?? 'Category #' . $category->id }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Unit</label>
        <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
            <option value="">None</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}"
                    {{ old('unit_id', $item->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name ?? $unit->unit_name ?? $unit->symbol ?? 'Unit #' . $unit->id }}
                </option>
            @endforeach
        </select>
        @error('unit_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
            <option value="">None</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    {{ old('supplier_id', $item->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name ?? $supplier->supplier_name ?? $supplier->company_name ?? 'Supplier #' . $supplier->id }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


<div class="row align-items-stretch mb-3">
    <div class="col-md-4 mb-3 mb-md-0">
        <label class="form-label">Item Picture</label>
        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
            @if(isset($item) && $item->image_path)
                <div class="mb-3 text-center">
                    <img src="{{ $item->image_url }}"
                         alt="{{ $item->display_name }}"
                         class="img-fluid rounded-3 border bg-white"
                         style="max-height: 160px; object-fit: contain;">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox"
                           name="remove_image"
                           value="1"
                           id="remove_image"
                           class="form-check-input">
                    <label class="form-check-label text-danger" for="remove_image">
                        Remove current picture
                    </label>
                </div>
            @else
                <div class="d-flex align-items-center justify-content-center border rounded-3 bg-white text-secondary mb-3"
                     style="height: 160px;">
                    No image uploaded
                </div>
            @endif

            <input type="file"
                   name="image"
                   accept="image/jpeg,image/png,image/webp"
                   class="form-control @error('image') is-invalid @enderror">
            <div class="small text-secondary mt-2">Admin/Manager only. JPG, PNG, or WEBP up to 4MB.</div>
            @error('image')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-8">
        <label class="form-label">Description</label>
    <textarea name="description"
              rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Enter item description">{{ old('description', $item->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">Cost Price <span class="text-danger">*</span></label>
        <input type="number"
               step="0.01"
               name="cost_price"
               value="{{ old('cost_price', $item->cost_price ?? 0) }}"
               class="form-control @error('cost_price') is-invalid @enderror">
        @error('cost_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Selling Price <span class="text-danger">*</span></label>
        <input type="number"
               step="0.01"
               name="selling_price"
               value="{{ old('selling_price', $item->selling_price ?? 0) }}"
               class="form-control @error('selling_price') is-invalid @enderror">
        @error('selling_price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
        <input type="number"
               name="reorder_level"
               value="{{ old('reorder_level', $item->reorder_level ?? 0) }}"
               class="form-control @error('reorder_level') is-invalid @enderror">
        @error('reorder_level')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ old('status', isset($item) ? (int) $item->status : 1) == 1 ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ old('status', isset($item) ? (int) $item->status : 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>
        </select>

        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="form-check border rounded-3 p-3 ps-5 h-100">
            <input type="checkbox"
                   name="is_serialized"
                   value="1"
                   id="is_serialized"
                   class="form-check-input"
                   {{ old('is_serialized', $item->is_serialized ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_serialized">
                Serialized item
            </label>
            <div class="small text-secondary mt-1">Track individual serial numbers during stock in and stock out.</div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="form-check border rounded-3 p-3 ps-5 h-100">
            <input type="checkbox"
                   name="is_service_unit"
                   value="1"
                   id="is_service_unit"
                   class="form-check-input"
                   {{ old('is_service_unit', $item->is_service_unit ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_service_unit">
                Service Unit / Borrowable
            </label>
            <div class="small text-secondary mt-1">Use this item for borrowed company service unit tracking.</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serialized = document.getElementById('is_serialized');
        const serviceUnit = document.getElementById('is_service_unit');

        if (serialized && serviceUnit) {
            serviceUnit.addEventListener('change', function () {
                if (serviceUnit.checked) {
                    serialized.checked = true;
                }
            });
        }
    });
</script>