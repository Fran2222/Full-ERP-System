@php
    $isStockInPage = request()->routeIs('warehouse.stock-in');
@endphp

<style>
    .warehouse-select2-wrap .select2-container {
        width: 100% !important;
    }

    .warehouse-select2-wrap .select2-container--default .select2-selection--single {
        min-height: 44px;
        border-radius: 12px;
        border-color: #d9dee8;
        display: flex;
        align-items: center;
    }

    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 44px;
        padding-left: 14px;
        padding-right: 36px;
        font-weight: 600;
    }

    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #8a94a6;
        font-weight: 500;
    }

    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 8px;
    }

    .warehouse-select2-dropdown {
        border-color: #d9dee8 !important;
        border-radius: 12px !important;
        overflow: hidden;
    }

    .warehouse-select2-dropdown .select2-search__field {
        border-radius: 10px;
        border-color: #d9dee8 !important;
        min-height: 38px;
        padding: 8px 10px;
        outline: none;
    }

    .warehouse-select2-dropdown .select2-results__option {
        padding: 10px 12px;
        font-weight: 600;
    }

    .warehouse-select2-dropdown .select2-results__option--highlighted {
        background: #315cf6 !important;
    }

    .warehouse-movement-input {
        min-height: 44px;
        border-radius: 12px;
        border-color: #d9dee8;
    }

    .warehouse-movement-input:focus {
        border-color: #3f5cff;
        box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12);
    }

    .warehouse-serial-box {
        border: 1px dashed #cdd6e6;
        border-radius: 14px;
        padding: 14px;
        background: #fbfdff;
    }
</style>

