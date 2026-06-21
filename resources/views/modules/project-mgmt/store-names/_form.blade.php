@php
    $isEdit = isset($storeName);
@endphp

@if ($errors->any())
    <div class="alert alert-danger rounded-3">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="card rounded-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Store Code</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $isEdit ? $storeName->code : '') }}" placeholder="Optional code">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Store Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $isEdit ? $storeName->name : '') }}" placeholder="Enter store name" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $isEdit ? $storeName->contact_person : '') }}" placeholder="Contact person">
                    @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $isEdit ? $storeName->contact_number : '') }}" placeholder="Contact number">
                    @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $isEdit ? $storeName->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $isEdit ? $storeName->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Store address">{{ old('address', $isEdit ? $storeName->address : '') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional remarks">{{ old('remarks', $isEdit ? $storeName->remarks : '') }}</textarea>
                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('store-names.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Store' : 'Save Store' }}</button>
            </div>
        </div>
    </div>
</form>
