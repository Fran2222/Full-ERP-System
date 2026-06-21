@csrf

@php
    $vehicle = $vehicle ?? null;

    $selectedTypeId = old('vehicle_type_id', optional($vehicle)->vehicle_type_id);
    $selectedStatusId = old('status_id', optional($vehicle)->status_id);
    $selectedBranchId = old('assigned_branch_id', optional($vehicle)->assigned_branch_id);
    $selectedDriverId = old('default_driver_id', optional($vehicle)->default_driver_id);

    $fuelSource = isset($fuelTypes) ? $fuelTypes : collect(['Gasoline', 'Diesel', 'Electric', 'Hybrid']);

    $vehiclePhotoUrl = null;
    if ($vehicle && !empty($vehicle->photo_url)) {
        $vehiclePhotoUrl = $vehicle->photo_url;
    } elseif ($vehicle && !empty($vehicle->photo_path)) {
        $vehiclePhotoUrl = asset('storage/' . $vehicle->photo_path);
    }

    $buttonText = $buttonText ?? 'Save Vehicle';
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-4">Vehicle Information</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vehicle Code</label>
                        <input type="text"
                               name="vehicle_code"
                               class="form-control @error('vehicle_code') is-invalid @enderror"
                               value="{{ old('vehicle_code', optional($vehicle)->vehicle_code) }}"
                               placeholder="Auto-generated if blank">
                        @error('vehicle_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plate Number</label>
                        <input type="text"
                               name="plate_number"
                               class="form-control @error('plate_number') is-invalid @enderror"
                               value="{{ old('plate_number', optional($vehicle)->plate_number) }}"
                               placeholder="Example: ABC 1234">
                        @error('plate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                        <select name="vehicle_type_id"
                                class="form-select @error('vehicle_type_id') is-invalid @enderror"
                                required>
                            <option value="">Select Vehicle Type</option>
                            @foreach($types ?? [] as $type)
                                <option value="{{ $type->id }}" {{ (string) $selectedTypeId === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->name ?? $type->title ?? ('Type #' . $type->id) }}
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Brand</label>
                        <input type="text"
                               name="brand"
                               class="form-control @error('brand') is-invalid @enderror"
                               value="{{ old('brand', optional($vehicle)->brand) }}"
                               placeholder="Example: Mitsubishi">
                        @error('brand')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Model</label>
                        <input type="text"
                               name="model"
                               class="form-control @error('model') is-invalid @enderror"
                               value="{{ old('model', optional($vehicle)->model) }}"
                               placeholder="Example: L300">
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Year Model</label>
                        <input type="text"
                               name="year_model"
                               class="form-control @error('year_model') is-invalid @enderror"
                               value="{{ old('year_model', optional($vehicle)->year_model) }}"
                               placeholder="Example: 2020">
                        @error('year_model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text"
                               name="color"
                               class="form-control @error('color') is-invalid @enderror"
                               value="{{ old('color', optional($vehicle)->color) }}"
                               placeholder="Example: White">
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fuel Type</label>
                        <select name="fuel_type"
                                class="form-select @error('fuel_type') is-invalid @enderror">
                            <option value="">Select Fuel Type</option>
                            @foreach($fuelSource as $fuel)
                                @php
                                    $fuelValue = is_object($fuel) ? ($fuel->name ?? $fuel->title ?? '') : $fuel;
                                    $fuelLabel = $fuelValue;
                                    $selectedFuel = old('fuel_type', optional($vehicle)->fuel_type);
                                @endphp
                                @if($fuelValue !== '')
                                    <option value="{{ $fuelValue }}" {{ (string) $selectedFuel === (string) $fuelValue ? 'selected' : '' }}>
                                        {{ $fuelLabel }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('fuel_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Current Odometer</label>
                        <input type="number"
                               name="current_odometer"
                               class="form-control @error('current_odometer') is-invalid @enderror"
                               value="{{ old('current_odometer', optional($vehicle)->current_odometer ?? 0) }}"
                               min="0">
                        @error('current_odometer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Engine No.</label>
                        <input type="text"
                               name="engine_no"
                               class="form-control @error('engine_no') is-invalid @enderror"
                               value="{{ old('engine_no', optional($vehicle)->engine_no) }}">
                        @error('engine_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Chassis No.</label>
                        <input type="text"
                               name="chassis_no"
                               class="form-control @error('chassis_no') is-invalid @enderror"
                               value="{{ old('chassis_no', optional($vehicle)->chassis_no) }}">
                        @error('chassis_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Acquisition Date</label>
                        <input type="date"
                               name="acquisition_date"
                               class="form-control @error('acquisition_date') is-invalid @enderror"
                               value="{{ old('acquisition_date', optional(optional($vehicle)->acquisition_date)->format('Y-m-d')) }}">
                        @error('acquisition_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Acquisition Cost</label>
                        <input type="number"
                               name="acquisition_cost"
                               class="form-control @error('acquisition_cost') is-invalid @enderror"
                               value="{{ old('acquisition_cost', optional($vehicle)->acquisition_cost) }}"
                               step="0.01"
                               min="0">
                        @error('acquisition_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Notes about the vehicle...">{{ old('remarks', optional($vehicle)->remarks) }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-4">Assignment Defaults</h5>

                <div class="mb-3">
                    <label class="form-label">Assigned Branch</label>
                    <select name="assigned_branch_id"
                            class="form-select @error('assigned_branch_id') is-invalid @enderror">
                        <option value="">Select Branch</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name ?? $branch->branch_name ?? ('Branch #' . $branch->id) }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_branch_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Default Driver / Custodian</label>
                    <select name="default_driver_id"
                            class="form-select @error('default_driver_id') is-invalid @enderror">
                        <option value="">Select Driver / Custodian</option>
                        @foreach($drivers ?? [] as $driver)
                            <option value="{{ $driver->id }}" {{ (string) $selectedDriverId === (string) $driver->id ? 'selected' : '' }}>
                                {{ $driver->name ?? trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? '')) ?: ($driver->email ?? ('User #' . $driver->id)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('default_driver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label">Status</label>
                    <select name="status_id"
                            class="form-select @error('status_id') is-invalid @enderror">
                        <option value="">Select Status</option>
                        @foreach($statuses ?? [] as $status)
                            <option value="{{ $status->id }}" {{ (string) $selectedStatusId === (string) $status->id ? 'selected' : '' }}>
                                {{ $status->name ?? $status->title ?? ('Status #' . $status->id) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Vehicle Photo</h5>

                @if($vehiclePhotoUrl)
                    <div class="mb-3">
                        <img src="{{ $vehiclePhotoUrl }}"
                             alt="Vehicle Photo"
                             class="rounded border"
                             style="width: 220px; max-width: 100%; height: 150px; object-fit: cover;">
                    </div>
                @endif

                <input type="file"
                       name="photo"
                       class="form-control @error('photo') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <small class="text-muted d-block mt-2">Accepted: JPG, PNG, WEBP. Max 4MB.</small>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('vehicle.vehicles.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        </div>
    </div>
</div>