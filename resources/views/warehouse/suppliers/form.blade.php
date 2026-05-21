<div class="warehouse-form-section mb-4">
    <div class="warehouse-section-heading mb-3">
        <div>
            <h5 class="fw-bold mb-1">Supplier Information</h5>
            <p class="text-secondary mb-0">Main supplier name, contact person, and status.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6 col-lg-6">
            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
            <input type="text"
                   name="supplier_name"
                   value="{{ old('supplier_name', $supplier->supplier_name ?? '') }}"
                   class="form-control warehouse-input @error('supplier_name') is-invalid @enderror"
                   placeholder="Enter supplier name">
            @error('supplier_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Contact Person</label>
            <input type="text"
                   name="contact_person"
                   value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                   class="form-control warehouse-input @error('contact_person') is-invalid @enderror"
                   placeholder="Enter contact person">
            @error('contact_person')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-2 col-lg-2">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select warehouse-input @error('status') is-invalid @enderror">
                <option value="1" {{ old('status', isset($supplier) ? (int) $supplier->status : 1) == 1 ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ old('status', isset($supplier) ? (int) $supplier->status : 1) == 0 ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="warehouse-form-section mb-4">
    <div class="warehouse-section-heading mb-3">
        <div>
            <h5 class="fw-bold mb-1">Contact Details</h5>
            <p class="text-secondary mb-0">Phone, email, and supplier address.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Phone</label>
            <input type="text"
                   name="phone"
                   value="{{ old('phone', $supplier->phone ?? '') }}"
                   class="form-control warehouse-input @error('phone') is-invalid @enderror"
                   placeholder="Enter phone number">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-8 col-lg-8">
            <label class="form-label">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $supplier->email ?? '') }}"
                   class="form-control warehouse-input @error('email') is-invalid @enderror"
                   placeholder="Enter email address">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address"
                      rows="4"
                      class="form-control warehouse-input @error('address') is-invalid @enderror"
                      placeholder="Enter supplier address">{{ old('address', $supplier->address ?? '') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>