@php
    $isEdit = isset($projectGasSlip);
    $selectedVehicle = old('project_vehicle_id', $isEdit ? $projectGasSlip->project_vehicle_id : null);
    $selectedDrivers = old('driver_ids', $isEdit ? $projectGasSlip->drivers->pluck('id')->map(fn($id)=>(string)$id)->toArray() : []);
    $selectedVehicleLabel = 'Select Plate #';

    foreach($vehicles as $vehicle) {
        if((string)$selectedVehicle === (string)$vehicle->id) {
            $selectedVehicleLabel = $vehicle->plate_name . ' (' . $vehicle->vehicle_code . ')';
            break;
        }
    }

    $issuedDateValue = old('issued_date', $isEdit ? optional($projectGasSlip->issued_date)->format('Y-m-d') : now()->format('Y-m-d'));
    $returnedDateValue = old('returned_date', $isEdit ? optional($projectGasSlip->returned_date)->format('Y-m-d H:i:s') : '');
@endphp

<style>
    .gas-form-card {
        border: 1px solid #eef1f7;
        box-shadow: 0 10px 30px rgba(17,38,146,.04);
    }

    .gas-date-picker .form-control {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-right: 0;
        border-radius: 6px 0 0 6px;
        background: #fff;
    }

    .gas-date-picker .input-group-text {
        height: 44px;
        border: 1px solid #e0e5f2;
        border-left: 0;
        border-radius: 0 6px 6px 0;
        background: #fff;
        color: #6c757d;
        cursor: pointer;
    }

    .gas-date-picker svg {
        width: 18px;
        height: 18px;
        display: block;
    }

    .gas-bottom-gap {
        margin-bottom: 5rem;
    }

    .gas-custom-select {
        position: relative;
    }

    .gas-select-trigger {
        min-height: 44px;
        border: 1px solid #e0e5f2;
        background: #fff;
        color: #1f2937;
        border-radius: 6px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 14px 0 16px;
        text-align: left;
    }

    .gas-select-trigger.active {
        border-color: #3a57e8;
    }

    .gas-selected-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .gas-select-menu {
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

    .gas-custom-select.open .gas-select-menu {
        display: block;
    }

    .gas-search-input {
        border: 1px solid #e0e5f2;
        border-radius: 6px;
        min-height: 44px;
    }

    .gas-select-options {
        max-height: 220px;
        overflow-y: auto;
        padding: 6px 0;
    }

    .gas-select-option {
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

    .gas-select-option.selected {
        color: #0d6efd;
        background: #f0f5ff;
    }

    .gas-select-option:hover {
        background: #f7f9ff;
    }

    .gas-check {
        width: 18px;
        font-weight: 700;
        color: #198754;
        visibility: hidden;
    }

    .gas-select-option.selected .gas-check {
        visibility: visible;
    }

    .gas-form-actions {
        padding-bottom: 1rem;
    }
</style>

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="gasSlipForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if($errors->any())
        <div id="gasValidationErrors" class="d-none">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card rounded-4 mb-5 gas-form-card gas-bottom-gap">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">PO # <span class="text-danger">*</span></label>
                    <input type="text" name="po_no" class="form-control @error('po_no') is-invalid @enderror" value="{{ old('po_no', $isEdit ? $projectGasSlip->po_no : '') }}" placeholder="Enter PO number" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Plate # <span class="text-danger">*</span></label>
                    <div class="gas-custom-select" id="gasVehicleCustomSelect">
                        <button type="button" class="gas-select-trigger {{ $selectedVehicle ? 'active' : '' }}" id="gasVehicleTrigger">
                            <span class="gas-selected-text" id="gasVehicleSelectedText">{{ $selectedVehicleLabel }}</span>
                            <span>▾</span>
                        </button>

                        <div class="gas-select-menu">
                            <div class="p-2">
                                <input type="text" class="form-control gas-search-input" id="gasVehicleSearch" placeholder="Search plate...">
                            </div>

                            <div class="gas-select-options">
                                @foreach($vehicles as $vehicle)
                                    @php
                                        $vehicleLabel = $vehicle->plate_name . ' (' . $vehicle->vehicle_code . ')';
                                        $isSelectedVehicle = (string)$selectedVehicle === (string)$vehicle->id;
                                    @endphp
                                    <button type="button"
                                            class="gas-select-option gas-vehicle-option {{ $isSelectedVehicle ? 'selected' : '' }}"
                                            data-id="{{ $vehicle->id }}"
                                            data-label="{{ $vehicleLabel }}"
                                            data-search="{{ strtolower($vehicleLabel) }}">
                                        <span class="gas-check">✓</span>
                                        <span>{{ $vehicleLabel }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="gas-vehicle-no-result d-none px-3 py-2 text-muted">No plate found.</div>
                        </div>
                    </div>
                    <input type="hidden" name="project_vehicle_id" id="gasVehicleInput" value="{{ $selectedVehicle }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                    <div class="gas-custom-select" id="gasDriverCustomSelect">
                        <button type="button" class="gas-select-trigger {{ count($selectedDrivers) ? 'active' : '' }}" id="gasDriverTrigger">
                            <span class="gas-selected-text" id="gasDriverSelectedText">{{ count($selectedDrivers) ? count($selectedDrivers) . ' Driver/s Selected' : 'Select driver/s' }}</span>
                            <span>▾</span>
                        </button>

                        <div class="gas-select-menu">
                            <div class="p-2">
                                <input type="text" class="form-control gas-search-input" id="gasDriverSearch" placeholder="Search driver...">
                            </div>

                            <div class="gas-select-options" id="gasDriverOptions"></div>
                            <div class="gas-driver-no-result d-none px-3 py-2 text-muted">No driver found.</div>
                        </div>
                    </div>

                    <div id="gasDriverHiddenInputs">
                        @foreach($selectedDrivers as $driverId)
                            <input type="hidden" name="driver_ids[]" value="{{ $driverId }}">
                        @endforeach
                    </div>
                    <small class="text-muted">Drivers are filtered by selected plate.</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Issued Date <span class="text-danger">*</span></label>
                    <div class="input-group gas-date-picker wrap_flatpicker">
                        <input type="text"
                               name="issued_date"
                               class="form-control @error('issued_date') is-invalid @enderror"
                               value="{{ $issuedDateValue }}"
                               placeholder="Select Date.."
                               autocomplete="off"
                               data-input
                               required>
                        <span class="input-group-text input-button pointer-event" title="toggle" data-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Returned Date</label>
                    <input type="text"
                           @if($isEdit) name="returned_date" @endif
                           class="form-control"
                           value="{{ $returnedDateValue }}"
                           {{ $isEdit ? '' : 'readonly disabled' }}
                           placeholder="Auto-filled when Done is checked">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ $isEdit ? ucfirst($projectGasSlip->status) : 'Issued' }}" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="amount" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $isEdit ? $projectGasSlip->amount : '') }}" placeholder="0.00" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $isEdit ? $projectGasSlip->location : '') }}" placeholder="Enter location">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Upload Attachment</label>
                    <input type="file" name="attachment" class="form-control">
                    @if($isEdit && $projectGasSlip->attachment_path)
                        <small>
                            <a href="{{ asset('storage/'.$projectGasSlip->attachment_path) }}" target="_blank">{{ $projectGasSlip->attachment_original_name }}</a>
                        </small>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Optional remarks">{{ old('remarks', $isEdit ? $projectGasSlip->remarks : '') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 gas-form-actions">
                <a href="{{ $isEdit ? route('project-gas-slips.show', $projectGasSlip->id) : route('project-gas-slips.index') }}" class="btn btn-light">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Update Gas Slip' : 'Save Gas Slip' }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
