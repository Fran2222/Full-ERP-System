@csrf

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Vehicle Information</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vehicle Code</label>
                        <input type="text" name="vehicle_code" class="form-control @error('vehicle_code') is-invalid @enderror"
                               value="{{ old('vehicle_code', $vehicle->vehicle_code ?? '') }}"
                               placeholder="Auto-generated if blank">
                        @error('vehicle_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plate Number</label>
                        <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror"
                               value="{{ old('plate_number', $vehicle->plate_number ?? '') }}"
                               placeholder="Example: ABC 1234">
                        @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select name="vehicle_type_id" class="form-select @error('vehicle_type_id') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected((string) old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') === (string) $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand', $vehicle->brand ?? '') }}" placeholder="Mitsubishi">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model ?? '') }}" placeholder="L300">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Year Model</label>
                        <input type="number" name="year_model" class="form-control" value="{{ old('year_model', $vehicle->year_model ?? '') }}" min="1900" max="{{ date('Y') + 1 }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fuel Type</label>
                        <select name="fuel_type" class="form-select">
                            @php($fuel = old('fuel_type', $vehicle->fuel_type ?? ''))
                            <option value="">Select Fuel Type</option>
                            @foreach(['Diesel', 'Gasoline', 'Electric', 'Hybrid'] as $option)
                                <option value="{{ $option }}" @selected($fuel === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Current Odometer</label>
                        <input type="number" name="current_odometer" class="form-control" value="{{ old('current_odometer', $vehicle->current_odometer ?? 0) }}" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Engine No.</label>
                        <input type="text" name="engine_no" class="form-control" value="{{ old('engine_no', $vehicle->engine_no ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chassis No.</label>
                        <input type="text" name="chassis_no" class="form-control" value="{{ old('chassis_no', $vehicle->chassis_no ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Acquisition Date</label>
                        <input type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', isset($vehicle) && $vehicle->acquisition_date ? $vehicle->acquisition_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Acquisition Cost</label>
                        <input type="number" step="0.01" name="acquisition_cost" class="form-control" value="{{ old('acquisition_cost', $vehicle->acquisition_cost ?? '') }}" min="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="4" placeholder="Notes about the vehicle...">{{ old('remarks', $vehicle->remarks ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Assignment Defaults</h5>

                <div class="mb-3">
                    <label class="form-label">Assigned Branch</label>
                    <select name="assigned_branch_id" class="form-select">
                        <option value="">No branch selected</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('assigned_branch_id', $vehicle->assigned_branch_id ?? '') === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Default Driver / Custodian</label>
                    <select name="default_driver_id" class="form-select">
                        <option value="">No default driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected((string) old('default_driver_id', $vehicle->default_driver_id ?? '') === (string) $driver->id)>
                                {{ $driver->full_name ?: ($driver->name ?? $driver->email) }}{{ $driver->email ? ' - ' . $driver->email : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_id" class="form-select">
                        <option value="">Use default status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected((string) old('status_id', $vehicle->status_id ?? '') === (string) $status->id)>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Vehicle Photo</h5>

                @if(isset($vehicle) && $vehicle->photo_url)
                    <img src="{{ $vehicle->photo_url }}" class="img-fluid rounded-3 mb-3" alt="Vehicle photo">
                @endif

                <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                <div class="small text-muted mt-2">Accepted: JPG, PNG, WEBP. Max 4MB.</div>
                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('vehicle.vehicles.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Save Vehicle' }}</button>
        </div>
    </div>
</div>
