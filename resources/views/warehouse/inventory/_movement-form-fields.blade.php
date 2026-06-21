@php
    $isStockInPage = request()->routeIs('warehouse.stock-in');
    $isStockOutPage = request()->routeIs('warehouse.stock-out');
    $isAdjustmentPage = request()->routeIs('warehouse.adjustment');
    $usePicker = $isStockOutPage;
@endphp

<style>
    .warehouse-select2-wrap .select2-container { width: 100% !important; }
    .warehouse-select2-wrap .select2-container--default .select2-selection--single {
        min-height: 44px; border-radius: 12px; border-color: #d9dee8; display: flex; align-items: center;
    }
    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827; line-height: 44px; padding-left: 14px; padding-right: 36px; font-weight: 600;
    }
    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #8a94a6; font-weight: 500; }
    .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow { height: 44px; right: 8px; }
    .warehouse-select2-dropdown { border-color: #d9dee8 !important; border-radius: 12px !important; overflow: hidden; }
    .warehouse-select2-dropdown .select2-search__field { border-radius: 10px; border-color: #d9dee8 !important; min-height: 38px; padding: 8px 10px; outline: none; }
    .warehouse-select2-dropdown .select2-results__option { padding: 10px 12px; font-weight: 600; }
    .warehouse-select2-dropdown .select2-results__option--highlighted { background: #315cf6 !important; }

    .warehouse-movement-input { min-height: 44px; border-radius: 12px; border-color: #d9dee8; }
    .warehouse-movement-input:focus { border-color: #3f5cff; box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12); }
    .warehouse-serial-box, .warehouse-stockout-serial-box { border: 1px dashed #cdd6e6; border-radius: 14px; padding: 14px; background: #fbfdff; }
    .warehouse-stockout-picker-row .wmc-selected-material-summary { border: 1px solid rgba(63,92,255,.24); border-radius: 14px; padding: 10px 12px; background: #fff; min-height: 58px; }
    .warehouse-stockout-picker-row .wmc-open-item-picker { width: 100%; border-radius: 10px; }
    .warehouse-stockout-serial-list { max-height: 210px; overflow-y: auto; border: 1px solid #e8edf7; border-radius: 12px; background: #fff; }
    .warehouse-stockout-serial-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 10px; border-bottom:1px solid #eef2f7; }
    .warehouse-stockout-serial-row:last-child { border-bottom:0; }
    .warehouse-stockout-serial-row label { margin:0; cursor:pointer; display:flex; align-items:center; gap:9px; }
    .warehouse-stockout-chip { display:inline-flex; align-items:center; gap:6px; border:1px solid rgba(63,92,255,.22); background:rgba(63,92,255,.06); color:#315cf6; border-radius:999px; padding:3px 9px; font-size:12px; margin:3px 4px 0 0; }
    .warehouse-stockout-chip button { border:0; background:transparent; color:#315cf6; padding:0; line-height:1; }
</style>

<div class="row">
    <div class="col-md-6 mb-3 {{ $usePicker ? 'warehouse-stockout-picker-row' : 'warehouse-select2-wrap' }}" data-item-picker-context="{{ $isStockOutPage ? 'stock-out' : 'movement' }}">
        <label class="form-label">Item</label>

        @if($usePicker)
            <input type="hidden" name="item_id" class="warehouse-picker-item-id" value="{{ old('item_id') }}" required>

            <div class="wmc-selected-material-summary">
                <div class="text-muted">No item selected.</div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm wmc-open-item-picker open-stockout-picker mt-2 w-100">
                Search / Select Item
            </button>

            <small class="text-secondary d-block mt-1">Search item, view details, then select serials below if serialized.</small>
        @else
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
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control warehouse-movement-input warehouse-quantity-input" value="{{ old('quantity') }}" required>
        @if($isStockInPage)
            <small class="text-secondary warehouse-serialized-help d-none">For serialized items, quantity will auto-match the serial count.</small>
        @elseif($usePicker)
            <small class="text-secondary warehouse-stockout-qty-help d-none">For serialized stock out, quantity follows selected serial count.</small>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Branch <span class="text-secondary small">(optional)</span></label>
        <select name="branch_id" class="form-select warehouse-movement-input warehouse-branch-select">
            <option value="">Central / Unassigned Warehouse</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Location</label>
        <select name="location_id" class="form-select warehouse-movement-input warehouse-location-select" required>
            <option value="">Select Location</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" data-branch-id="{{ optional($location->branch)->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
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
                <textarea name="serial_numbers_text" class="form-control warehouse-movement-input warehouse-serial-textarea" rows="5" placeholder="Enter one serial number per line">{{ old('serial_numbers_text') }}</textarea>
                <small class="text-secondary">Enter one serial number per line. Quantity will auto-update based on entered serial count.</small>
            </div>
        </div>
    @endif

    @if($usePicker)
        <div class="col-md-12 mb-3 warehouse-stockout-serial-section d-none">
            <div class="warehouse-stockout-serial-box">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <label class="form-label mb-0">Serial Numbers <span class="text-danger">*</span></label>
                        <div class="form-text mb-0">No CTRL needed. Tick the serials to stock out from the selected location only.</div>
                    </div>
                    <span class="badge bg-primary warehouse-stockout-selected-count">Selected: 0</span>
                </div>
                <input type="text" class="form-control warehouse-movement-input warehouse-stockout-serial-search mb-2" placeholder="Search serial no...">
                <div class="d-flex justify-content-end gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary warehouse-stockout-select-visible">Select All Visible</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary warehouse-stockout-clear">Clear</button>
                </div>
                <div class="warehouse-stockout-serial-list"></div>
                <div class="warehouse-stockout-hidden-serials"></div>
                <div class="warehouse-stockout-selected-chips mt-2 text-secondary small">No serial selected yet.</div>
            </div>
        </div>
    @endif

    <div class="col-md-6 mb-3">
        <label class="form-label">Reference No.</label>
        <input type="text" name="reference_no" class="form-control warehouse-movement-input" value="{{ old('reference_no') }}" placeholder="Auto-generated if blank">
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control warehouse-movement-input" rows="3">{{ old('remarks') }}</textarea>
    </div>
</div>

@if($usePicker)
    <script src="{{ asset('assets/js/wmc-item-picker.js') }}"></script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isStockInPage = @json($isStockInPage);
        const usePicker = @json($usePicker);

        function hasWarehouseSelect2() { return window.jQuery && typeof jQuery.fn.select2 === 'function'; }
        function loadWarehouseSelect2Assets(callback) {
            if (!window.jQuery) return;
            if (hasWarehouseSelect2()) { callback(); return; }
            if (!document.getElementById('warehouse-select2-css')) {
                const css = document.createElement('link'); css.id = 'warehouse-select2-css'; css.rel = 'stylesheet'; css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'; document.head.appendChild(css);
            }
            if (!document.getElementById('warehouse-select2-js')) {
                const script = document.createElement('script'); script.id = 'warehouse-select2-js'; script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'; script.onload = callback; document.body.appendChild(script);
            } else {
                const waitForSelect2 = setInterval(function () { if (hasWarehouseSelect2()) { clearInterval(waitForSelect2); callback(); } }, 100);
            }
        }

        function selectedItemIsSerialized() {
            const selected = document.querySelector('.warehouse-item-select option:checked');
            return selected && String(selected.dataset.isSerialized || '0') === '1';
        }
        function serialCount() {
            const textarea = document.querySelector('.warehouse-serial-textarea');
            if (!textarea) return 0;
            return textarea.value.split(/\r\n|\r|\n/).map(value => value.trim()).filter(Boolean).length;
        }
        function syncSerialSection() {
            const qtyInput = document.querySelector('.warehouse-quantity-input');
            const serialSection = document.querySelector('.warehouse-serial-section');
            const serialHelp = document.querySelector('.warehouse-serialized-help');
            const isSerialized = selectedItemIsSerialized();
            if (!isStockInPage || !serialSection || !qtyInput) return;
            if (isSerialized) {
                serialSection.classList.remove('d-none'); if (serialHelp) serialHelp.classList.remove('d-none'); qtyInput.readOnly = true; qtyInput.value = serialCount() || '';
            } else {
                serialSection.classList.add('d-none'); if (serialHelp) serialHelp.classList.add('d-none'); qtyInput.readOnly = false;
            }
        }
        function initWarehouseItemSelect2() {
            if (!hasWarehouseSelect2() || usePicker) return;
            window.jQuery('.warehouse-item-select').each(function () {
                const $select = window.jQuery(this); if ($select.hasClass('select2-hidden-accessible')) return;
                $select.select2({ width: '100%', placeholder: $select.data('placeholder') || 'Search item...', allowClear: true, dropdownCssClass: 'warehouse-select2-dropdown' }).on('change', syncSerialSection);
            });
            syncSerialSection();
        }
        if (!usePicker) { loadWarehouseSelect2Assets(initWarehouseItemSelect2); }
        document.querySelectorAll('.warehouse-item-select').forEach(select => select.addEventListener('change', syncSerialSection));
        const serialTextarea = document.querySelector('.warehouse-serial-textarea'); if (serialTextarea) serialTextarea.addEventListener('input', syncSerialSection);

        if (!usePicker) return;

        const pickerRow = document.querySelector('.warehouse-stockout-picker-row');
        const itemIdInput = document.querySelector('.warehouse-picker-item-id');
        const qtyInput = document.querySelector('.warehouse-quantity-input');
        const branchSelect = document.querySelector('.warehouse-branch-select');
        const locationSelect = document.querySelector('.warehouse-location-select');
        const serialSection = document.querySelector('.warehouse-stockout-serial-section');
        const serialList = document.querySelector('.warehouse-stockout-serial-list');
        const serialSearch = document.querySelector('.warehouse-stockout-serial-search');
        const serialHidden = document.querySelector('.warehouse-stockout-hidden-serials');
        const serialChips = document.querySelector('.warehouse-stockout-selected-chips');
        const serialBadge = document.querySelector('.warehouse-stockout-selected-count');
        const qtyHelp = document.querySelector('.warehouse-stockout-qty-help');
        let selectedItem = null;
        let serialRows = [];
        let selectedSerials = new Map();

        function syncPickerSource() {
            if (!pickerRow) return;
            pickerRow.dataset.sourceBranchId = branchSelect ? (branchSelect.value || '') : '';
            pickerRow.dataset.sourceLocationId = locationSelect ? (locationSelect.value || '') : '';
            pickerRow.dataset.branchId = branchSelect ? (branchSelect.value || '') : '';
            pickerRow.dataset.locationId = locationSelect ? (locationSelect.value || '') : '';
        }


        function openStockoutPicker() {
            syncPickerSource();
            if (!locationSelect || !locationSelect.value) {
                alert('Please select Location first before choosing an item.');
                return;
            }
            if (window.WMCItemPicker && typeof window.WMCItemPicker.openPicker === 'function') {
                window.WMCItemPicker.openPicker(pickerRow);
            } else {
                alert('Global item picker script not loaded. Please refresh with CTRL + F5.');
            }
        }

        document.querySelectorAll('.open-stockout-picker').forEach(function (button) {
            if (button.dataset.boundStockoutPicker === '1') return;
            button.dataset.boundStockoutPicker = '1';
            button.addEventListener('click', openStockoutPicker);
        });

        function resetSelectedItem() {
            selectedItem = null;
            selectedSerials.clear();
            if (itemIdInput) itemIdInput.value = '';
            if (qtyInput) { qtyInput.value = ''; qtyInput.readOnly = false; }
            if (serialSection) serialSection.classList.add('d-none');
            renderSerials();
        }

        function renderSerials() {
            if (!serialList) return;
            const q = (serialSearch ? serialSearch.value : '').toLowerCase().trim();
            const visible = serialRows.filter(row => !q || String(row.text || '').toLowerCase().includes(q));
            serialList.innerHTML = visible.length ? visible.map(row => {
                const checked = selectedSerials.has(String(row.id)) ? 'checked' : '';
                return `<div class="warehouse-stockout-serial-row" data-serial-id="${row.id}"><label><input type="checkbox" class="warehouse-stockout-serial-check" value="${row.id}" ${checked}> <span>${row.text}</span></label><span class="badge bg-success">Available</span></div>`;
            }).join('') : '<div class="text-muted small p-3">No available serials found for this selected item/location.</div>';
            if (serialHidden) {
                serialHidden.innerHTML = Array.from(selectedSerials.keys()).map(id => `<input type="hidden" name="serial_ids[]" value="${id}">`).join('');
            }
            const selectedCount = selectedSerials.size;
            if (serialBadge) serialBadge.textContent = `Selected: ${selectedCount}`;
            if (qtyInput && selectedItem && selectedItem.is_serialized) qtyInput.value = selectedCount || 0;
            if (serialChips) {
                serialChips.innerHTML = selectedCount ? Array.from(selectedSerials.entries()).map(([id, text]) => `<span class="warehouse-stockout-chip">${text}<button type="button" data-remove-serial="${id}">&times;</button></span>`).join('') : 'No serial selected yet.';
            }
        }

        function loadStockoutSerials() {
            serialRows = [];
            selectedSerials.clear();
            renderSerials();
            if (!selectedItem || !selectedItem.is_serialized) return;
            const params = new URLSearchParams({
                item_id: selectedItem.item_id || '',
                inventory_id: selectedItem.inventory_id || '',
                location_id: selectedItem.location_id || (locationSelect ? locationSelect.value : ''),
                branch_id: selectedItem.branch_id || (branchSelect ? branchSelect.value : ''),
                limit: 1000
            });
            fetch(`/warehouse/item-picker/serials?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(payload => {
                    serialRows = (payload.data || []).map(row => ({ id: row.id, text: row.serial_no || row.serial_number || row.text || row.id }));
                    renderSerials();
                })
                .catch(() => {
                    serialList.innerHTML = '<div class="text-danger small p-3">Unable to load serials.</div>';
                });
        }

        syncPickerSource();
        if (branchSelect) branchSelect.addEventListener('change', function () { syncPickerSource(); resetSelectedItem(); });
        if (locationSelect) locationSelect.addEventListener('change', function () { syncPickerSource(); resetSelectedItem(); });
        if (serialSearch) serialSearch.addEventListener('input', renderSerials);

        document.addEventListener('wmc:item-picker:selected', function (event) {
            const detail = event.detail || {};
            if (!pickerRow || detail.targetRow !== pickerRow) return;
            selectedItem = detail.item || null;
            if (!selectedItem) return;
            if (itemIdInput) itemIdInput.value = selectedItem.item_id || '';
            if (selectedItem.branch_id !== undefined && branchSelect && String(branchSelect.value || '') !== String(selectedItem.branch_id || '')) branchSelect.value = selectedItem.branch_id || '';
            if (selectedItem.location_id !== undefined && locationSelect && String(locationSelect.value || '') !== String(selectedItem.location_id || '')) locationSelect.value = selectedItem.location_id || '';
            syncPickerSource();
            if (qtyInput) {
                qtyInput.max = selectedItem.available || '';
                qtyInput.readOnly = !!selectedItem.is_serialized;
                qtyInput.value = selectedItem.is_serialized ? 0 : (detail.quantity || 1);
            }
            if (selectedItem.is_serialized) {
                if (serialSection) serialSection.classList.remove('d-none');
                if (qtyHelp) qtyHelp.classList.remove('d-none');
                loadStockoutSerials();
            } else {
                if (serialSection) serialSection.classList.add('d-none');
                if (qtyHelp) qtyHelp.classList.add('d-none');
            }
        });

        document.addEventListener('change', function (event) {
            if (!event.target.classList.contains('warehouse-stockout-serial-check')) return;
            const id = String(event.target.value);
            const row = serialRows.find(item => String(item.id) === id);
            if (event.target.checked) selectedSerials.set(id, row ? row.text : id); else selectedSerials.delete(id);
            renderSerials();
        });

        document.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('[data-remove-serial]');
            if (removeBtn) { selectedSerials.delete(String(removeBtn.dataset.removeSerial)); renderSerials(); return; }
            if (event.target.classList.contains('warehouse-stockout-select-visible')) {
                const q = (serialSearch ? serialSearch.value : '').toLowerCase().trim();
                serialRows.filter(row => !q || String(row.text || '').toLowerCase().includes(q)).forEach(row => selectedSerials.set(String(row.id), row.text));
                renderSerials();
            }
            if (event.target.classList.contains('warehouse-stockout-clear')) { selectedSerials.clear(); renderSerials(); }
        });
    });
</script>