$(function () {
    const vehicleDrivers = @json($vehicleDrivers);
    let selectedDrivers = @json(array_values($selectedDrivers).map(String));

    function driverName(driver) {
        return driver.name || ('User #' + driver.id);
    }

    function renderDriverInputs() {
        let holder = $('#gasDriverHiddenInputs');
        holder.empty();

        selectedDrivers.forEach(function (id) {
            holder.append(`<input type="hidden" name="driver_ids[]" value="${id}">`);
        });

        $('#gasDriverSelectedText').text(
            selectedDrivers.length > 0
                ? selectedDrivers.length + ' Driver/s Selected'
                : 'Select driver/s'
        );

        $('#gasDriverTrigger').toggleClass('active', selectedDrivers.length > 0);
    }

    function loadDrivers() {
        let vehicleId = String($('#gasVehicleInput').val() || '');
        let drivers = vehicleDrivers[vehicleId] || [];
        let options = $('#gasDriverOptions');
        options.empty();

        drivers.forEach(function (driver) {
            let id = String(driver.id);
            let name = driverName(driver);
            let selected = selectedDrivers.includes(id) ? 'selected' : '';

            options.append(`
                <button type="button"
                        class="gas-select-option gas-driver-option ${selected}"
                        data-id="${id}"
                        data-name="${name}"
                        data-search="${name.toLowerCase()}">
                    <span class="gas-check">✓</span>
                    <span>${name}</span>
                </button>
            `);
        });

        $('.gas-driver-no-result').toggleClass('d-none', drivers.length > 0);
        renderDriverInputs();
    }

    function openSelect(wrapper, searchInput) {
        $('.gas-custom-select').not(wrapper).removeClass('open');
        wrapper.toggleClass('open');
        searchInput.val('').trigger('input').focus();
    }

    $('#gasVehicleTrigger').on('click', function (e) {
        e.stopPropagation();
        openSelect($('#gasVehicleCustomSelect'), $('#gasVehicleSearch'));
    });

    $('#gasDriverTrigger').on('click', function (e) {
        e.stopPropagation();
        openSelect($('#gasDriverCustomSelect'), $('#gasDriverSearch'));
    });

    $('.gas-select-menu').on('click', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', function () {
        $('.gas-custom-select').removeClass('open');
    });

    $('.gas-vehicle-option').on('click', function () {
        let id = String($(this).data('id'));
        let label = $(this).data('label');

        $('#gasVehicleInput').val(id);
        $('#gasVehicleSelectedText').text(label);
        $('#gasVehicleTrigger').addClass('active');

        $('.gas-vehicle-option').removeClass('selected');
        $(this).addClass('selected');

        selectedDrivers = [];
        loadDrivers();
        $('#gasVehicleCustomSelect').removeClass('open');
    });

    $(document).on('click', '.gas-driver-option', function () {
        let id = String($(this).data('id'));

        if (selectedDrivers.includes(id)) {
            selectedDrivers = selectedDrivers.filter(item => item !== id);
            $(this).removeClass('selected');
        } else {
            selectedDrivers.push(id);
            $(this).addClass('selected');
        }

        renderDriverInputs();
    });

    $('#gasVehicleSearch').on('input', function () {
        let keyword = ($(this).val() || '').toLowerCase();
        let visible = 0;

        $('.gas-vehicle-option').each(function () {
            let matched = String($(this).data('search') || '').includes(keyword);
            $(this).toggle(matched);
            if (matched) visible++;
        });

        $('.gas-vehicle-no-result').toggleClass('d-none', visible > 0);
    });

    $('#gasDriverSearch').on('input', function () {
        let keyword = ($(this).val() || '').toLowerCase();
        let visible = 0;

        $('.gas-driver-option').each(function () {
            let matched = String($(this).data('search') || '').includes(keyword);
            $(this).toggle(matched);
            if (matched) visible++;
        });

        $('.gas-driver-no-result').toggleClass('d-none', visible > 0);
    });

    if (typeof flatpickr !== 'undefined') {
        $('.wrap_flatpicker').flatpickr({
            wrap: true,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true
        });
    }

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Please check the form',
            html: $('#gasValidationErrors').html(),
            confirmButtonText: 'OK'
        });
    @endif

    $('#gasSlipForm').on('submit', function (e) {
        let errors = [];

        if (!$('input[name="po_no"]').val().trim()) errors.push('PO # is required.');
        if (!$('#gasVehicleInput').val()) errors.push('Plate # is required.');
        if (selectedDrivers.length < 1) errors.push('Driver is required.');
        if (!$('input[name="issued_date"]').val().trim()) errors.push('Issued Date is required.');
        if (!$('input[name="amount"]').val().trim()) errors.push('Amount is required.');

        if (errors.length) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Please check the form',
                html: '<ul class="text-start mb-0">' + errors.map(error => `<li>${error}</li>`).join('') + '</ul>',
                confirmButtonText: 'OK'
            });
        }
    });

    loadDrivers();
});
</script>
@endpush
