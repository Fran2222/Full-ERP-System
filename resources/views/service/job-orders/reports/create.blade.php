<x-app-layout :assets="$assets ?? []">
<div class="container-fluid py-4">
    @include('service.partials.nav', ['active' => 'job-orders'])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-1">Add Technician Report</h4>
            <p class="text-muted mb-0">
                {{ $jobOrder->job_order_no ?? ('JO-' . str_pad($jobOrder->id, 5, '0', STR_PAD_LEFT)) }}
                @if(!empty($jobOrder->subject))
                    - {{ $jobOrder->subject }}
                @endif
            </p>
        </div>
        <a href="{{ route('service.job-orders.show', $jobOrder->id) }}" class="btn btn-light">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the report form.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('service.job-orders.reports.store', $jobOrder->id) }}"
          enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body">
                        <h5 class="mb-4">Report Details</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="datetime-local" name="started_at" class="form-control" value="{{ old('started_at') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Completion Time</label>
                                <input type="datetime-local" name="completed_at" class="form-control" value="{{ old('completed_at') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Findings</label>
                                <textarea name="findings" class="form-control" rows="4" placeholder="What did the technician find?">{{ old('findings') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Work Done</label>
                                <textarea name="work_done" class="form-control" rows="4" placeholder="Describe the work performed.">{{ old('work_done') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Recommendations</label>
                                <textarea name="recommendations" class="form-control" rows="3" placeholder="Recommended next steps, replacement, monitoring, etc.">{{ old('recommendations') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Additional notes.">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Materials / Parts Used</h5>
                                <p class="text-muted mb-0">Select warehouse inventory used for this service. Serialized items should use quantity 1.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addMaterialBtn">Add Material</button>
                        </div>

                        <div id="materialsWrap" class="d-flex flex-column gap-3"></div>

                        <template id="materialRowTemplate">
                            <div class="border rounded p-3 material-row">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Warehouse Item</label>
                                        <select class="form-select material-inventory" name="materials[__INDEX__][warehouse_inventory_id]">
                                            <option value="">No material</option>
                                            @foreach($inventoryOptions ?? [] as $inv)
                                                <option value="{{ $inv->inventory_id }}"
                                                        data-item-id="{{ $inv->warehouse_item_id }}"
                                                        data-unit="{{ $inv->unit_name }}"
                                                        data-serialized="{{ $inv->is_serialized ? 1 : 0 }}"
                                                        data-available="{{ $inv->available ?? $inv->on_hand }}">
                                                    {{ $inv->item_code }} - {{ $inv->item_name }}
                                                    | {{ $inv->branch_name }} {{ $inv->location_name ? ' / ' . $inv->location_name : '' }}
                                                    | Available: {{ $inv->available ?? $inv->on_hand }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Serial No. if serialized</label>
                                        <select class="form-select material-serial" name="materials[__INDEX__][warehouse_item_serial_id]">
                                            <option value="">No serial</option>
                                            @foreach($serialOptions ?? [] as $serial)
                                                <option value="{{ $serial->id }}" data-item-id="{{ $serial->warehouse_item_id }}">
                                                    {{ $serial->serial_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Qty</label>
                                        <input type="number" min="0" step="0.01" class="form-control material-qty" name="materials[__INDEX__][quantity]" value="1">
                                        <input type="hidden" class="material-unit" name="materials[__INDEX__][unit]">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-material">Remove</button>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Material Remarks</label>
                                        <input type="text" class="form-control" name="materials[__INDEX__][remarks]" placeholder="Optional material remarks">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body">
                        <h5 class="mb-4">Status Update</h5>

                        <label class="form-label">Update Job Order Status</label>
                        <select name="status_update_id" class="form-select">
                            <option value="">Keep current status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ (string) old('status_update_id') === (string) $status->id ? 'selected' : '' }}>
                                    {{ $status->name ?? ('Status #' . $status->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body">
                        <h5 class="mb-3">Photo Documentation</h5>

                        <input type="file"
                               name="photos[]"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               multiple>

                        <small class="text-muted d-block mt-2">
                            Upload up to 5 photos. Accepted: JPG, PNG, WEBP. Max 4MB each.
                        </small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('service.job-orders.show', $jobOrder->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Report</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const wrap = document.getElementById('materialsWrap');
    const template = document.getElementById('materialRowTemplate');
    const addBtn = document.getElementById('addMaterialBtn');
    let index = 0;

    function refreshSerials(row) {
        const inv = row.querySelector('.material-inventory');
        const serial = row.querySelector('.material-serial');
        const qty = row.querySelector('.material-qty');
        const unit = row.querySelector('.material-unit');

        const selected = inv.options[inv.selectedIndex];
        const itemId = selected ? selected.getAttribute('data-item-id') : '';
        const isSerialized = selected ? selected.getAttribute('data-serialized') === '1' : false;
        const unitName = selected ? (selected.getAttribute('data-unit') || '') : '';

        unit.value = unitName;

        Array.from(serial.options).forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            opt.hidden = opt.getAttribute('data-item-id') !== itemId;
        });

        serial.value = '';

        if (isSerialized) {
            qty.value = 1;
            qty.readOnly = true;
        } else {
            qty.readOnly = false;
        }
    }

    function addRow() {
        const html = template.innerHTML.replaceAll('__INDEX__', index);
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();
        const row = holder.firstElementChild;
        wrap.appendChild(row);

        row.querySelector('.material-inventory').addEventListener('change', function () {
            refreshSerials(row);
        });

        row.querySelector('.remove-material').addEventListener('click', function () {
            row.remove();
        });

        refreshSerials(row);
        index++;
    }

    addBtn.addEventListener('click', addRow);
})();
</script>
</x-app-layout>


{{-- service-material-searchable-dropdowns-patch-v1 --}}
<style>
    .service-materials-card .select2-container {
        width: 100% !important;
        max-width: 100% !important;
    }

    .service-materials-card .select2-container--default .select2-selection--single {
        min-height: 42px;
        height: 42px;
        border: 1px solid #e6e9f4;
        border-radius: 8px;
        display: flex;
        align-items: center;
        background-color: #fff;
    }

    .service-materials-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #5f6b7a;
        line-height: 40px;
        padding-left: 14px;
        padding-right: 32px;
        font-size: 14px;
    }

    .service-materials-card .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #8a92a6;
    }

    .service-materials-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }

    .service-materials-card .select2-container--default.select2-container--focus .select2-selection--single,
    .service-materials-card .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3a57e8;
        box-shadow: 0 0 0 0.15rem rgba(58, 87, 232, 0.10);
    }

    .select2-dropdown.service-material-select2-dropdown {
        border: 1px solid #e6e9f4;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(20, 20, 43, 0.10);
    }

    .select2-dropdown.service-material-select2-dropdown .select2-search--dropdown {
        padding: 10px;
    }

    .select2-dropdown.service-material-select2-dropdown .select2-search__field {
        height: 38px;
        border: 1px solid #e6e9f4 !important;
        border-radius: 8px;
        outline: none;
        padding: 8px 10px;
        font-size: 14px;
    }

    .select2-dropdown.service-material-select2-dropdown .select2-results__options {
        max-height: 220px;
        overflow-y: auto;
    }

    .select2-dropdown.service-material-select2-dropdown .select2-results__option {
        padding: 9px 12px;
        font-size: 14px;
    }

    .service-material-native-search-note {
        display: none;
        margin-top: 4px;
        font-size: 11px;
        color: #8a92a6;
    }

    .service-materials-card.service-material-select2-unavailable .service-material-native-search-note {
        display: block;
    }
</style>

<script>
(function () {
    function materialSelects(context) {
        context = context || document;
        return context.querySelectorAll(
            'select[name*="[warehouse_inventory_id]"], ' +
            'select[name*="[warehouse_item_serial_id]"], ' +
            'select[name*="warehouse_inventory_id"], ' +
            'select[name*="warehouse_item_serial_id"]'
        );
    }

    function decorateNativeFallback(select) {
        if (!select || select.dataset.serviceMaterialNativeDecorated === '1') {
            return;
        }

        select.dataset.serviceMaterialNativeDecorated = '1';

        var label = select.closest('.form-group, .mb-3, .col-md-4, .col-md-6, .col-lg-4, .col-lg-6, td, div');
        if (!label) {
            return;
        }

        var note = document.createElement('div');
        note.className = 'service-material-native-search-note';
        note.textContent = 'Tip: type while dropdown is focused to jump/search options.';
        label.appendChild(note);
    }

    function initOneSelect(select) {
        if (!select || select.dataset.serviceMaterialSearchReady === '1') {
            return;
        }

        select.dataset.serviceMaterialSearchReady = '1';

        var isSerial = select.name && select.name.indexOf('warehouse_item_serial_id') !== -1;
        var placeholder = isSerial ? 'Search / select serial no...' : 'Search / select warehouse item...';

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            var $select = window.jQuery(select);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: placeholder,
                allowClear: true,
                dropdownAutoWidth: false,
                dropdownCssClass: 'service-material-select2-dropdown',
                minimumResultsForSearch: 0
            });

            return;
        }

        var card = select.closest('.service-materials-card') || document.querySelector('.service-materials-card');
        if (card) {
            card.classList.add('service-material-select2-unavailable');
        }

        decorateNativeFallback(select);
    }

    function initMaterialDropdowns(context) {
        materialSelects(context).forEach(initOneSelect);
    }

    function watchMaterialRows() {
        var target =
            document.querySelector('.service-materials-card') ||
            document.querySelector('[id*="material"]') ||
            document.body;

        if (!target || !window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!node || node.nodeType !== 1) {
                        return;
                    }

                    if (node.matches && node.matches('select')) {
                        initOneSelect(node);
                    }

                    initMaterialDropdowns(node);
                });
            });
        });

        observer.observe(target, {
            childList: true,
            subtree: true
        });
    }

    function markMaterialsCard() {
        var headings = Array.from(document.querySelectorAll('h1,h2,h3,h4,h5,h6,label,div,strong'));
        headings.forEach(function (el) {
            if ((el.textContent || '').toLowerCase().indexOf('materials / parts used') !== -1) {
                var card = el.closest('.card, .iq-card, .shadow-sm, .bg-white, .rounded, div');
                if (card) {
                    card.classList.add('service-materials-card');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        markMaterialsCard();
        initMaterialDropdowns(document);
        watchMaterialRows();

        document.addEventListener('click', function (event) {
            var text = (event.target && event.target.textContent ? event.target.textContent : '').trim().toLowerCase();
            if (text.indexOf('add material') !== -1) {
                setTimeout(function () {
                    markMaterialsCard();
                    initMaterialDropdowns(document);
                }, 120);
            }
        });
    });

    window.serviceInitMaterialDropdowns = initMaterialDropdowns;
})();
</script>



{{-- global-warehouse-item-picker-phase4 --}}
<x-warehouse.item-picker-modal />

<script>
    window.WMC_ITEM_PICKER_BASE = "{{ url('') }}";
</script>
<script src="{{ asset('assets/js/wmc-item-picker.js') }}"></script>


{{-- force-item-picker-photo-zoom-clean-ui-v3-fixed-start --}}
<div id="wmcForcePhotoZoomOverlay" class="wmc-force-photo-zoom-overlay" aria-hidden="true">
    <div class="wmc-force-photo-zoom-panel">
        <button type="button" class="wmc-force-photo-zoom-close" aria-label="Close preview">&times;</button>
        <div class="wmc-force-photo-zoom-header">
            <div class="wmc-force-photo-zoom-title">Item Photo Preview</div>
            <div class="wmc-force-photo-zoom-subtitle" id="wmcForcePhotoZoomSubtitle">Warehouse item image</div>
        </div>
        <div class="wmc-force-photo-zoom-body">
            <img id="wmcForcePhotoZoomImg" src="" alt="Item Photo Preview">
        </div>
    </div>
</div>

<style>
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
        padding-bottom: 14px !important;
    }

    #wmcItemPickerModal .card,
    #wmcItemPickerModal .wmc-picker-info-box {
        border: 1px solid rgba(58, 87, 232, 0.13) !important;
        box-shadow: 0 10px 24px rgba(35, 45, 66, 0.06) !important;
    }

    #wmcItemPickerResults tr:hover {
        background: rgba(58, 87, 232, 0.06) !important;
    }

    #wmcItemPickerResults tr.wmc-picker-active,
    #wmcItemPickerResults tr.table-active,
    #wmcItemPickerResults tr.active {
        background: linear-gradient(90deg, rgba(58, 87, 232, 0.14), rgba(58, 87, 232, 0.04)) !important;
        box-shadow: inset 4px 0 0 #3a57e8 !important;
    }

    #wmcItemPickerModal .wmc-item-picker-photo {
        border: 2px solid rgba(58, 87, 232, 0.35) !important;
        background: linear-gradient(145deg, #f7f9ff, #ffffff) !important;
        box-shadow: 0 10px 22px rgba(58, 87, 232, 0.16) !important;
        cursor: pointer !important;
        position: relative !important;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease !important;
    }

    #wmcItemPickerModal .wmc-item-picker-photo:hover {
        transform: translateY(-1px) !important;
        border-color: rgba(58, 87, 232, 0.95) !important;
        box-shadow: 0 16px 32px rgba(58, 87, 232, 0.25) !important;
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
        line-height: 1;
        padding: 5px 7px;
        border-radius: 999px;
        opacity: 0;
        transform: translateY(3px);
        transition: opacity .16s ease, transform .16s ease;
        pointer-events: none;
        z-index: 2;
    }

    #wmcItemPickerModal .wmc-item-picker-photo.wmc-force-has-photo::after {
        opacity: 1;
        transform: translateY(0);
    }

    #wmcItemPickerUseBtn {
        border-radius: 10px !important;
        box-shadow: 0 10px 22px rgba(58, 87, 232, 0.26) !important;
    }

    .wmc-force-photo-zoom-overlay {
        position: fixed;
        inset: 0;
        background: rgba(20, 24, 38, 0.76);
        backdrop-filter: blur(3px);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .wmc-force-photo-zoom-overlay.show {
        display: flex;
    }

    .wmc-force-photo-zoom-panel {
        width: min(900px, 96vw);
        max-height: 90vh;
        background: #fff;
        border-radius: 18px;
        border: 1px solid rgba(58, 87, 232, 0.25);
        border-top: 5px solid #3a57e8;
        box-shadow: 0 30px 90px rgba(0, 0, 0, 0.38);
        overflow: hidden;
        position: relative;
    }

    .wmc-force-photo-zoom-close {
        position: absolute;
        top: 13px;
        right: 16px;
        width: 36px;
        height: 36px;
        border: 0;
        background: rgba(58, 87, 232, 0.10);
        color: #232d42;
        border-radius: 999px;
        font-size: 26px;
        line-height: 30px;
        cursor: pointer;
        z-index: 2;
    }

    .wmc-force-photo-zoom-close:hover {
        background: rgba(58, 87, 232, 0.18);
    }

    .wmc-force-photo-zoom-header {
        padding: 18px 62px 14px 22px;
        background: linear-gradient(90deg, rgba(58, 87, 232, 0.10), rgba(255, 255, 255, 1));
        border-bottom: 1px solid rgba(58, 87, 232, 0.14);
    }

    .wmc-force-photo-zoom-title {
        font-size: 18px;
        font-weight: 700;
        color: #232d42;
    }

    .wmc-force-photo-zoom-subtitle {
        font-size: 13px;
        color: #6c757d;
        margin-top: 2px;
    }

    .wmc-force-photo-zoom-body {
        min-height: 420px;
        max-height: calc(90vh - 90px);
        overflow: auto;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            linear-gradient(45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(58, 87, 232, 0.04) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(58, 87, 232, 0.04) 75%);
        background-size: 24px 24px;
        background-position: 0 0, 0 12px, 12px -12px, -12px 0px;
    }

    .wmc-force-photo-zoom-body img {
        max-width: 100%;
        max-height: calc(90vh - 150px);
        object-fit: contain;
        border-radius: 12px;
        background: #fff;
    }
</style>

<script>
(function () {
    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function getVisiblePhoto() {
        var img = qs('#wmcItemPickerPhoto');
        if (!img) return null;
        if (img.classList.contains('d-none')) return null;
        if (!img.getAttribute('src')) return null;
        return img;
    }

    function refreshPhotoBox() {
        var box = qs('#wmcItemPickerModal .wmc-item-picker-photo');
        var img = getVisiblePhoto();

        if (!box) return;

        if (img) {
            box.classList.add('wmc-force-has-photo');
            box.setAttribute('title', 'Click to zoom item photo');
            box.setAttribute('role', 'button');
            box.setAttribute('tabindex', '0');
        } else {
            box.classList.remove('wmc-force-has-photo');
            box.removeAttribute('title');
            box.removeAttribute('role');
            box.removeAttribute('tabindex');
        }
    }

    function openZoom() {
        var img = getVisiblePhoto();
        var overlay = qs('#wmcForcePhotoZoomOverlay');
        var zoomImg = qs('#wmcForcePhotoZoomImg');
        var subtitle = qs('#wmcForcePhotoZoomSubtitle');
        var code = qs('#wmcItemPickerCode');
        var name = qs('#wmcItemPickerName');

        if (!img || !overlay || !zoomImg) return;

        zoomImg.src = img.src;

        var label = '';
        if (code && code.textContent.trim()) label += code.textContent.trim();
        if (name && name.textContent.trim()) label += (label ? ' - ' : '') + name.textContent.trim();

        if (subtitle) subtitle.textContent = label || 'Warehouse item image';

        overlay.classList.add('show');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeZoom() {
        var overlay = qs('#wmcForcePhotoZoomOverlay');
        if (!overlay) return;

        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function bindEvents() {
        var box = qs('#wmcItemPickerModal .wmc-item-picker-photo');
        var overlay = qs('#wmcForcePhotoZoomOverlay');
        var closeBtn = qs('.wmc-force-photo-zoom-close');

        if (box && box.dataset.forceZoomBound !== '1') {
            box.dataset.forceZoomBound = '1';

            box.addEventListener('click', function () {
                openZoom();
            });

            box.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openZoom();
                }
            });
        }

        if (overlay && overlay.dataset.forceZoomBound !== '1') {
            overlay.dataset.forceZoomBound = '1';

            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    closeZoom();
                }
            });
        }

        if (closeBtn && closeBtn.dataset.forceZoomBound !== '1') {
            closeBtn.dataset.forceZoomBound = '1';
            closeBtn.addEventListener('click', closeZoom);
        }

        if (document.body.dataset.forceZoomEscBound !== '1') {
            document.body.dataset.forceZoomEscBound = '1';
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeZoom();
                }
            });
        }
    }

    function bootForceZoom() {
        bindEvents();
        refreshPhotoBox();

        setInterval(refreshPhotoBox, 600);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootForceZoom);
    } else {
        bootForceZoom();
    }

    document.addEventListener('click', function () {
        setTimeout(function () {
            bindEvents();
            refreshPhotoBox();
        }, 100);
    });
})();
</script>
{{-- force-item-picker-photo-zoom-clean-ui-v3-fixed-end --}}

