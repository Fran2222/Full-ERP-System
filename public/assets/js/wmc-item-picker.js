(function () {
    var state = {
        targetRow: null,
        selectedItem: null,
        selectedSerial: null,
        searchTimer: null,
        lastRows: []
    };

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function qsa(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function money(value) {
        var n = parseFloat(value || 0);
        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function csrf() {
        var token = qs('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    function apiBase() {
        return (window.WMC_ITEM_PICKER_BASE || '').replace(/\/$/, '');
    }

    function isTransferContext() {
        return state.targetRow && state.targetRow.dataset && state.targetRow.dataset.itemPickerContext === 'transfer';
    }

    function isBulkSerialContext() {
        return state.targetRow && state.targetRow.dataset && ['transfer', 'stock-out'].indexOf(state.targetRow.dataset.itemPickerContext) !== -1;
    }

    function transferSourceParams() {
        if (!state.targetRow || !state.targetRow.dataset) {
            return { branchId: '', locationId: '' };
        }

        var context = state.targetRow.dataset.itemPickerContext || '';
        if (['transfer', 'stock-out'].indexOf(context) === -1) {
            return { branchId: '', locationId: '' };
        }

        return {
            branchId: state.targetRow.dataset.fromBranchId || state.targetRow.dataset.sourceBranchId || state.targetRow.dataset.branchId || '',
            locationId: state.targetRow.dataset.fromLocationId || state.targetRow.dataset.sourceLocationId || state.targetRow.dataset.locationId || ''
        };
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf()
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed: ' + response.status);
            }
            return response.json();
        });
    }

    function getMaterialRows() {
        var selects = qsa('select[name*="[warehouse_inventory_id]"], select[name*="warehouse_inventory_id"]');
        var rows = [];

        selects.forEach(function (select) {
            var row =
                select.closest('.material-row') ||
                select.closest('[data-material-row]') ||
                select.closest('tr') ||
                select.closest('.row') ||
                select.closest('.card-body') ||
                select.parentElement;

            if (row && rows.indexOf(row) === -1) {
                rows.push(row);
            }
        });

        return rows;
    }

    function rowFind(row, kind) {
        if (!row) return null;

        if (kind === 'inventory') {
            return qs('select[name*="[warehouse_inventory_id]"], select[name*="warehouse_inventory_id"]', row);
        }

        if (kind === 'serial') {
            return qs('select[name*="[warehouse_item_serial_id]"], select[name*="warehouse_item_serial_id"]', row);
        }

        if (kind === 'qty') {
            return qs('input[name*="[quantity]"], input[name*="quantity"]', row);
        }

        if (kind === 'remarks') {
            return qs('input[name*="[remarks]"], textarea[name*="[remarks]"], input[name*="remarks"], textarea[name*="remarks"]', row);
        }

        return null;
    }

    function ensurePickerButton(row) {
        if (!row || row.dataset.wmcPickerReady === '1') {
            return;
        }

        if (row.dataset && row.dataset.itemPickerContext === 'stock-out') {
            return;
        }

        var inv = rowFind(row, 'inventory');
        if (!inv) {
            return;
        }

        row.dataset.wmcPickerReady = '1';

        inv.classList.add('wmc-global-picker-hidden-native');

        var wrap = document.createElement('div');
        wrap.className = 'wmc-selected-material-summary';
        wrap.innerHTML = '<div class="text-muted">No item selected.</div>';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-primary btn-sm wmc-open-item-picker mt-2';
        btn.innerHTML = 'Search / Select Item';

        btn.addEventListener('click', function () {
            openPicker(row);
        });

        inv.parentNode.insertBefore(wrap, inv);
        inv.parentNode.insertBefore(btn, inv.nextSibling);

        updateRowSummary(row);
    }

    function initRows() {
        getMaterialRows().forEach(ensurePickerButton);
    }

    function setSelectValue(select, value, label) {
        if (!select) return;

        var exists = false;
        qsa('option', select).forEach(function (option) {
            if (String(option.value) === String(value)) {
                exists = true;
            }
        });

        if (!exists && value !== null && value !== undefined && value !== '') {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = label || value;
            select.appendChild(opt);
        }

        select.value = value || '';
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function updateRowSummary(row) {
        if (!row) return;

        var summary = qs('.wmc-selected-material-summary', row);
        var inv = rowFind(row, 'inventory');
        var serial = rowFind(row, 'serial');
        var qty = rowFind(row, 'qty');

        if (!summary || !inv) return;

        var selected = inv.options[inv.selectedIndex];
        if (!selected || !inv.value) {
            summary.innerHTML = '<div class="text-muted">No item selected.</div>';
            return;
        }

        var serialText = '';
        if (serial && serial.value) {
            serialText = '<div class="small text-muted">Serial: ' + escapeHtml(serial.options[serial.selectedIndex].textContent || serial.value) + '</div>';
        }

        summary.innerHTML =
            '<div class="fw-semibold">' + escapeHtml(selected.textContent || 'Selected item') + '</div>' +
            '<div class="small text-muted">Qty: ' + escapeHtml(qty ? qty.value : '1') + '</div>' +
            serialText;
    }

    function openPicker(row) {
        state.targetRow = row;
        state.selectedItem = null;
        state.selectedSerial = null;

        resetPreview();

        var search = qs('#wmcItemPickerSearch');
        var serialized = qs('#wmcItemPickerSerializedFilter');
        var availableOnly = qs('#wmcItemPickerAvailableOnly');
        var serialSearch = qs('#wmcItemPickerSerialSearch');
        var remarks = qs('#wmcItemPickerRemarks');

        if (search) search.value = '';
        if (serialized) serialized.value = '';
        if (availableOnly) availableOnly.checked = true;
        if (serialSearch) serialSearch.value = '';
        if (remarks) remarks.value = '';

        searchItems();

        var modalEl = qs('#wmcItemPickerModal');
        if (!modalEl) {
            alert('Item picker modal not found.');
            return;
        }

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(modalEl).modal('show');
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }

        setTimeout(function () {
            if (search) search.focus();
        }, 250);
    }

    function resetPreview() {
        var empty = qs('#wmcItemPickerPreviewEmpty');
        var preview = qs('#wmcItemPickerPreview');
        if (empty) empty.classList.remove('d-none');
        if (preview) preview.classList.add('d-none');

        var results = qs('#wmcItemPickerResults');
        if (results) {
            results.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Loading items...</td></tr>';
        }
    }

    function searchItems() {
        var q = qs('#wmcItemPickerSearch') ? qs('#wmcItemPickerSearch').value : '';
        var serialized = qs('#wmcItemPickerSerializedFilter') ? qs('#wmcItemPickerSerializedFilter').value : '';
        var availableOnly = qs('#wmcItemPickerAvailableOnly') && qs('#wmcItemPickerAvailableOnly').checked ? '1' : '0';

        var source = transferSourceParams();
        var url = apiBase() + '/warehouse/item-picker/search?q=' + encodeURIComponent(q) +
            '&serialized=' + encodeURIComponent(serialized) +
            '&available_only=' + encodeURIComponent(availableOnly) +
            '&branch_id=' + encodeURIComponent(source.branchId || '') +
            '&location_id=' + encodeURIComponent(source.locationId || '');

        fetchJson(url)
            .then(function (json) {
                renderResults(json.data || []);
            })
            .catch(function (err) {
                var results = qs('#wmcItemPickerResults');
                if (results) {
                    results.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Unable to load items.</td></tr>';
                }
                console.error(err);
            });
    }

    function renderResults(rows) {
        state.lastRows = rows;

        var tbody = qs('#wmcItemPickerResults');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No items found.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function (row, index) {
            var type = row.is_serialized ? 'Serialized' : 'Regular';
            return '<tr data-index="' + index + '">' +
                '<td><a href="javascript:void(0)" class="fw-semibold">' + escapeHtml(row.item_code || '-') + '</a></td>' +
                '<td><div class="fw-semibold">' + escapeHtml(row.item_name || '-') + '</div><div class="small text-muted">' + escapeHtml(row.unit_name || '-') + '</div></td>' +
                '<td>' + escapeHtml(row.category_name || '-') + '</td>' +
                '<td><div>' + escapeHtml(row.location_name || '-') + '</div><div class="small text-muted">' + escapeHtml(row.branch_name || '-') + '</div></td>' +
                '<td class="text-end fw-semibold">' + escapeHtml(row.available || 0) + '</td>' +
                '<td><span class="badge ' + (row.is_serialized ? 'bg-info' : 'bg-secondary') + '">' + type + '</span></td>' +
                '</tr>';
        }).join('');

        qsa('tr[data-index]', tbody).forEach(function (tr) {
            tr.addEventListener('click', function () {
                qsa('tr', tbody).forEach(function (r) { r.classList.remove('wmc-picker-active'); });
                tr.classList.add('wmc-picker-active');
                selectPreview(rows[parseInt(tr.dataset.index, 10)]);
            });
        });
    }

    function selectPreview(item) {
        state.selectedItem = item;
        state.selectedSerial = null;

        var empty = qs('#wmcItemPickerPreviewEmpty');
        var preview = qs('#wmcItemPickerPreview');

        if (empty) empty.classList.add('d-none');
        if (preview) preview.classList.remove('d-none');

        var photo = qs('#wmcItemPickerPhoto');
        var fallback = qs('#wmcItemPickerPhotoFallback');

        if (photo && fallback) {
            if (item.image_url) {
                photo.src = item.image_url;
                photo.classList.remove('d-none');
                fallback.classList.add('d-none');
            } else {
                photo.src = '';
                photo.classList.add('d-none');
                fallback.classList.remove('d-none');
            }
        }

        setText('#wmcItemPickerCode', item.item_code || '-');
        setText('#wmcItemPickerName', item.item_name || '-');
        setText('#wmcItemPickerType', item.is_serialized ? 'Serialized' : 'Regular Item');
        setText('#wmcItemPickerAvailable', item.available || 0);
        setText('#wmcItemPickerOnHand', item.on_hand || 0);
        var canViewCostPrice = item.can_view_cost_price === true || window.WMC_CAN_VIEW_COST_PRICE === true;
        var costEl = qs('#wmcItemPickerCost');
        if (costEl) {
            costEl.textContent = canViewCostPrice ? money(item.cost_price) : 'Restricted';
            var costCard = costEl.closest('.border, .card, .col-6, .col-md-6, .col-md-4');
            if (costCard) {
                costCard.style.display = canViewCostPrice ? '' : 'none';
            }
        }
        setText('#wmcItemPickerPrice', money(item.selling_price));
        setText('#wmcItemPickerDescription', item.description || '-');
        setText('#wmcItemPickerSpecs', item.specs || '-');

        var serialBox = qs('#wmcItemPickerSerialBox');
        var qty = qs('#wmcItemPickerQty');

        if (item.is_serialized) {
            if (isTransferContext()) {
                if (serialBox) serialBox.classList.add('d-none');
                if (qty) {
                    qty.value = 1;
                    qty.readOnly = true;
                }
            } else {
                if (serialBox) serialBox.classList.remove('d-none');
                if (qty) {
                    qty.value = 1;
                    qty.readOnly = true;
                }
                loadSerials('');
            }
        } else {
            if (serialBox) serialBox.classList.add('d-none');
            if (qty) {
                qty.value = 1;
                qty.readOnly = false;
                qty.max = item.available || '';
            }
        }
    }

    function setText(sel, value) {
        var el = qs(sel);
        if (el) el.textContent = value;
    }

    function loadSerials(q) {
        var select = qs('#wmcItemPickerSerialSelect');
        if (!select || !state.selectedItem) return;

        select.innerHTML = '<option value="">Loading serials...</option>';

        var source = transferSourceParams();
        var url = apiBase() + '/warehouse/item-picker/serials?item_id=' + encodeURIComponent(state.selectedItem.item_id) +
            '&inventory_id=' + encodeURIComponent(state.selectedItem.inventory_id) +
            '&location_id=' + encodeURIComponent(state.selectedItem.location_id || source.locationId || '') +
            '&branch_id=' + encodeURIComponent(state.selectedItem.branch_id || source.branchId || '') +
            '&q=' + encodeURIComponent(q || '');

        fetchJson(url)
            .then(function (json) {
                var rows = json.data || [];
                if (!rows.length) {
                    select.innerHTML = '<option value="">No available serials</option>';
                    return;
                }

                select.innerHTML = '<option value="">Select serial</option>' + rows.map(function (row) {
                    return '<option value="' + escapeHtml(row.id) + '">' + escapeHtml(row.serial_no) + '</option>';
                }).join('');
            })
            .catch(function () {
                select.innerHTML = '<option value="">Unable to load serials</option>';
            });
    }

    function useSelectedItem() {
        if (!state.targetRow || !state.selectedItem) {
            alert('Please select an item first.');
            return;
        }

        var item = state.selectedItem;
        var serialSelect = qs('#wmcItemPickerSerialSelect');
        var qtyInput = qs('#wmcItemPickerQty');
        var remarksInput = qs('#wmcItemPickerRemarks');

        if (item.is_serialized && !isBulkSerialContext() && (!serialSelect || !serialSelect.value)) {
            alert('Please select a serial number.');
            return;
        }

        var qty = parseFloat(qtyInput ? qtyInput.value : 1) || 1;

        if (item.is_serialized) {
            qty = 1;
        }

        if (!item.is_serialized && qty > parseFloat(item.available || 0)) {
            alert('Quantity cannot exceed available stock.');
            return;
        }

        var inv = rowFind(state.targetRow, 'inventory');
        var serial = rowFind(state.targetRow, 'serial');
        var rowQty = rowFind(state.targetRow, 'qty');
        var rowRemarks = rowFind(state.targetRow, 'remarks');

        var itemLabel = item.item_code + ' - ' + item.item_name + ' | ' + item.branch_name + ' / ' + item.location_name;
        setSelectValue(inv, item.inventory_id, itemLabel);

        var serialLabel = '';
        if (serial) {
            if (item.is_serialized && !isBulkSerialContext()) {
                serialLabel = serialSelect.options[serialSelect.selectedIndex].textContent || serialSelect.value;
                setSelectValue(serial, serialSelect.value, serialLabel);
            } else {
                setSelectValue(serial, '', 'No serial');
            }
        }

        if (rowQty) {
            rowQty.value = qty;
            rowQty.readOnly = !!item.is_serialized;
            rowQty.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (rowRemarks && remarksInput && remarksInput.value) {
            rowRemarks.value = remarksInput.value;
            rowRemarks.dispatchEvent(new Event('change', { bubbles: true }));
        }

        try {
            if (!serialLabel && serialSelect && serialSelect.value && serialSelect.options[serialSelect.selectedIndex]) {
                serialLabel = serialSelect.options[serialSelect.selectedIndex].textContent || serialSelect.value;
            }

            document.dispatchEvent(new CustomEvent('wmc:item-picker:selected', {
                detail: {
                    item: item,
                    serial_id: (item.is_serialized && !isBulkSerialContext() && serialSelect) ? (serialSelect.value || '') : '',
                    serial_label: serialLabel || '',
                    quantity: qty,
                    remarks: remarksInput ? remarksInput.value : '',
                    targetRow: state.targetRow
                }
            }));
        } catch (e) {
            console.warn('WMC item picker selected event failed.', e);
        }

        updateRowSummary(state.targetRow);

        var modalEl = qs('#wmcItemPickerModal');
        if (modalEl) {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            } else if (window.jQuery && window.jQuery.fn.modal) {
                window.jQuery(modalEl).modal('hide');
            } else {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
            }
        }
    }

    function bindModalEvents() {
        var search = qs('#wmcItemPickerSearch');
        var serialized = qs('#wmcItemPickerSerializedFilter');
        var availableOnly = qs('#wmcItemPickerAvailableOnly');
        var serialSearch = qs('#wmcItemPickerSerialSearch');
        var useBtn = qs('#wmcItemPickerUseBtn');

        if (search && !search.dataset.bound) {
            search.dataset.bound = '1';
            search.addEventListener('input', function () {
                clearTimeout(state.searchTimer);
                state.searchTimer = setTimeout(searchItems, 250);
            });
        }

        if (serialized && !serialized.dataset.bound) {
            serialized.dataset.bound = '1';
            serialized.addEventListener('change', searchItems);
        }

        if (availableOnly && !availableOnly.dataset.bound) {
            availableOnly.dataset.bound = '1';
            availableOnly.addEventListener('change', searchItems);
        }

        if (serialSearch && !serialSearch.dataset.bound) {
            serialSearch.dataset.bound = '1';
            serialSearch.addEventListener('input', function () {
                clearTimeout(state.searchTimer);
                state.searchTimer = setTimeout(function () {
                    loadSerials(serialSearch.value);
                }, 250);
            });
        }

        if (useBtn && !useBtn.dataset.bound) {
            useBtn.dataset.bound = '1';
            useBtn.addEventListener('click', useSelectedItem);
        }
    }

    function observeRows() {
        if (!window.MutationObserver) return;

        var observer = new MutationObserver(function () {
            initRows();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindModalEvents();
        initRows();
        observeRows();

        document.addEventListener('click', function (event) {
            var text = (event.target && event.target.textContent || '').trim().toLowerCase();
            if (text.indexOf('add material') !== -1) {
                setTimeout(initRows, 150);
                setTimeout(initRows, 400);
            }
        });
    });

    window.WMCItemPicker = {
        initRows: initRows,
        openPicker: openPicker
    };
})();
