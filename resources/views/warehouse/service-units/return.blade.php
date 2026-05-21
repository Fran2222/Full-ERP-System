<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Return Service Unit</h4>
                        <p class="text-secondary mb-0">Record the return condition and update serial status.</p>
                    </div>
                    <a href="{{ route('warehouse.service-units.show', $serviceUnit) }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <div class="fw-semibold mb-2">Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small">Borrow No.</div>
                            <div class="fw-bold">{{ $serviceUnit->borrow_no }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small">Employee</div>
                            <div class="fw-bold">{{ $serviceUnit->employee?->full_name ?: $serviceUnit->employee?->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small">Item / Serial</div>
                            <div class="fw-bold">{{ $serviceUnit->item?->name ?? $serviceUnit->item?->item_name }} — {{ $serviceUnit->serial?->serial_number }}</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('warehouse.service-units.return.store', $serviceUnit) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Condition In <span class="text-danger">*</span></label>
                            <select name="condition_in" class="form-select @error('condition_in') is-invalid @enderror" required>
                                @foreach(['Good', 'Used - Good', 'Used - Fair', 'For Repair', 'Damaged', 'Lost'] as $condition)
                                    <option value="{{ $condition }}" {{ old('condition_in') === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                            @error('condition_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Serial Status After Return <span class="text-danger">*</span></label>
                            <select name="return_status" class="form-select @error('return_status') is-invalid @enderror" required>
                                <option value="available" {{ old('return_status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="for_repair" {{ old('return_status') === 'for_repair' ? 'selected' : '' }}>For Repair</option>
                                <option value="damaged" {{ old('return_status') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="lost" {{ old('return_status') === 'lost' ? 'selected' : '' }}>Lost</option>
                            </select>
                            @error('return_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Return Remarks</label>
                            <textarea name="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes about condition or return details">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('warehouse.service-units.show', $serviceUnit) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-success px-4">Save Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