<div class="row">
    <div class="col-md-6 mb-3 warehouse-select2-wrap">
        <label class="form-label">Item</label>
        <select name="item_id"
                class="form-select warehouse-movement-input warehouse-item-select"
                data-placeholder="Search item code or name..."
                required>
            <option value="">Select Item</option>
            @foreach($items as $item)
                @php
                    $itemCode = $item->item_code ?? $item->code;
                    $itemName = $item->item_name ?? $item->name;
                    $unitSymbol = optional($item->unit)->symbol ?? optional($item->unit)->abbreviation ?? optional($item->unit)->name;
                    $isSerialized = (bool) ($item->is_serialized ?? false);
                @endphp

                <option value="{{ $item->id }}"
                        data-code="{{ $itemCode }}"
                        data-name="{{ $itemName }}"
                        data-is-serialized="{{ $isSerialized ? 1 : 0 }}"
                        {{ old('item_id') == $item->id ? 'selected' : '' }}>
                    {{ $itemCode }} - {{ $itemName }} {{ $unitSymbol ? '(' . $unitSymbol . ')' : '' }}{{ $isSerialized ? ' - Serialized' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Quantity</label>
        <input type="number"
               step="0.01"
               min="0.01"
               name="quantity"
               class="form-control warehouse-movement-input warehouse-quantity-input"
               value="{{ old('quantity') }}"
               required>
        @if($isStockInPage)
            <small class="text-secondary warehouse-serialized-help d-none">
                For serialized items, quantity will auto-match the serial count.
            </small>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Branch <span class="text-secondary small">(optional)</span></label>
        <select name="branch_id" class="form-select warehouse-movement-input">
            <option value="">Central / Unassigned Warehouse</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Location</label>
        <select name="location_id" class="form-select warehouse-movement-input" required>
            <option value="">Select Location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                    {{ $location->location_name ?? $location->name }}
                    {{ optional($location->branch)->name ? '- ' . optional($location->branch)->name : '- Central / Unassigned' }}
                </option>
            @endforeach
        </select>
    </div>

    @if($isStockInPage)
        <div class="col-md-12 mb-3 warehouse-serial-section d-none">
            <div class="warehouse-serial-box">
                <label class="form-label">Serial Numbers</label>
                <textarea name="serial_numbers_text"
                          class="form-control warehouse-movement-input warehouse-serial-textarea"
                          rows="5"
                          placeholder="Enter one serial number per line">{{ old('serial_numbers_text') }}</textarea>
                <small class="text-secondary">
                    Enter one serial number per line. Quantity will auto-update based on entered serial count.
                </small>
            </div>
        </div>
    @endif

    <div class="col-md-6 mb-3">
        <label class="form-label">Reference No.</label>
        <input type="text"
               name="reference_no"
               class="form-control warehouse-movement-input"
               value="{{ old('reference_no') }}"
               placeholder="Auto-generated if blank">
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks"
                  class="form-control warehouse-movement-input"
                  rows="3">{{ old('remarks') }}</textarea>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isStockInPage = @json($isStockInPage);

        function hasWarehouseSelect2() {
            return window.jQuery && typeof jQuery.fn.select2 === 'function';
        }

        function loadWarehouseSelect2Assets(callback) {
            if (!window.jQuery) {
                return;
            }

            if (hasWarehouseSelect2()) {
                callback();
                return;
            }

            if (!document.getElementById('warehouse-select2-css')) {
                const css = document.createElement('link');
                css.id = 'warehouse-select2-css';
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                document.head.appendChild(css);
            }

            if (!document.getElementById('warehouse-select2-js')) {
                const script = document.createElement('script');
                script.id = 'warehouse-select2-js';
                script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                script.onload = callback;
                document.body.appendChild(script);
            } else {
                const waitForSelect2 = setInterval(function () {
                    if (hasWarehouseSelect2()) {
                        clearInterval(waitForSelect2);
                        callback();
                    }
                }, 100);
            }
        }

        function selectedItemIsSerialized() {
            const selected = document.querySelector('.warehouse-item-select option:checked');
            return selected && String(selected.dataset.isSerialized || '0') === '1';
        }

        function serialCount() {
            const textarea = document.querySelector('.warehouse-serial-textarea');
            if (!textarea) return 0;

            return textarea.value
                .split(/\r\n|\r|\n/)
                .map(value => value.trim())
                .filter(Boolean)
                .length;
        }

        function syncSerialSection() {
            const qtyInput = document.querySelector('.warehouse-quantity-input');
            const serialSection = document.querySelector('.warehouse-serial-section');
            const serialHelp = document.querySelector('.warehouse-serialized-help');
            const isSerialized = selectedItemIsSerialized();

            if (!isStockInPage || !serialSection || !qtyInput) {
                return;
            }

            if (isSerialized) {
                serialSection.classList.remove('d-none');
                if (serialHelp) serialHelp.classList.remove('d-none');
                qtyInput.readOnly = true;
                qtyInput.value = serialCount() || '';
            } else {
                serialSection.classList.add('d-none');
                if (serialHelp) serialHelp.classList.add('d-none');
                qtyInput.readOnly = false;
            }
        }

        function initWarehouseItemSelect2() {
            if (!hasWarehouseSelect2()) {
                return;
            }

            $('.warehouse-item-select').each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }

                $select.select2({
                    width: '100%',
                    placeholder: $select.data('placeholder') || 'Search item code or name...',
                    allowClear: true,
                    dropdownCssClass: 'warehouse-select2-dropdown',
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }

                        if (typeof data.text === 'undefined') {
                            return null;
                        }

                        const term = params.term.toLowerCase();
                        const text = data.text.toLowerCase();
                        const element = data.element;
                        const code = element ? String($(element).data('code') || '').toLowerCase() : '';
                        const name = element ? String($(element).data('name') || '').toLowerCase() : '';

                        if (text.indexOf(term) > -1 || code.indexOf(term) > -1 || name.indexOf(term) > -1) {
                            return data;
                        }

                        return null;
                    }
                });

                $select.on('change', syncSerialSection);
            });
        }

        document.querySelector('.warehouse-item-select')?.addEventListener('change', syncSerialSection);
        document.querySelector('.warehouse-serial-textarea')?.addEventListener('input', syncSerialSection);

        loadWarehouseSelect2Assets(function () {
            initWarehouseItemSelect2();
            syncSerialSection();
        });

        syncSerialSection();
    });
</script>
