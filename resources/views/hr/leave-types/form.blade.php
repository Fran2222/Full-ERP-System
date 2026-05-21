<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @if ($errors->any())
            <div class="alert alert-danger rounded-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isEdit = $isEdit ?? false;

            $formAction = $isEdit
                ? route('hr.leave-types.update', $leaveType->id)
                : route('hr.leave-types.store');

            $pageTitle = $isEdit ? 'Edit Leave Type' : 'Create Leave Type';
            $buttonText = $isEdit ? 'Update Leave Type' : 'Save Leave Type';

            $selectedIsPaid = old('is_paid', isset($leaveType->is_paid) ? (string) (int) $leaveType->is_paid : '1');
            $selectedStatus = old('status', $leaveType->status ?? 'active');
        @endphp

        <form action="{{ $formAction }}" method="POST">
            @csrf

            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card rounded-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-0">{{ $pageTitle }}</h4>
                        <p class="mb-0 text-secondary">
                            {{ $isEdit ? 'Update leave type details.' : 'Add a new leave type' }}
                        </p>
                    </div>

                    <a href="{{ route('hr.leave-types.index') }}" class="btn btn-primary btn-sm px-4" style="border-radius: 4px;">
                        Back
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Leave Type</label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $leaveType->name ?? '') }}"
                                   placeholder="Example: Sick Leave"
                                   required>

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Allowed Leave Day Per Year</label>
                            <input type="number"
                                   step="0.5"
                                   min="0"
                                   name="default_credits"
                                   class="form-control @error('default_credits') is-invalid @enderror"
                                   value="{{ old('default_credits', $leaveType->default_credits ?? 0) }}"
                                   required>

                            @error('default_credits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Leave Category</label>
                            <select name="is_paid"
                                    class="form-select @error('is_paid') is-invalid @enderror"
                                    required>
                                <option value="1" {{ $selectedIsPaid === '1' ? 'selected' : '' }}>
                                    Leave With Pay
                                </option>
                                <option value="0" {{ $selectedIsPaid === '0' ? 'selected' : '' }}>
                                    Leave Without Pay
                                </option>
                            </select>

                            @error('is_paid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Status</label>
                            <select name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required>
                                <option value="active" {{ $selectedStatus === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ $selectedStatus === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>

                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description"
                                      rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Optional description">{{ old('description', $leaveType->description ?? '') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    {{ $buttonText }}
                </button>

                <a href="{{ route('hr.leave-types.index') }}" class="btn btn-light">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>