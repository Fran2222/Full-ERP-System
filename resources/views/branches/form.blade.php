<x-app-layout :assets="$assets ?? []">
    <div>
        @php
            $id = $id ?? null;
        @endphp

        @if(isset($id))
            <form action="{{ route('branches.update', $id) }}" method="POST">
            @method('PATCH')
        @else
            <form action="{{ route('branches.store') }}" method="POST">
        @endif
            @csrf

            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">{{ $id !== null ? 'Update' : 'Add' }} Branch</h4>
                            </div>
                            <div class="card-action">
                                <a href="{{ route('branches.index') }}" class="btn btn-sm btn-primary">Back</a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-label">Branch Name: <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $data->name ?? '') }}"
                                           placeholder="Enter Branch Name" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Branch Code: <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control"
                                           value="{{ old('code', $data->code ?? '') }}"
                                           placeholder="Enter Branch Code" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <label class="form-label">Address:</label>
                                    <textarea name="address" class="form-control" rows="4" placeholder="Enter Branch Address">{{ old('address', $data->address ?? '') }}</textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Status: <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status', $data->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $data->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                {{ $id !== null ? 'Update' : 'Add' }} Branch
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>