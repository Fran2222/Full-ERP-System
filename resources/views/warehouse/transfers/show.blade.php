<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        @php
            $statusClass = match($transfer->status) {
                'draft' => 'bg-primary-subtle text-primary',
                'in_transit' => 'bg-warning-subtle text-warning',
                'received' => 'bg-success-subtle text-success',
                'cancelled' => 'bg-danger-subtle text-danger',
                default => 'bg-secondary-subtle text-secondary',
            };
        @endphp

        <div class="card rounded-4 border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">
                            Transfer {{ $transfer->transfer_no }}
                            <span class="badge rounded-pill {{ $statusClass }} ms-2">{{ $transfer->status_label }}</span>
                        </h4>
                        <p class="text-secondary mb-0">Transfer order details, item list, serial numbers, and status timeline.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('warehouse.transfer') }}" class="btn btn-outline-secondary">Back</a>
                        @if($canCancel)
                            <form method="POST" action="{{ route('warehouse.transfer.cancel', $transfer->id) }}" onsubmit="return confirm('Cancel this transfer?');">@csrf @method('PATCH')<button class="btn btn-outline-danger" type="submit">Cancel</button></form>
                        @endif
                        @if($canDispatch)
                            <form method="POST" action="{{ route('warehouse.transfer.dispatch', $transfer->id) }}" onsubmit="return confirm('Dispatch this transfer? Source stock will be deducted.');">@csrf @method('PATCH')<button class="btn btn-warning" type="submit">Dispatch / Deliver</button></form>
                        @endif
                        @if($canReceive ?? false)
                            <form method="POST" action="{{ route('warehouse.transfer.receive', $transfer->id) }}" onsubmit="return confirm('Receive this transfer? Destination stock will be added.');">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Receive Transfer</button></form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-1">From</div>
                            <h5 class="fw-bold mb-1">{{ $transfer->fromLocation?->location_name ?? $transfer->fromLocation?->name ?? '-' }}</h5>
                            <div class="text-secondary">{{ $transfer->from_branch_name }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-secondary small mb-1">To</div>
                            <h5 class="fw-bold mb-1">{{ $transfer->toLocation?->location_name ?? $transfer->toLocation?->name ?? '-' }}</h5>
                            <div class="text-secondary">{{ $transfer->to_branch_name }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Transfer Date</div><div class="fw-semibold">{{ optional($transfer->transfer_date)->format('M d, Y') ?: '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Created By</div><div class="fw-semibold">{{ $transfer->creator?->name ?? '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Dispatched At</div><div class="fw-semibold">{{ optional($transfer->dispatched_at)->format('M d, Y h:i A') ?: '-' }}</div></div></div>
                    <div class="col-lg-3"><div class="border rounded-4 p-3"><div class="text-secondary small">Received At</div><div class="fw-semibold">{{ optional($transfer->received_at)->format('M d, Y h:i A') ?: '-' }}</div></div></div>
                </div>

                <h5 class="fw-bold mb-3">Transfer Items</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>#</th><th>Item</th><th class="text-end">Qty</th><th>Serial No(s).</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @foreach($transfer->items as $line)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $line->item?->display_name ?? '-' }}</div>
                                        <div class="small text-secondary">{{ $line->item?->display_code ?? '-' }}</div>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format((float) $line->quantity, 2) }}</td>
                                    <td>
                                        @forelse($line->serials as $serial)
                                            <span class="badge bg-light text-primary border me-1 mb-1">{{ $serial->serial_number }}</span>
                                        @empty
                                            <span class="text-secondary">-</span>
                                        @endforelse
                                    </td>
                                    <td class="text-secondary">{{ $line->remarks ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border rounded-4 p-3">
                    <h6 class="fw-bold mb-2">Remarks</h6>
                    <p class="mb-0 text-secondary">{{ $transfer->remarks ?: 'No remarks added.' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


{{-- force-transfer-global-item-picker-phase6-start --}}
<x-warehouse.item-picker-modal />

<script>
    window.WMC_ITEM_PICKER_BASE = "{{ url('') }}";
</script>
<script src="{{ asset('assets/js/wmc-item-picker.js') }}"></script>

<script>
(function () {
    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function onTransferPage() {
        return window.location.pathname.indexOf('/warehouse/transfer') !== -1;
    }

    function lowerText(el) {
        return el ? (el.textContent || '').trim().toLowerCase() : '';
    }

    function fieldWrap(el) {
        return el ? (el.closest('.form-group, .mb-3, .col-md-6, .col-md-4, .col, .row, div') || el.parentElement) : null;
    }

    function labelNear(el) {
        var wrap = fieldWrap(el);
        var label = wrap ? qs('label', wrap) : null;
        return lowerText(label);
    }

    function findItemLabel() {
        return qsa('label').find(function (label) {
            var t = lowerText(label);
            return t === 'item' || t.indexOf('item') !== -1 || t.indexOf('product') !== -1;
        });
    }

    function findItemSelect() {
        var label = findItemLabel();

        if (label) {
            var forId = label.getAttribute('for');
            if (forId) {
                var byFor = qs('#' + CSS.escape(forId));
                if (byFor && byFor.tagName && byFor.tagName.toLowerCase() === 'select') {
                    return byFor;
                }
            }

            var wrap = fieldWrap(label);
            if (wrap) {
                var selectInWrap = qs('select', wrap);
                if (selectInWrap) {
                    return selectInWrap;
                }
            }
        }

        return qsa('select').find(function (select) {
            var name = (select.getAttribute('name') || '').toLowerCase();
            var id = (select.getAttribute('id') || '').toLowerCase();
            var label = labelNear(select);
            var txt = lowerText(select);

            if (name.indexOf('location') !== -1 || id.indexOf('location') !== -1 || label.indexOf('location') !== -1) return false;
            if (name.indexOf('branch') !== -1 || id.indexOf('branch') !== -1 || label.indexOf('branch') !== -1) return false;
            if (name.indexOf('status') !== -1 || id.indexOf('status') !== -1) return false;

            return (
                name.indexOf('item') !== -1 ||
                id.indexOf('item') !== -1 ||
                label.indexOf('item') !== -1 ||
                txt.indexOf('item-') !== -1 ||
                txt.indexOf('serialized') !== -1 ||
                txt.indexOf('pcs') !== -1
            );
        });
    }

    function findSourceLocationSelect() {
        var candidates = qsa('select').filter(function (select) {
            var name = (select.getAttribute('name') || '').toLowerCase();
            var id = (select.getAttribute('id') || '').toLowerCase();
            var label = labelNear(select);

            return (
                name.indexOf('from') !== -1 ||
                name.indexOf('source') !== -1 ||
                name.indexOf('origin') !== -1 ||
                id.indexOf('from') !== -1 ||
                id.indexOf('source') !== -1 ||
                id.indexOf('origin') !== -1 ||
                label.indexOf('from') !== -1 ||
                label.indexOf('source') !== -1 ||
                label.indexOf('origin') !== -1 ||
                label.indexOf('current location') !== -1
            ) && (
                name.indexOf('location') !== -1 ||
                id.indexOf('location') !== -1 ||
                label.indexOf('location') !== -1 ||
                label.indexOf('warehouse') !== -1
            );
        });

        if (candidates.length) {
            return candidates[0];
        }

        // Fallback: first location select, but avoid "to/destination" labels.
        return qsa('select').find(function (select) {
            var name = (select.getAttribute('name') || '').toLowerCase();
            var id = (select.getAttribute('id') || '').toLowerCase();
            var label = labelNear(select);

            var isLocation = name.indexOf('location') !== -1 || id.indexOf('location') !== -1 || label.indexOf('location') !== -1 || label.indexOf('warehouse') !== -1;
            var isDestination = name.indexOf('to') !== -1 || name.indexOf('destination') !== -1 || id.indexOf('to') !== -1 || id.indexOf('destination') !== -1 || label.indexOf('to') !== -1 || label.indexOf('destination') !== -1;

            return isLocation && !isDestination;
        });
    }

    function findQtyInput() {
        return qsa('input').find(function (input) {
            var name = (input.getAttribute('name') || '').toLowerCase();
            var id = (input.getAttribute('id') || '').toLowerCase();
            return name === 'quantity' || name.indexOf('quantity') !== -1 || name.indexOf('qty') !== -1 || id.indexOf('quantity') !== -1 || id.indexOf('qty') !== -1;
        });
    }

    function setSelectValue(select, value, label) {
        if (!select || value === null || value === undefined || value === '') return;

        var has = false;
        qsa('option', select).forEach(function (opt) {
            if (String(opt.value) === String(value)) has = true;
        });

        if (!has) {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label || String(value);
            select.appendChild(opt);
        }

        select.value = String(value);

        if (window.jQuery) {
            window.jQuery(select).val(String(value)).trigger('change');
        }

        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function hideSelectAndSelect2(select) {
        if (!select) return;

        select.style.display = 'none';
        select.classList.add('d-none');

        if (select.id) {
            var container = qs('#select2-' + select.id + '-container');
            if (container) {
                var s2wrap = container.closest('.select2, .select2-container');
                if (s2wrap) {
                    s2wrap.style.display = 'none';
                    s2wrap.classList.add('d-none');
                }
            }

            qsa('.select2, .select2-container').forEach(function (s2) {
                if (qs('#select2-' + select.id + '-container', s2)) {
                    s2.style.display = 'none';
                    s2.classList.add('d-none');
                }
            });
        }

        var parent = select.parentElement;
        if (parent) {
            qsa('.select2, .select2-container', parent).forEach(function (s2) {
                s2.style.display = 'none';
                s2.classList.add('d-none');
            });
        }
    }

    function ensureSerialField(form) {
        var serial =
            qs('input[name="warehouse_item_serial_id"]', form) ||
            qs('select[name="warehouse_item_serial_id"]', form) ||
            qs('input[name="serial_id"]', form) ||
            qs('select[name="serial_id"]', form);

        if (serial) return serial;

        serial = document.createElement('input');
        serial.type = 'hidden';
        serial.name = 'warehouse_item_serial_id';
        serial.id = 'transfer_picker_serial_id';
        form.appendChild(serial);

        return serial;
    }

    function ensurePickerTargetRow(form) {
        var row = qs('#transferGlobalPickerTargetRow', form);
        if (row) return row;

        row = document.createElement('div');
        row.id = 'transferGlobalPickerTargetRow';
        row.className = 'd-none';
        row.setAttribute('data-material-row', '1');

        var inv = document.createElement('select');
        inv.name = '_transfer_picker[warehouse_inventory_id]';
        inv.className = 'd-none';

        var serial = document.createElement('select');
        serial.name = '_transfer_picker[warehouse_item_serial_id]';
        serial.className = 'd-none';

        row.appendChild(inv);
        row.appendChild(serial);
        form.appendChild(row);

        return row;
    }

    function createPickerUI(itemSelect) {
        if (!itemSelect || itemSelect.dataset.transferPickerReady === '1') return;

        var form = itemSelect.closest('form');
        if (!form) return;

        itemSelect.dataset.transferPickerReady = '1';

        hideSelectAndSelect2(itemSelect);
        ensurePickerTargetRow(form);
        ensureSerialField(form);

        var holder = document.createElement('div');
        holder.id = 'transferPickerVisibleBox';
        holder.className = 'transfer-picker-visible-box';

        holder.innerHTML =
            '<div class="transfer-picker-summary" id="transferPickerSummary">' +
                '<div class="text-muted">No item selected.</div>' +
            '</div>' +
            '<button type="button" class="btn btn-outline-primary btn-sm mt-2 transfer-open-picker-btn" id="transferOpenPickerBtn">' +
                'Search / Select Item' +
            '</button>' +
            '<div class="small text-muted mt-1">Search item, view details, select serial if required.</div>';

        var label = findItemLabel();

        if (label && label.parentElement) {
            label.parentElement.insertBefore(holder, itemSelect);
        } else if (itemSelect.parentElement) {
            itemSelect.parentElement.insertBefore(holder, itemSelect);
        }

        var btn = qs('#transferOpenPickerBtn', holder);
        if (btn) {
            btn.addEventListener('click', function () {
                var targetRow = qs('#transferGlobalPickerTargetRow', form);

                if (window.WMCItemPicker && typeof window.WMCItemPicker.open === 'function') {
                    window.WMCItemPicker.open(targetRow);
                    return;
                }

                if (window.WMCItemPicker && typeof window.WMCItemPicker.initRows === 'function') {
                    window.WMCItemPicker.initRows();
                    setTimeout(function () {
                        var generatedBtn = qs('.wmc-open-item-picker', targetRow);
                        if (generatedBtn) {
                            generatedBtn.click();
                        } else {
                            alert('Item picker loaded but cannot open. Send wmc-item-picker.js if this appears.');
                        }
                    }, 100);
                    return;
                }

                alert('Global item picker script not loaded. Please refresh with CTRL + F5.');
            });
        }

        if (window.WMCItemPicker && typeof window.WMCItemPicker.initRows === 'function') {
            window.WMCItemPicker.initRows();
        }
    }

    function updateSummary(item, serialLabel, qty) {
        var box = qs('#transferPickerSummary');
        if (!box || !item) return;

        var photo = item.photo_url || item.image_url || '';
        var photoHtml = photo
            ? '<img src="' + photo + '" alt="" style="width:46px;height:46px;object-fit:contain;border:1px solid rgba(58,87,232,.25);border-radius:8px;background:#fff;">'
            : '<div style="width:46px;height:46px;border:1px solid rgba(58,87,232,.18);border-radius:8px;background:#f7f8fb;display:flex;align-items:center;justify-content:center;font-size:10px;color:#8a92a6;">No Photo</div>';

        box.innerHTML =
            '<div class="d-flex align-items-center gap-2">' +
                photoHtml +
                '<div class="flex-grow-1">' +
                    '<div class="fw-semibold text-dark">' + (item.item_code || 'ITEM') + ' - ' + (item.item_name || '') + '</div>' +
                    '<div class="small text-muted">' + (item.category_name || '-') + ' | Source: ' + (item.location_name || '-') + '</div>' +
                    '<div class="small text-muted">Available: ' + (item.available_qty ?? '-') + ' | Serial: ' + (serialLabel || '-') + ' | Qty: ' + (qty || 1) + '</div>' +
                '</div>' +
            '</div>';
    }

    function applyPickedItem(detail) {
        if (!onTransferPage() || !detail || !detail.item) return;

        var item = detail.item;
        var form = qs('form');
        if (!form) return;

        var itemSelect = findItemSelect();
        var sourceLocation = findSourceLocationSelect();
        var qtyInput = findQtyInput();
        var serialField = ensureSerialField(form);

        var itemLabel = (item.item_code || 'ITEM') + ' - ' + (item.item_name || '');
        var sourceLabel = item.location_name || 'Selected Source Location';

        if (itemSelect) {
            setSelectValue(itemSelect, item.item_id, itemLabel);
            hideSelectAndSelect2(itemSelect);
        }

        if (sourceLocation && item.location_id) {
            setSelectValue(sourceLocation, item.location_id, sourceLabel);
        }

        if (qtyInput) {
            qtyInput.value = detail.quantity || 1;
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (serialField) {
            serialField.value = detail.serial_id || '';
            serialField.dispatchEvent(new Event('change', { bubbles: true }));
        }

        updateSummary(item, detail.serial_label || detail.serial_id || '', detail.quantity || 1);
    }

    function bootTransferPicker() {
        if (!onTransferPage()) return;

        var itemSelect = findItemSelect();
        if (!itemSelect) return;

        createPickerUI(itemSelect);
        hideSelectAndSelect2(itemSelect);
    }

    document.addEventListener('wmc:item-picker:selected', function (event) {
        applyPickedItem(event.detail || {});
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootTransferPicker);
    } else {
        bootTransferPicker();
    }

    setTimeout(bootTransferPicker, 300);
    setTimeout(bootTransferPicker, 1000);

    setInterval(function () {
        if (onTransferPage()) {
            var itemSelect = findItemSelect();
            if (itemSelect) hideSelectAndSelect2(itemSelect);
        }
    }, 1000);
})();
</script>

<style>
    .transfer-picker-visible-box {
        border: 1px solid rgba(58, 87, 232, .18);
        border-radius: 10px;
        padding: 10px;
        background: linear-gradient(145deg, #fbfcff, #ffffff);
        box-shadow: 0 8px 18px rgba(35, 45, 66, .045);
    }

    .transfer-picker-summary {
        min-height: 44px;
    }

    .transfer-open-picker-btn {
        width: 100%;
        border-color: #3a57e8 !important;
        color: #3a57e8 !important;
        font-weight: 600;
        border-radius: 8px;
    }

    .transfer-open-picker-btn:hover {
        background: #3a57e8 !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(58, 87, 232, .22);
    }

    /* Same picker modal UI/photo zoom consistency */
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

<div id="wmcTransferPhotoZoomOverlay" class="wmc-force-photo-zoom-overlay" aria-hidden="true">
    <div class="wmc-force-photo-zoom-panel">
        <button type="button" class="wmc-force-photo-zoom-close" aria-label="Close preview">&times;</button>
        <div class="wmc-force-photo-zoom-header">
            <div class="wmc-force-photo-zoom-title">Item Photo Preview</div>
            <div class="wmc-force-photo-zoom-subtitle" id="wmcTransferPhotoZoomSubtitle">Warehouse item image</div>
        </div>
        <div class="wmc-force-photo-zoom-body">
            <img id="wmcTransferPhotoZoomImg" src="" alt="Item Photo Preview">
        </div>
    </div>
</div>

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
        var overlay = qs('#wmcTransferPhotoZoomOverlay');
        var zoomImg = qs('#wmcTransferPhotoZoomImg');
        var subtitle = qs('#wmcTransferPhotoZoomSubtitle');
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
        var overlay = qs('#wmcTransferPhotoZoomOverlay');
        if (!overlay) return;

        overlay.classList.remove('show');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function bindEvents() {
        var box = qs('#wmcItemPickerModal .wmc-item-picker-photo');
        var overlay = qs('#wmcTransferPhotoZoomOverlay');
        var closeBtn = qs('#wmcTransferPhotoZoomOverlay .wmc-force-photo-zoom-close');

        if (box && box.dataset.transferZoomBound !== '1') {
            box.dataset.transferZoomBound = '1';

            box.addEventListener('click', openZoom);

            box.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openZoom();
                }
            });
        }

        if (overlay && overlay.dataset.transferZoomBound !== '1') {
            overlay.dataset.transferZoomBound = '1';

            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) closeZoom();
            });
        }

        if (closeBtn && closeBtn.dataset.transferZoomBound !== '1') {
            closeBtn.dataset.transferZoomBound = '1';
            closeBtn.addEventListener('click', closeZoom);
        }

        if (document.body.dataset.transferZoomEscBound !== '1') {
            document.body.dataset.transferZoomEscBound = '1';
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeZoom();
            });
        }
    }

    function bootZoom() {
        bindEvents();
        refreshPhotoBox();
        setInterval(refreshPhotoBox, 700);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootZoom);
    } else {
        bootZoom();
    }

    document.addEventListener('click', function () {
        setTimeout(function () {
            bindEvents();
            refreshPhotoBox();
        }, 80);
    });
})();
</script>
{{-- force-transfer-global-item-picker-phase6-end --}}

