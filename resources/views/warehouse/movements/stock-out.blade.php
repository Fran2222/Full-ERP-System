<x-app-layout>
@include('warehouse.partials.styles')
<div class="container-fluid py-4">@include('warehouse.partials.nav')
<div class="card wmc-card"><div class="card-header bg-white border-0"><h4 class="mb-0">Stock Out / Issue</h4><small class="text-muted">Deduct released items from inventory.</small></div><div class="card-body">
<form method="POST" action="{{ route('warehouse.stock-out.store') }}">@csrf
@include('warehouse.movements._form', ['mode' => 'stock_out', 'buttonText' => 'Save Stock Out'])
</form></div></div></div></x-app-layout>


{{-- global-item-picker-stock-out-phase5 --}}
<x-warehouse.item-picker-modal />

<script>
    window.WMC_ITEM_PICKER_BASE = "{{ url('') }}";
</script>
<script src="{{ asset('assets/js/wmc-item-picker.js') }}"></script>


{{-- stock-out-global-item-picker-force-phase5-v2-start --}}
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

    function isStockOutPage() {
        return window.location.pathname.indexOf('/warehouse/stock-out') !== -1;
    }

    function labelNear(el) {
        var wrap = el.closest('.form-group, .mb-3, .col-md-6, .col-md-4, .col, .row') || el.parentElement;
        var label = wrap ? qs('label', wrap) : null;
        return label ? label.textContent.trim().toLowerCase() : '';
    }

    function optionTextLooksLikeItem(select) {
        return qsa('option', select).some(function (opt) {
            var t = (opt.textContent || '').toLowerCase();
            return t.indexOf('item-') !== -1 || t.indexOf('serialized') !== -1 || t.indexOf('pcs') !== -1;
        });
    }

    function findNativeItemSelect(form) {
        var selects = qsa('select', form);

        return selects.find(function (select) {
            var name = (select.getAttribute('name') || '').toLowerCase();
            var label = labelNear(select);

            if (name.indexOf('location') !== -1 || name.indexOf('branch') !== -1 || name.indexOf('status') !== -1) {
                return false;
            }

            if (name === 'item_id' || name === 'warehouse_item_id' || name.indexOf('[item_id]') !== -1 || name.indexOf('[warehouse_item_id]') !== -1) {
                return true;
            }

            if (label === 'item' || label.indexOf('item') !== -1) {
                return true;
            }

            return optionTextLooksLikeItem(select);
        });
    }

    function findLocationSelect(form) {
        return qsa('select', form).find(function (select) {
            var name = (select.getAttribute('name') || '').toLowerCase();
            var label = labelNear(select);
            return name.indexOf('location') !== -1 || label.indexOf('location') !== -1;
        });
    }

    function findQtyInput(form) {
        return qsa('input', form).find(function (input) {
            var name = (input.getAttribute('name') || '').toLowerCase();
            return name === 'quantity' || name.indexOf('[quantity]') !== -1 || name.indexOf('qty') !== -1;
        });
    }

    function setSelectValue(select, value, label) {
        if (!select || value === undefined || value === null || value === '') {
            return;
        }

        var exists = false;
        qsa('option', select).forEach(function (opt) {
            if (String(opt.value) === String(value)) {
                exists = true;
            }
        });

        if (!exists) {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label || value;
            select.appendChild(opt);
        }

        select.value = value;

        if (window.jQuery) {
            window.jQuery(select).val(value).trigger('change');
        } else {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function hideNativeSelect(select) {
        if (!select || select.dataset.wmcStockOutHidden === '1') {
            return;
        }

        select.dataset.wmcStockOutHidden = '1';

        var select2 = null;
        if (select.nextElementSibling && select.nextElementSibling.classList.contains('select2')) {
            select2 = select.nextElementSibling;
        }

        if (!select2 && select.id) {
            select2 = qs('[aria-labelledby="select2-' + select.id + '-container"]');
            if (select2) {
                select2 = select2.closest('.select2');
            }
        }

        select.classList.add('d-none');
        select.style.display = 'none';

        if (select2) {
            select2.style.display = 'none';
        }
    }

    function ensureSerialHidden(form) {
        var serial =
            qs('select[name="warehouse_item_serial_id"]', form) ||
            qs('input[name="warehouse_item_serial_id"]', form) ||
            qs('select[name="serial_id"]', form) ||
            qs('input[name="serial_id"]', form);

        if (serial) {
            return serial;
        }

        serial = document.createElement('input');
        serial.type = 'hidden';
        serial.name = 'warehouse_item_serial_id';
        serial.id = 'wmc_stockout_serial_id';
        form.appendChild(serial);
        return serial;
    }

    function ensurePickerBridge(form, itemSelect) {
        if (!form || !itemSelect || itemSelect.dataset.wmcStockOutPickerReady === '1') {
            return;
        }

        itemSelect.dataset.wmcStockOutPickerReady = '1';

        var parent = itemSelect.parentElement || form;

        var bridgeWrap = document.createElement('div');
        bridgeWrap.className = 'wmc-stockout-picker-bridge mt-1';
        bridgeWrap.setAttribute('data-material-row', '1');

        var hiddenInv = document.createElement('select');
        hiddenInv.name = '_stockout_picker[warehouse_inventory_id]';
        hiddenInv.className = 'd-none wmc-stockout-hidden-inventory';
        hiddenInv.style.display = 'none';

        var hiddenSerial = document.createElement('select');
        hiddenSerial.name = '_stockout_picker[warehouse_item_serial_id]';
        hiddenSerial.className = 'd-none wmc-stockout-hidden-serial';
        hiddenSerial.style.display = 'none';

        bridgeWrap.appendChild(hiddenInv);
        bridgeWrap.appendChild(hiddenSerial);

        parent.insertBefore(bridgeWrap, itemSelect);

        hideNativeSelect(itemSelect);

        setTimeout(function () {
            if (window.WMCItemPicker && typeof window.WMCItemPicker.initRows === 'function') {
                window.WMCItemPicker.initRows();
            }
        }, 150);

        setTimeout(function () {
            if (window.WMCItemPicker && typeof window.WMCItemPicker.initRows === 'function') {
                window.WMCItemPicker.initRows();
            }
        }, 500);
    }

    function applySelectedToStockOut(detail) {
        if (!detail || !detail.item) {
            return;
        }

        var targetRow = detail.targetRow;
        if (!targetRow || !targetRow.classList || !targetRow.classList.contains('wmc-stockout-picker-bridge')) {
            return;
        }

        var form = targetRow.closest('form');
        if (!form) {
            return;
        }

        var item = detail.item;
        var itemSelect = findNativeItemSelect(form);
        var locationSelect = findLocationSelect(form);
        var qtyInput = findQtyInput(form);
        var serialInput = ensureSerialHidden(form);

        var itemLabel = (item.item_code || 'ITEM') + ' - ' + (item.item_name || 'Selected Item');
        var locationLabel = item.location_name || 'Selected Location';

        if (itemSelect) {
            setSelectValue(itemSelect, item.item_id, itemLabel);
        }

        if (locationSelect && item.location_id) {
            setSelectValue(locationSelect, item.location_id, locationLabel);
        }

        if (qtyInput) {
            qtyInput.value = detail.quantity || (item.is_serialized ? 1 : 1);
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (serialInput) {
            serialInput.value = detail.serial_id || '';
            serialInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function initStockOutPicker() {
        if (!isStockOutPage()) {
            return;
        }

        var form = qs('form');
        if (!form) {
            return;
        }

        var itemSelect = findNativeItemSelect(form);
        if (!itemSelect) {
            return;
        }

        ensurePickerBridge(form, itemSelect);
    }

    document.addEventListener('wmc:item-picker:selected', function (event) {
        applySelectedToStockOut(event.detail || {});
    });

    document.addEventListener('DOMContentLoaded', function () {
        initStockOutPicker();
        setTimeout(initStockOutPicker, 300);
        setTimeout(initStockOutPicker, 900);
    });

    document.addEventListener('click', function () {
        setTimeout(initStockOutPicker, 150);
    });
})();
</script>

<style>
    /* Stock Out item picker bridge */
    .wmc-stockout-picker-bridge .wmc-selected-material-summary {
        min-height: 42px;
        border: 1px solid rgba(58, 87, 232, .18);
        border-radius: 8px;
        background: linear-gradient(145deg, #fbfcff, #ffffff);
        padding: 10px 12px;
        box-shadow: 0 8px 18px rgba(35, 45, 66, .045);
    }

    .wmc-stockout-picker-bridge .wmc-open-item-picker {
        width: 100%;
        margin-top: 8px !important;
        border-color: #3a57e8;
        color: #3a57e8;
        font-weight: 600;
        border-radius: 8px;
    }

    .wmc-stockout-picker-bridge .wmc-open-item-picker:hover {
        background: #3a57e8;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(58, 87, 232, .22);
    }
</style>
{{-- stock-out-global-item-picker-force-phase5-v2-end --}}

