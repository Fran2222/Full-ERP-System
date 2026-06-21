@php
    $isEdit = isset($projectVehicle);
    $selectedDrivers = old('driver_ids', $isEdit ? $projectVehicle->drivers->pluck('id')->map(fn($id)=>(string)$id)->toArray() : []);
    $selectedCount = count($selectedDrivers);
@endphp

<style>
    .vehicle-form-card {
        border: 1px solid #eef1f7;
        box-shadow: 0 10px 30px rgba(17,38,146,.04);
    }

    .vehicle-multi-select {
        position: relative;
    }

    .vehicle-multi-trigger {
        min-height: 44px;
        border: 1px solid #3a57e8;
        background: #fff;
        color: #1f2937;
        border-radius: 8px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 14px 0 16px;
        text-align: left;
    }

    .vehicle-multi-trigger .vehicle-selected-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .vehicle-multi-menu {
        display: none;
        position: absolute;
        top: calc(100% + 7px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e0e5f2;
        border-radius: 8px;
        box-shadow: 0 14px 32px rgba(17,38,146,.13);
        z-index: 1060;
        overflow: hidden;
    }

    .vehicle-multi-select.open .vehicle-multi-menu {
        display: block;
    }

    .vehicle-driver-search {
        border: 1px solid #e0e5f2;
        border-radius: 6px;
        min-height: 44px;
    }

    .vehicle-multi-options {
        max-height: 210px;
        overflow-y: auto;
        padding: 6px 0;
    }

    .vehicle-driver-option {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 10px 18px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 14px;
        color: #6c757d;
        font-size: 15px;
    }

    .vehicle-driver-option.selected {
        color: #0d6efd;
        background: #f0f5ff;
    }

    .vehicle-driver-option:hover {
        background: #f7f9ff;
    }

    .vehicle-check {
        width: 18px;
        font-weight: 700;
        color: #198754;
        visibility: hidden;
    }

    .vehicle-driver-option.selected .vehicle-check {
        visibility: visible;
    }

    .vehicle-driver-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px 6px 7px;
        border-radius: 999px;
        background: #f5f7fb;
        color: #1f2937;
        font-weight: 600;
        margin: 3px 6px 3px 0;
    }

    .vehicle-driver-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #3a57e8;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<form action="{{ $action }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <strong>Please check the form:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card rounded-4 mb-5 vehicle-form-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Vehicle Code</label>
                    <input class="form-control" value="{{ $isEdit ? $projectVehicle->vehicle_code : $nextCode }}" readonly>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Plate # / Vehicle Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="plate_name"
                           class="form-control @error('plate_name') is-invalid @enderror"
                           value="{{ old('plate_name', $isEdit ? $projectVehicle->plate_name : '') }}"
                           required>
                    @error('plate_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label">Driver Name/s <span class="text-danger">*</span></label>

                    <div class="vehicle-multi-select" id="vehicleDriverMultiSelect">
                        <button type="button" class="vehicle-multi-trigger" id="vehicleDriverTrigger">
                            <span class="vehicle-selected-text" id="vehicleDriverSelectedText">
                                {{ $selectedCount > 0 ? $selectedCount . ' Driver/s Selected' : 'Select driver/s' }}
                            </span>
                            <span>▾</span>
                        </button>

                        <div class="vehicle-multi-menu">
                            <div class="p-2">
                                <input type="text"
                                       class="form-control vehicle-driver-search"
                                       id="vehicleDriverSearch"
                                       placeholder="Search driver...">
                            </div>

                            <div class="vehicle-multi-options" id="vehicleDriverOptions">
                                @foreach($users as $user)
                                    @php
                                        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? $user->email ?? 'User #'.$user->id);
                                        $isSelected = in_array((string)$user->id, $selectedDrivers, true);
                                    @endphp
                                    <button type="button"
                                            class="vehicle-driver-option {{ $isSelected ? 'selected' : '' }}"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $name }}"
                                            data-search="{{ strtolower($name . ' ' . ($user->email ?? '')) }}">
                                        <span class="vehicle-check">✓</span>
                                        <span>{{ $name }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="vehicle-no-driver d-none px-3 py-2 text-muted">No driver found.</div>
                        </div>
                    </div>

                    <div id="vehicleDriverHiddenInputs">
                        @foreach($selectedDrivers as $driverId)
                            <input type="hidden" name="driver_ids[]" value="{{ $driverId }}">
                        @endforeach
                    </div>

                    @error('driver_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('driver_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ old('status', $isEdit ? $projectVehicle->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $isEdit ? $projectVehicle->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Optional description">{{ old('description', $isEdit ? $projectVehicle->description : '') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ $isEdit ? route('project-vehicles.show', $projectVehicle->id) : route('project-vehicles.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Vehicle' : 'Save Vehicle' }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
$(function () {
    let selectedDrivers = @json(array_values($selectedDrivers));

    function renderHiddenInputs() {
        let holder = $('#vehicleDriverHiddenInputs');
        holder.empty();

        selectedDrivers.forEach(function (id) {
            holder.append(`<input type="hidden" name="driver_ids[]" value="${id}">`);
        });

        $('#vehicleDriverSelectedText').text(
            selectedDrivers.length > 0
                ? selectedDrivers.length + ' Driver/s Selected'
                : 'Select driver/s'
        );
    }

    $('#vehicleDriverTrigger').on('click', function (e) {
        e.stopPropagation();
        $('#vehicleDriverMultiSelect').toggleClass('open');
        $('#vehicleDriverSearch').val('').trigger('input').focus();
    });

    $('.vehicle-multi-menu').on('click', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', function () {
        $('#vehicleDriverMultiSelect').removeClass('open');
    });

    $('.vehicle-driver-option').on('click', function () {
        let id = String($(this).data('id'));

        if (selectedDrivers.includes(id)) {
            selectedDrivers = selectedDrivers.filter(item => item !== id);
            $(this).removeClass('selected');
        } else {
            selectedDrivers.push(id);
            $(this).addClass('selected');
        }

        renderHiddenInputs();
    });

    $('#vehicleDriverSearch').on('input', function () {
        let keyword = ($(this).val() || '').toLowerCase();
        let visible = 0;

        $('.vehicle-driver-option').each(function () {
            let matched = String($(this).data('search') || '').includes(keyword);
            $(this).toggle(matched);
            if (matched) visible++;
        });

        $('.vehicle-no-driver').toggleClass('d-none', visible > 0);
    });

    renderHiddenInputs();
});
</script>
@endpush
