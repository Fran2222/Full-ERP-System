<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card rounded-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Create Client</h4>
                <p class="text-secondary mb-0">Add new project client.</p>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clients.store') }}" method="POST" class="row g-3 needs-validation" novalidate>
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Unique Code</label>
                        <input type="text"
                               name="code"
                               class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $code) }}"
                               readonly>
                        <div class="invalid-feedback">
                            @error('code')
                                {{ $message }}
                            @else
                                Client code is required.
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Enter client/company name"
                               maxlength="100"
                               required>
                        <div class="invalid-feedback">
                            @error('name')
                                {{ $message }}
                            @else
                                Client name is required and must not exceed 100 characters.
                            @enderror
                        </div>
                    </div>

                  <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text"
                        name="contact_person"
                        class="form-control @error('contact_person') is-invalid @enderror"
                        value="{{ old('contact_person') }}"
                        maxlength="100"
                        placeholder="Enter contact person">
                    <div class="invalid-feedback">
                        @error('contact_person')
                            {{ $message }}
                        @else
                            Contact person must not exceed 100 characters.
                        @enderror
                    </div>
                </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text"
                               name="contact_number"
                               class="form-control @error('contact_number') is-invalid @enderror"
                               value="{{ old('contact_number') }}"
                               placeholder="09XXXXXXXXX"
                               maxlength="50">
                        <div class="invalid-feedback">
                            @error('contact_number')
                                {{ $message }}
                            @else
                                Contact number must not exceed 50 characters.
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">@</span>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="example@email.com"
                                   maxlength="255">
                            <div class="invalid-feedback">
                                @error('email')
                                    {{ $message }}
                                @else
                                    Please provide a valid email address.
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>
                            <option value="">Select Status</option>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="invalid-feedback">
                            @error('status')
                                {{ $message }}
                            @else
                                Please select a status.
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="3"
                                  maxlength="180"
                                  placeholder="Enter address">{{ old('address') }}</textarea>
                        <div class="invalid-feedback">
                            @error('address')
                                {{ $message }}
                            @else
                                Address must not exceed 180 characters.
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Add remarks or client notes">{{ old('remarks') }}</textarea>
                        <div class="invalid-feedback">
                            @error('remarks')
                                {{ $message }}
                            @else
                                Remarks must not exceed 1000 characters.
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('clients.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.needs-validation').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        form.classList.add('was-validated');
                    }, false);
                });
            });
        </script>
    @endpush
</x-app-layout>
