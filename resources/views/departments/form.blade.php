<x-app-layout>
    @php
        $isEdit = isset($department) && !empty($department->id);
        $designationText = old('designation_names_text', $designationText ?? '');
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="header-title">
                        <h4 class="card-title mb-0">
                            {{ $isEdit ? 'Edit Department' : 'Add Department' }}
                        </h4>
                    </div>
                    <a href="{{ route('departments.index') }}" class="btn btn-sm btn-primary">
                        Back
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ $isEdit ? route('departments.update', $department->id) : route('departments.store') }}" method="POST">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $department->name ?? '') }}"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $department->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $department->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Designations</label>
                                <textarea
                                    name="designation_names_text"
                                    rows="6"
                                    class="form-control @error('designation_names_text') is-invalid @enderror"
                                    placeholder="One designation per line&#10;Example:&#10;Accounting Manager&#10;Bookkeeper&#10;Accounting Assistant"
                                >{{ $designationText }}</textarea>
                                <small class="text-muted">Add one designation per line for this department.</small>
                                @error('designation_names_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update Department' : 'Save Department' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
