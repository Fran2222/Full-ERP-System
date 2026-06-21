<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')
        <x-warehouse.item-picker-modal />
        <script>window.WMC_ITEM_PICKER_BASE = "{{ url('') }}";</script>

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('warehouse.adjustment.create'), 403);
        @endphp

        <div class="card rounded-4 border-0 shadow-sm warehouse-adjustment-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Stock Adjustment</h4>
                        <p class="text-secondary mb-0">
                            Adjust warehouse stock by adding quantity or deducting selected serials/items.
                        </p>
                    </div>

                    @can('warehouse.inventory.view')
                        <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary warehouse-soft-btn">
                            Back To Inventory
                        </a>
                    @endcan
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

                <form method="POST" action="{{ route('warehouse.adjustment.store') }}" id="warehouseAdjustmentForm">
                    @csrf

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="warehouse-section-card h-100">
                                <h5 class="fw-bold mb-1">Adjustment Details</h5>
                                <p class="text-secondary mb-4">
                                    Select branch/location first, then search and select an item.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label">Branch <span class="text-secondary small">(optional)</span></label>
                                    <select name="branch_id" class="form-select warehouse-input warehouse-adjustment-branch">
                                        <option value="">Central / Unassigned Warehouse</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <select name="location_id" class="form-select warehouse-input warehouse-adjustment-location" required>
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" data-branch-id="{{ optional($location->branch)->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->location_name ?? $location->name }}
                                                {{ optional($location->branch)->name ? ' - ' . optional($location->branch)->name : ' - Central / Unassigned' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 warehouse-adjustment-picker-row" data-item-picker-context="stock-out">
                                    <label class="form-label">Item <span class="text-danger">*</span></label>
                                    <input type="hidden" name="item_id" class="warehouse-adjustment-item-id" value="{{ old('item_id') }}" required>

                                    <div class="wmc-selected-material-summary warehouse-adjustment-summary">
                                        <div class="text-muted">No item selected.</div>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm wmc-open-item-picker open-adjustment-picker mt-2 w-100">
                                        Search / Select Item
                                    </button>
                                    <small class="text-secondary d-block mt-1">Search item, view details, then select serials below if serialized deduction.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="warehouse-section-card h-100">
                                <h5 class="fw-bold mb-1">Quantity Adjustment</h5>
                                <p class="text-secondary mb-4">
                                    Add stock or deduct stock from the selected warehouse location.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                                    <select name="adjustment_type" class="form-select warehouse-input warehouse-adjustment-type" required>
                                        <option value="add" {{ old('adjustment_type') === 'add' ? 'selected' : '' }}>Add Stock</option>
                                        <option value="deduct" {{ old('adjustment_type') === 'deduct' ? 'selected' : '' }}>Deduct Stock</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="quantity"
                                           value="{{ old('quantity') }}"
                                           min="0.01"
                                           step="0.01"
                                           class="form-control warehouse-input warehouse-adjustment-quantity"
                                           placeholder="0.00"
                                           required>
                                    <small class="text-secondary warehouse-adjustment-qty-help d-none">For serialized items, quantity follows the entered/selected serial count.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Reference No.</label>
                                    <input type="text"
                                           name="reference_no"
                                           value="{{ old('reference_no') }}"
                                           class="form-control warehouse-input"
                                           placeholder="Optional reference no.">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks"
                                              rows="3"
                                              class="form-control warehouse-input"
                                              placeholder="Optional adjustment remarks">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 warehouse-adjustment-add-serial-section d-none">
                            <div class="warehouse-section-card">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">Serial Numbers to Add</h5>
                                        <p class="text-secondary mb-0">Enter one serial number per line. Quantity will auto-count.</p>
                                    </div>
                                    <span class="badge bg-primary warehouse-adjustment-add-count">Serials: 0</span>
                                </div>
                                <textarea name="serial_numbers" class="form-control warehouse-input warehouse-adjustment-serial-textarea" rows="5" placeholder="Enter one serial number per line">{{ old('serial_numbers') }}</textarea>
                            </div>
                        </div>

                        <div class="col-12 warehouse-adjustment-deduct-serial-section d-none">
                            <div class="warehouse-section-card warehouse-adjustment-serial-box">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">Serial Numbers to Deduct</h5>
                                        <p class="text-secondary mb-0">No CTRL needed. Tick serials from the selected location only.</p>
                                    </div>
                                    <span class="badge bg-primary warehouse-adjustment-selected-count">Selected: 0</span>
                                </div>
                                <input type="text" class="form-control warehouse-input warehouse-adjustment-serial-search mb-2" placeholder="Search serial no...">
                                <div class="d-flex justify-content-end gap-2 mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary warehouse-adjustment-select-visible">Select All Visible</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary warehouse-adjustment-clear">Clear</button>
                                </div>
                                <div class="warehouse-adjustment-serial-list"></div>
                                <div class="warehouse-adjustment-hidden-serials"></div>
                                <div class="warehouse-adjustment-selected-chips mt-2 text-secondary small">No serial selected yet.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        @can('warehouse.inventory.view')
                            <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary px-4 warehouse-soft-btn">
                                Cancel
                            </a>
                        @endcan

                        @can('warehouse.adjustment.create')
                            <button type="submit" class="btn btn-primary px-4 warehouse-soft-btn">
                                Save Adjustment
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .warehouse-adjustment-card,
        .warehouse-section-card {
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045) !important;
        }
        .warehouse-section-card { padding: 22px; background: #ffffff; }
        .warehouse-soft-btn { border-radius: 10px; padding: 10px 18px; font-weight: 700; }
        .warehouse-input { min-height: 44px; border-radius: 12px; border-color: #d9dee8; }
        .warehouse-input:focus { border-color: #3f5cff; box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12); }
        .warehouse-adjustment-summary { border: 1px solid rgba(63,92,255,.24); border-radius: 14px; padding: 10px 12px; background: #fff; min-height: 58px; }
        .warehouse-adjustment-serial-list { max-height: 240px; overflow-y: auto; border: 1px solid #e8edf7; border-radius: 12px; background: #fff; }
        .warehouse-adjustment-serial-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 10px; border-bottom:1px solid #eef2f7; }
        .warehouse-adjustment-serial-row:last-child { border-bottom:0; }
        .warehouse-adjustment-serial-row label { margin:0; cursor:pointer; display:flex; align-items:center; gap:9px; }
        .warehouse-adjustment-chip { display:inline-flex; align-items:center; gap:6px; border:1px solid rgba(63,92,255,.22); background:rgba(63,92,255,.06); color:#315cf6; border-radius:999px; padding:3px 9px; font-size:12px; margin:3px 4px 0 0; }
        .warehouse-adjustment-chip button { border:0; background:transparent; color:#315cf6; padding:0; line-height:1; }

        #wmcItemPickerModal .modal-content {
            border: 1px solid rgba(58, 87, 232, 0.24) !important;
            border-top: 5px solid #3a57e8 !important;
            border-radius: 18px !important;
            box-shadow: 0 25px 70px rgba(35, 45, 66, 0.28) !important;
            overflow: hidden !important;
        }
        #wmcItemPickerModal .modal-header {
            background: linear-gradient(90deg, rgba(58, 87, 232, 0.10), rgba(255, 255, 255, 1)) !important;
            border-bottom: 1px solid rgba(58, 87, 232, 0.14) !important;
        }
        #wmcItemPickerModal .wmc-item-picker-photo {
            border: 2px solid rgba(58, 87, 232, 0.35) !important;
            background: linear-gradient(145deg, #f7f9ff, #ffffff) !important;
            box-shadow: 0 10px 22px rgba(58, 87, 232, 0.16) !important;
            cursor: pointer !important;
            position: relative !important;
            border-radius: 14px !important;
        }
        #wmcItemPickerModal .wmc-item-picker-photo::after {
            content: "Zoom";
            position: absolute;
            right: 6px;
            bottom: 6px;
            background: #3a57e8;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 7px;
            border-radius: 999px;
            z-index: 2;
            pointer-events: none;
        }
        .wmc-adjustment-photo-zoom-overlay { position: fixed; inset: 0; background: rgba(20, 24, 38, 0.76); backdrop-filter: blur(3px); z-index: 99999; display: none; align-items: center; justify-content: center; padding: 24px; }
        .wmc-adjustment-photo-zoom-overlay.show { display: flex; }
        .wmc-adjustment-photo-zoom-panel { width: min(900px, 96vw); max-height: 90vh; background: #fff; border-radius: 18px; border: 1px solid rgba(58, 87, 232, 0.25); border-top: 5px solid #3a57e8; box-shadow: 0 30px 90px rgba(0, 0, 0, 0.38); overflow: hidden; position: relative; }
        .wmc-adjustment-photo-zoom-close { position: absolute; top: 13px; right: 16px; width: 36px; height: 36px; border: 0; background: rgba(58, 87, 232, 0.10); color: #232d42; border-radius: 999px; font-size: 26px; line-height: 30px; cursor: pointer; z-index: 2; }
        .wmc-adjustment-photo-zoom-header { padding: 18px 62px 14px 22px; background: linear-gradient(90deg, rgba(58, 87, 232, 0.10), rgba(255, 255, 255, 1)); border-bottom: 1px solid rgba(58, 87, 232, 0.14); }
        .wmc-adjustment-photo-zoom-title { font-size: 18px; font-weight: 700; color: #232d42; }
        .wmc-adjustment-photo-zoom-subtitle { font-size: 13px; color: #6c757d; margin-top: 2px; }
        .wmc-adjustment-photo-zoom-body { min-height: 420px; max-height: calc(90vh - 90px); overflow: auto; padding: 20px; display: flex; align-items: center; justify-content: center; background: linear-gradient(45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%), linear-gradient(-45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%), linear-gradient(45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%), linear-gradient(-45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%); background-size: 24px 24px; background-position: 0 0, 0 12px, 12px -12px, -12px 0px; }
        .wmc-adjustment-photo-zoom-body img { max-width: 100%; max-height: calc(90vh - 150px); object-fit: contain; border-radius: 12px; background: #fff; }
    </style>

    <div id="wmcAdjustmentPhotoZoomOverlay" class="wmc-adjustment-photo-zoom-overlay" aria-hidden="true">
        <div class="wmc-adjustment-photo-zoom-panel">
            <button type="button" class="wmc-adjustment-photo-zoom-close" aria-label="Close preview">&times;</button>
            <div class="wmc-adjustment-photo-zoom-header">
                <div class="wmc-adjustment-photo-zoom-title">Item Photo Preview</div>
                <div class="wmc-adjustment-photo-zoom-subtitle" id="wmcAdjustmentPhotoZoomSubtitle">Warehouse item image</div>
            </div>
            <div class="wmc-adjustment-photo-zoom-body">
                <img id="wmcAdjustmentPhotoZoomImg" src="" alt="Item Photo Preview">
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/wmc-item-picker.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('warehouseAdjustmentForm');
            const pickerRow = document.querySelector('.warehouse-adjustment-picker-row');
            const itemIdInput = document.querySelector('.warehouse-adjustment-item-id');
            const qtyInput = document.querySelector('.warehouse-adjustment-quantity');
            const qtyHelp = document.querySelector('.warehouse-adjustment-qty-help');
            const branchSelect = document.querySelector('.warehouse-adjustment-branch');
            const locationSelect = document.querySelector('.warehouse-adjustment-location');
            const adjustmentType = document.querySelector('.warehouse-adjustment-type');
            const addSerialSection = document.querySelector('.warehouse-adjustment-add-serial-section');
            const deductSerialSection = document.querySelector('.warehouse-adjustment-deduct-serial-section');
            const serialTextarea = document.querySelector('.warehouse-adjustment-serial-textarea');
            const addCount = document.querySelector('.warehouse-adjustment-add-count');
            const serialList = document.querySelector('.warehouse-adjustment-serial-list');
            const serialSearch = document.querySelector('.warehouse-adjustment-serial-search');
            const serialHidden = document.querySelector('.warehouse-adjustment-hidden-serials');
            const serialChips = document.querySelector('.warehouse-adjustment-selected-chips');
            const serialBadge = document.querySelector('.warehouse-adjustment-selected-count');
            const summary = document.querySelector('.warehouse-adjustment-summary');
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

            function serialLines() {
                const text = serialTextarea ? serialTextarea.value : '';
                return text.split(/\r?\n|,|;/).map(v => v.trim()).filter(Boolean);
            }

            function resetSelectedItem() {
                selectedItem = null;
                serialRows = [];
                selectedSerials.clear();
                if (itemIdInput) itemIdInput.value = '';
                if (summary) summary.innerHTML = '<div class="text-muted">No item selected.</div>';
                if (qtyInput) { qtyInput.value = ''; qtyInput.readOnly = false; qtyInput.max = ''; }
                updateModeSections();
                renderSerials();
            }

            function updateSummary() {
                if (!summary || !selectedItem) return;
                const img = selectedItem.image_url ? '<img src="' + selectedItem.image_url + '" alt="" style="width:42px;height:42px;object-fit:contain;border:1px solid #dbe3ff;border-radius:8px;background:#fff;margin-right:10px;">' : '';
                summary.innerHTML = '<div class="d-flex align-items-center">' + img + '<div><div class="fw-semibold">' + (selectedItem.item_code || '-') + ' - ' + (selectedItem.item_name || '-') + '</div><div class="small text-muted">' + (selectedItem.category_name || '-') + ' | ' + (selectedItem.location_name || '-') + ' | Available: ' + (selectedItem.available || 0) + (selectedItem.is_serialized ? ' | Serialized' : '') + '</div></div></div>';
            }

            function updateModeSections() {
                const type = adjustmentType ? adjustmentType.value : 'add';
                const isSerialized = !!(selectedItem && selectedItem.is_serialized);
                if (qtyHelp) qtyHelp.classList.toggle('d-none', !isSerialized);
                if (addSerialSection) addSerialSection.classList.toggle('d-none', !(isSerialized && type === 'add'));
                if (deductSerialSection) deductSerialSection.classList.toggle('d-none', !(isSerialized && type === 'deduct'));
                if (!isSerialized && qtyInput) qtyInput.readOnly = false;
                if (isSerialized && qtyInput) qtyInput.readOnly = true;
                if (isSerialized && type === 'add') syncAddSerialQty();
                if (isSerialized && type === 'deduct') {
                    if (selectedItem) loadDeductSerials();
                }
            }

            function syncAddSerialQty() {
                const count = serialLines().length;
                if (addCount) addCount.textContent = 'Serials: ' + count;
                if (qtyInput && selectedItem && selectedItem.is_serialized && adjustmentType && adjustmentType.value === 'add') qtyInput.value = count || 0;
            }

            function renderSerials() {
                if (!serialList) return;
                const q = (serialSearch ? serialSearch.value : '').toLowerCase().trim();
                const visible = serialRows.filter(row => !q || String(row.text || '').toLowerCase().includes(q));
                serialList.innerHTML = visible.length ? visible.map(row => {
                    const checked = selectedSerials.has(String(row.id)) ? 'checked' : '';
                    return `<div class="warehouse-adjustment-serial-row" data-serial-id="${row.id}"><label><input type="checkbox" class="warehouse-adjustment-serial-check" value="${row.id}" ${checked}> <span>${row.text}</span></label><span class="badge bg-success">Available</span></div>`;
                }).join('') : '<div class="text-muted small p-3">No available serials found for this selected item/location.</div>';
                if (serialHidden) serialHidden.innerHTML = Array.from(selectedSerials.keys()).map(id => `<input type="hidden" name="serial_ids[]" value="${id}">`).join('');
                const selectedCount = selectedSerials.size;
                if (serialBadge) serialBadge.textContent = `Selected: ${selectedCount}`;
                if (qtyInput && selectedItem && selectedItem.is_serialized && adjustmentType && adjustmentType.value === 'deduct') qtyInput.value = selectedCount || 0;
                if (serialChips) serialChips.innerHTML = selectedCount ? Array.from(selectedSerials.entries()).map(([id, text]) => `<span class="warehouse-adjustment-chip">${text}<button type="button" data-remove-adjustment-serial="${id}">&times;</button></span>`).join('') : 'No serial selected yet.';
            }

            function loadDeductSerials() {
                serialRows = [];
                selectedSerials.clear();
                renderSerials();
                if (!selectedItem || !selectedItem.is_serialized || !adjustmentType || adjustmentType.value !== 'deduct') return;
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
                    .catch(() => { serialList.innerHTML = '<div class="text-danger small p-3">Unable to load serials.</div>'; });
            }

            document.querySelectorAll('.open-adjustment-picker').forEach(function (button) {
                if (button.dataset.boundAdjustmentPicker === '1') return;
                button.dataset.boundAdjustmentPicker = '1';
                button.addEventListener('click', function () {
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
                });
            });

            syncPickerSource();
            if (branchSelect) branchSelect.addEventListener('change', function () { syncPickerSource(); resetSelectedItem(); });
            if (locationSelect) locationSelect.addEventListener('change', function () { syncPickerSource(); resetSelectedItem(); });
            if (adjustmentType) adjustmentType.addEventListener('change', updateModeSections);
            if (serialTextarea) serialTextarea.addEventListener('input', syncAddSerialQty);
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
                updateSummary();
                if (qtyInput) {
                    qtyInput.max = selectedItem.available || '';
                    qtyInput.readOnly = !!selectedItem.is_serialized;
                    qtyInput.value = selectedItem.is_serialized ? 0 : (detail.quantity || 1);
                }
                updateModeSections();
            });

            document.addEventListener('change', function (event) {
                if (!event.target.classList.contains('warehouse-adjustment-serial-check')) return;
                const id = String(event.target.value);
                const row = serialRows.find(item => String(item.id) === id);
                if (event.target.checked) selectedSerials.set(id, row ? row.text : id); else selectedSerials.delete(id);
                renderSerials();
            });

            document.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('[data-remove-adjustment-serial]');
                if (removeBtn) { selectedSerials.delete(String(removeBtn.dataset.removeAdjustmentSerial)); renderSerials(); return; }
                if (event.target.classList.contains('warehouse-adjustment-select-visible')) {
                    const q = (serialSearch ? serialSearch.value : '').toLowerCase().trim();
                    serialRows.filter(row => !q || String(row.text || '').toLowerCase().includes(q)).forEach(row => selectedSerials.set(String(row.id), row.text));
                    renderSerials();
                }
                if (event.target.classList.contains('warehouse-adjustment-clear')) { selectedSerials.clear(); renderSerials(); }
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    const isSerialized = !!(selectedItem && selectedItem.is_serialized);
                    const type = adjustmentType ? adjustmentType.value : 'add';
                    if (isSerialized && type === 'add' && serialLines().length < 1) {
                        event.preventDefault();
                        alert('Please enter at least one serial number for serialized Add Stock adjustment.');
                        return;
                    }
                    if (isSerialized && type === 'deduct' && selectedSerials.size < 1) {
                        event.preventDefault();
                        alert('Please select at least one serial number for serialized Deduct Stock adjustment.');
                        return;
                    }
                    if (window.Swal) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'question',
                            title: 'Post stock adjustment?',
                            text: 'This will update warehouse inventory, serial records, and stock movement ledger.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, save adjustment',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#315cf6',
                            reverseButtons: true
                        }).then(result => { if (result.isConfirmed) form.submit(); });
                    }
                });
            }

            const overlay = document.getElementById('wmcAdjustmentPhotoZoomOverlay');
            const closeBtn = overlay ? overlay.querySelector('.wmc-adjustment-photo-zoom-close') : null;
            const zoomImg = document.getElementById('wmcAdjustmentPhotoZoomImg');
            const subtitle = document.getElementById('wmcAdjustmentPhotoZoomSubtitle');
            function visiblePhoto() {
                const img = document.getElementById('wmcItemPickerPhoto');
                if (!img || img.classList.contains('d-none') || !img.getAttribute('src')) return null;
                return img;
            }
            function openZoom() {
                const img = visiblePhoto();
                if (!img || !overlay || !zoomImg) return;
                zoomImg.src = img.src;
                if (subtitle) {
                    const code = document.getElementById('wmcItemPickerCode')?.textContent || '';
                    const name = document.getElementById('wmcItemPickerName')?.textContent || '';
                    subtitle.textContent = [code, name].filter(Boolean).join(' - ') || 'Warehouse item image';
                }
                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
            }
            function closeZoom() {
                if (!overlay) return;
                overlay.classList.remove('show');
                overlay.setAttribute('aria-hidden', 'true');
                if (zoomImg) zoomImg.src = '';
            }
            document.addEventListener('click', function (event) {
                if (event.target.closest('#wmcItemPickerModal .wmc-item-picker-photo')) openZoom();
                if (event.target === overlay || event.target.closest('.wmc-adjustment-photo-zoom-close')) closeZoom();
            });
            if (closeBtn) closeBtn.addEventListener('click', closeZoom);
            document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeZoom(); });
        });
    </script>
</x-app-layout>
