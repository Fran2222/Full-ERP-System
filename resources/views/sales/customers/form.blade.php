<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Customer Code <span class="text-danger">*</span></label>
        <input type="text"
               name="customer_code"
               value="{{ old('customer_code', $customer->customer_code ?? '') }}"
               class="form-control @error('customer_code') is-invalid @enderror"
               placeholder="Example: CUST-0001">

        @error('customer_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
        <input type="text"
               name="customer_name"
               value="{{ old('customer_name', $customer->customer_name ?? '') }}"
               class="form-control @error('customer_name') is-invalid @enderror"
               placeholder="Enter customer name">

        @error('customer_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Contact Person</label>
        <input type="text"
               name="contact_person"
               value="{{ old('contact_person', $customer->contact_person ?? '') }}"
               class="form-control @error('contact_person') is-invalid @enderror"
               placeholder="Enter contact person">

        @error('contact_person')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Phone</label>
        <input type="text"
               name="phone"
               value="{{ old('phone', $customer->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror"
               placeholder="Enter phone number">

        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Email</label>
        <input type="email"
               name="email"
               value="{{ old('email', $customer->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror"
               placeholder="Enter email address">

        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">TIN</label>
        <input type="text"
               name="tin"
               value="{{ old('tin', $customer->tin ?? '') }}"
               class="form-control @error('tin') is-invalid @enderror"
               placeholder="Enter TIN">

        @error('tin')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Payment Terms</label>
        <input type="text"
               name="payment_terms"
               value="{{ old('payment_terms', $customer->payment_terms ?? '') }}"
               class="form-control @error('payment_terms') is-invalid @enderror"
               placeholder="Example: Due on receipt / Net 30">

        @error('payment_terms')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ old('status', isset($customer) ? (int) $customer->status : 1) == 1 ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ old('status', isset($customer) ? (int) $customer->status : 1) == 0 ? 'selected' : '' }}>
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
        <label class="form-label">Billing Address</label>
        <textarea name="billing_address"
                  rows="3"
                  class="form-control @error('billing_address') is-invalid @enderror"
                  placeholder="Enter billing address">{{ old('billing_address', $customer->billing_address ?? '') }}</textarea>

        @error('billing_address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Shipping Address</label>
        <textarea name="shipping_address"
                  rows="3"
                  class="form-control @error('shipping_address') is-invalid @enderror"
                  placeholder="Enter shipping address">{{ old('shipping_address', $customer->shipping_address ?? '') }}</textarea>

        @error('shipping_address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>