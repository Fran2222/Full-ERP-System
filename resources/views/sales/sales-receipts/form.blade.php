@php
    $oldItems = old('items');

    if (!$oldItems) {
        $oldItems = [
            ['item_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0, 'serial_ids' => []],
        ];
    }

    $selectedCustomerId = old('customer_id', '');
    $selectedBranchId = old('branch_id', '');
    $selectedLocationId = old('location_id', '');
@endphp

<style>
    .sales-select2-wrap .select2-container {
        width: 100% !important;
    }

    .sales-select2-wrap .select2-container--default .select2-selection--single {
        min-height: 44px;
        border-radius: 12px;
        border-color: #d9dee8;
        display: flex;
        align-items: center;
    }

    .sales-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 44px;
        padding-left: 14px;
        padding-right: 36px;
        font-weight: 600;
    }

    .sales-select2-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #8a94a6;
        font-weight: 500;
    }

    .sales-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 8px;
    }

    .select2-dropdown.sales-select2-dropdown {
        border-color: #d9dee8;
        border-radius: 12px;
        overflow: hidden;
    }

    .select2-dropdown.sales-select2-dropdown .select2-search__field {
        border-radius: 10px;
        border-color: #d9dee8 !important;
        min-height: 38px;
        padding: 8px 10px;
        outline: none;
    }

    .select2-dropdown.sales-select2-dropdown .select2-results__option {
        padding: 10px 12px;
        font-weight: 600;
    }

    .select2-dropdown.sales-select2-dropdown .select2-results__option--highlighted {
        background: #315cf6 !important;
    }

    .receipt-serial-wrap {
        border: 1px dashed #cdd6ea;
        border-radius: 14px;
        background: #fbfcff;
        padding: 14px;
    }

    .receipt-serialized-note {
        color: #315cf6;
        font-size: 12px;
        font-weight: 700;
    }
</style>

<div class="sales-form-section mb-4">
    <div class="sales-section-heading">
        <div>
            <h5 class="fw-bold mb-1">Receipt Information</h5>
            <p class="text-secondary mb-0">Select customer, receipt date, payment method, and inventory source.</p>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-6 col-lg-6">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            <select name="customer_id"
                    id="receiptCustomer"
                    class="form-select sales-input @error('customer_id') is-invalid @enderror">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                            data-code="{{ $customer->customer_code }}"
                            data-name="{{ $customer->customer_name }}"
                            data-address="{{ $customer->billing_address }}"
                            {{ (string) $selectedCustomerId === (string) $customer->id ? 'selected' : '' }}>
                        {{ $customer->customer_code }} - {{ $customer->customer_name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6">
            <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
            <input type="date"
                   name="receipt_date"
                   value="{{ old('receipt_date', now()->format('Y-m-d')) }}"
                   class="form-control sales-input @error('receipt_date') is-invalid @enderror">
            @error('receipt_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6">
            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method"
                    class="form-select sales-input @error('payment_method') is-invalid @enderror">
                @foreach(['Cash', 'Check', 'Bank Transfer', 'GCash', 'Credit Card', 'Others'] as $method)
                    <option value="{{ $method }}" {{ old('payment_method', 'Cash') === $method ? 'selected' : '' }}>
                        {{ $method }}
                    </option>
                @endforeach
            </select>
            @error('payment_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Branch <span class="text-danger">*</span></label>
            <select name="branch_id"
                    id="receiptBranch"
                    class="form-select sales-input @error('branch_id') is-invalid @enderror">
                <option value="">Select Branch</option>
                @foreach($branches as $branch)
                    @php
                        $branchName = $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id;
                    @endphp

                    <option value="{{ $branch->id }}"
                            data-name="{{ $branchName }}"
                            {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branchName }}
                    </option>
                @endforeach
            </select>
            @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Warehouse Location <span class="text-danger">*</span></label>
            <select name="location_id"
                    id="receiptLocation"
                    class="form-select sales-input @error('location_id') is-invalid @enderror">
                <option value="">Select Location</option>
                @foreach($locations as $location)
                    @php
                        $locationName = $location->location_name ?: $location->name;
                        $locationCode = $location->location_code ?: $location->code ?: $locationName;
                    @endphp

                    <option value="{{ $location->id }}"
                            data-branch="{{ $location->branch_id }}"
                            data-code="{{ $locationCode }}"
                            data-name="{{ $locationName }}"
                            {{ (string) $selectedLocationId === (string) $location->id ? 'selected' : '' }}>
                        {{ $locationCode }} - {{ $locationName }}
                    </option>
                @endforeach
            </select>
            @error('location_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Reference No.</label>
            <input type="text"
                   name="reference_no"
                   value="{{ old('reference_no') }}"
                   class="form-control sales-input @error('reference_no') is-invalid @enderror"
                   placeholder="OR / Check / Transaction No.">
            @error('reference_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="sales-customer-preview" id="receiptCustomerPreview">
                <div class="sales-info-label">Selected Customer</div>
                <div class="sales-preview-name" id="receiptCustomerPreviewName">No customer selected</div>
                <div class="sales-preview-sub" id="receiptCustomerPreviewAddress">Select a customer to preview details.</div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="sales-customer-preview" id="receiptInventoryPreview">
                <div class="sales-info-label">Inventory Source</div>
                <div class="sales-preview-name" id="receiptInventoryPreviewName">No source selected</div>
                <div class="sales-preview-sub" id="receiptInventoryPreviewSub">Select a branch and warehouse location.</div>
            </div>
        </div>
    </div>
</div>

<div class="sales-form-section mb-4">
    <div class="sales-section-heading">
        <div>
            <h5 class="fw-bold mb-1">Sales Receipt Items</h5>
            <p class="text-secondary mb-0">Add sold warehouse items. You can search by item code or item name.</p>
        </div>

        <button type="button" class="btn btn-outline-primary sales-soft-btn" onclick="addReceiptLine()">
            Add Line
        </button>
    </div>

    <div id="receipt-lines" class="mt-3">
        @foreach($oldItems as $index => $line)
            <div class="invoice-line-card receipt-line">
                <div class="invoice-line-top">
                    <div>
                        <div class="sales-info-label">Line Item</div>
                        <div class="fw-bold text-dark receipt-line-title">Item #{{ $loop->iteration }}</div>
                    </div>

                    <button type="button"
                            class="btn btn-outline-danger sales-soft-btn invoice-remove-btn"
                            onclick="removeReceiptLine(this)">
                        Remove
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4 col-lg-6 sales-select2-wrap">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="items[{{ $index }}][item_id]"
                                class="form-select sales-input receipt-item-select @error('items.' . $index . '.item_id') is-invalid @enderror"
                                data-placeholder="Search item code or name...">
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                @php
                                    $itemCode = $item->code ?: $item->item_code;
                                    $itemName = $item->name ?: $item->item_name;
                                    $price = $item->selling_price ?? 0;
                                    $isSerialized = (bool) ($item->is_serialized ?? false);
                                @endphp

                                <option value="{{ $item->id }}"
                                        data-price="{{ $price }}"
                                        data-serialized="{{ $isSerialized ? '1' : '0' }}"
                                        data-description="{{ $item->description }}"
                                        data-code="{{ $itemCode }}"
                                        data-name="{{ $itemName }}"
                                        {{ ($line['item_id'] ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $itemCode }} - {{ $itemName }}
                                </option>
                            @endforeach
                        </select>
                        @error('items.' . $index . '.item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               name="items[{{ $index }}][quantity]"
                               value="{{ $line['quantity'] ?? 1 }}"
                               class="form-control sales-input receipt-qty @error('items.' . $index . '.quantity') is-invalid @enderror">
                        @error('items.' . $index . '.quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[{{ $index }}][unit_price]"
                               value="{{ $line['unit_price'] ?? 0 }}"
                               class="form-control sales-input receipt-price @error('items.' . $index . '.unit_price') is-invalid @enderror">
                        @error('items.' . $index . '.unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Line Total</label>
                        <input type="text"
                               class="form-control sales-input receipt-line-total fw-bold text-primary"
                               readonly
                               value="0.00">
                    </div>

                    <div class="col-xl-2 col-lg-9">
                        <label class="form-label">Stock Preview</label>
                        <div class="sales-line-preview receipt-stock-preview">
                            <div class="sales-line-code">Available: 0.00</div>
                            <div class="sales-line-name">Select source and item</div>
                        </div>
                    </div>

                    <div class="col-md-12 receipt-serial-wrap" style="display: none;">
                        <label class="form-label">Serial Numbers <span class="text-danger">*</span></label>
                        <select name="items[{{ $index }}][serial_ids][]"
                                class="form-select sales-input receipt-serial-select @error('items.' . $index . '.serial_ids') is-invalid @enderror"
                                multiple
                                data-placeholder="Search and select available serial number(s)...">
                            @foreach(($line['serial_ids'] ?? []) as $oldSerialId)
                                <option value="{{ $oldSerialId }}" selected>Serial #{{ $oldSerialId }}</option>
                            @endforeach
                        </select>
                        <div class="receipt-serialized-note mt-2">Serialized item: quantity automatically follows the selected serial count.</div>
                        @error('items.' . $index . '.serial_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="items[{{ $index }}][description]"
                                  rows="2"
                                  class="form-control sales-input receipt-description"
                                  placeholder="Optional item description">{{ $line['description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="sales-form-section h-100">
            <div class="sales-section-heading mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Notes</h5>
                    <p class="text-secondary mb-0">Optional remarks or receipt instructions.</p>
                </div>
            </div>

            <textarea name="notes"
                      rows="7"
                      class="form-control sales-input @error('notes') is-invalid @enderror"
                      placeholder="Enter sales receipt notes">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-lg-5">
        <div class="sales-total-card h-100">
            <div class="sales-section-heading mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Receipt Summary</h5>
                    <p class="text-secondary mb-0">Computed from sold item lines.</p>
                </div>
            </div>

            <div class="sales-total-row">
                <span>Subtotal</span>
                <strong id="receipt-subtotal">0.00</strong>
            </div>

            <hr>

            <div class="sales-total-row sales-total-main">
                <span>Total Paid</span>
                <strong id="receipt-total">0.00</strong>
            </div>

            <div class="sales-total-note mt-3">
                Saving this sales receipt immediately deducts stock from the selected branch/location.
            </div>
        </div>
    </div>
</div>

<script>
    const receiptStockMap = @json($stockMap ?? []);
    const receiptSerialSearchUrl = @json(route('sales.sales-receipts.available-serials'));
    let receiptLineIndex = {{ count($oldItems) }};

    function hasSelect2() {
        return window.jQuery && typeof jQuery.fn.select2 === 'function';
    }

    function loadSelect2Assets(callback) {
        if (!window.jQuery) {
            return;
        }

        if (hasSelect2()) {
            callback();
            return;
        }

        if (!document.getElementById('sales-select2-css')) {
            const css = document.createElement('link');
            css.id = 'sales-select2-css';
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            document.head.appendChild(css);
        }

        if (!document.getElementById('sales-select2-js')) {
            const script = document.createElement('script');
            script.id = 'sales-select2-js';
            script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            script.onload = callback;
            document.body.appendChild(script);
        } else {
            const waitForSelect2 = setInterval(function () {
                if (hasSelect2()) {
                    clearInterval(waitForSelect2);
                    callback();
                }
            }, 100);
        }
    }

    function initReceiptItemSelect2() {
        if (!hasSelect2()) {
            return;
        }

        $('.receipt-item-select').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: $select.data('placeholder') || 'Search item code or name...',
                allowClear: true,
                dropdownCssClass: 'sales-select2-dropdown',
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

            $select.off('select2:select select2:clear').on('select2:select select2:clear', function () {
                handleReceiptItemChange(this);
            });
        });
    }


    function initReceiptSerialSelect2() {
        if (!hasSelect2()) {
            document.querySelectorAll('.receipt-line').forEach(function (line) {
                if (isReceiptLineSerialized(line)) {
                    loadReceiptSerialOptions(line);
                }
            });
            return;
        }

        $('.receipt-serial-select').each(function () {
            const $select = $(this);
            const line = this.closest('.receipt-line');

            if (!isReceiptLineSerialized(line)) {
                return;
            }

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: $select.data('placeholder') || 'Search serial number...',
                allowClear: true,
                dropdownCssClass: 'sales-select2-dropdown',
                ajax: {
                    url: receiptSerialSearchUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        const parentLine = this[0].closest('.receipt-line');
                        return {
                            q: params.term || '',
                            item_id: parentLine.querySelector('.receipt-item-select')?.value || '',
                            branch_id: getSelectedBranchId(),
                            location_id: getSelectedLocationId(),
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results || []
                        };
                    },
                    cache: true
                }
            });

            $select.off('change.receiptSerial').on('change.receiptSerial', function () {
                updateSerializedQuantity(this.closest('.receipt-line'));
                calculateReceiptTotals();
            });
        });
    }

    function destroyReceiptSerialSelect2(context) {
        if (!hasSelect2()) {
            return;
        }

        /*
         * IMPORTANT:
         * Do not remove every .select2-container inside the receipt line.
         * The item dropdown is also a Select2 field. The old code removed all
         * Select2 containers when branch/location changed, so the item select
         * stayed hidden and looked blank.
         */
        $(context).find('.receipt-serial-select').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            // Clean only an orphaned container beside this serial field, if any.
            const $nextContainer = $select.next('.select2-container');
            if ($nextContainer.length) {
                $nextContainer.remove();
            }
        });
    }

    function destroyReceiptItemSelect2(context) {
        if (!hasSelect2()) {
            return;
        }

        $(context).find('.receipt-item-select').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        });

    }

    function showReceiptNotice(message, type = 'warning') {
        if (window.Swal) {
            Swal.fire({
                icon: type,
                title: type === 'warning' ? 'Notice' : 'Done',
                text: message,
                timer: 1800,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    function refreshReceiptIndexes() {
        document.querySelectorAll('.receipt-line').forEach(function (line, index) {
            line.querySelectorAll('select, input, textarea').forEach(function (field) {
                const name = field.getAttribute('name');
                if (!name) return;

                field.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
            });

            const title = line.querySelector('.receipt-line-title');
            if (title) {
                title.textContent = 'Item #' + (index + 1);
            }
        });

        receiptLineIndex = document.querySelectorAll('.receipt-line').length;
    }

    function getSelectedBranchId() {
        return document.getElementById('receiptBranch')?.value || '';
    }

    function getSelectedLocationId() {
        return document.getElementById('receiptLocation')?.value || '';
    }


    function isReceiptLineSerialized(line) {
        const select = line?.querySelector('.receipt-item-select');
        const selected = select ? select.options[select.selectedIndex] : null;

        return !!(selected && selected.value && String(selected.dataset.serialized || '0') === '1');
    }

    function selectedSerialCount(line) {
        const serialSelect = line.querySelector('.receipt-serial-select');
        if (!serialSelect) {
            return 0;
        }

        return Array.from(serialSelect.selectedOptions || []).filter(option => option.value).length;
    }

    function updateSerializedQuantity(line) {
        const qtyInput = line.querySelector('.receipt-qty');
        if (!qtyInput || !isReceiptLineSerialized(line)) {
            return;
        }

        const count = selectedSerialCount(line);
        qtyInput.value = count > 0 ? count : 0;
    }

    function resetReceiptSerialSelect(line) {
        const serialSelect = line.querySelector('.receipt-serial-select');
        if (!serialSelect) {
            return;
        }

        destroyReceiptSerialSelect2(line);
        serialSelect.innerHTML = '';
        serialSelect.value = '';
    }

    function syncReceiptSerializedControls(line) {
        const serialWrap = line.querySelector('.receipt-serial-wrap');
        const qtyInput = line.querySelector('.receipt-qty');

        if (!serialWrap || !qtyInput) {
            return;
        }

        if (isReceiptLineSerialized(line)) {
            serialWrap.style.display = '';
            qtyInput.readOnly = true;
            qtyInput.classList.add('bg-light');
            updateSerializedQuantity(line);

            loadSelect2Assets(function () {
                initReceiptSerialSelect2();
            });

            if (!hasSelect2()) {
                loadReceiptSerialOptions(line);
            }
        } else {
            serialWrap.style.display = 'none';
            qtyInput.readOnly = false;
            qtyInput.classList.remove('bg-light');
            resetReceiptSerialSelect(line);
        }
    }

    function loadReceiptSerialOptions(line) {
        const serialSelect = line.querySelector('.receipt-serial-select');

        if (!serialSelect || hasSelect2()) {
            return;
        }

        const itemId = line.querySelector('.receipt-item-select')?.value || '';
        const branchId = getSelectedBranchId();
        const locationId = getSelectedLocationId();

        if (!itemId || !branchId || !locationId) {
            serialSelect.innerHTML = '';
            return;
        }

        const url = receiptSerialSearchUrl
            + '?item_id=' + encodeURIComponent(itemId)
            + '&branch_id=' + encodeURIComponent(branchId)
            + '&location_id=' + encodeURIComponent(locationId);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                serialSelect.innerHTML = '';
                (data.results || []).forEach(function (serial) {
                    const option = document.createElement('option');
                    option.value = serial.id;
                    option.textContent = serial.text;
                    serialSelect.appendChild(option);
                });
            })
            .catch(() => {
                serialSelect.innerHTML = '';
            });
    }

    function getAvailableStock(itemId) {
        const branchId = parseInt(getSelectedBranchId() || 0);
        const locationId = parseInt(getSelectedLocationId() || 0);
        const selectedItemId = parseInt(itemId || 0);

        if (!branchId || !locationId || !selectedItemId) {
            return 0;
        }

        const found = receiptStockMap.find(function (row) {
            return parseInt(row.item_id) === selectedItemId
                && parseInt(row.branch_id) === branchId
                && parseInt(row.location_id) === locationId;
        });

        return found ? parseFloat(found.quantity || 0) : 0;
    }

    function updateReceiptLinePreview(line) {
        const select = line.querySelector('.receipt-item-select');
        const selected = select ? select.options[select.selectedIndex] : null;
        const qty = parseFloat(line.querySelector('.receipt-qty')?.value || 0);
        const codeBox = line.querySelector('.sales-line-code');
        const nameBox = line.querySelector('.sales-line-name');

        if (!selected || !selected.value) {
            if (codeBox) codeBox.textContent = 'Available: 0.00';
            if (nameBox) {
                nameBox.textContent = getSelectedBranchId() && getSelectedLocationId()
                    ? 'Search and select an item'
                    : 'Select source and item';
                nameBox.className = 'sales-line-name';
            }
            return;
        }

        const available = getAvailableStock(selected.value);

        if (codeBox) {
            codeBox.textContent = 'Available: ' + available.toFixed(2);
        }

        if (nameBox) {
            nameBox.textContent = (selected.dataset.code || '-') + ' - ' + (selected.dataset.name || selected.textContent.trim());

            if (isReceiptLineSerialized(line)) {
                const count = selectedSerialCount(line);
                nameBox.className = count > 0 ? 'sales-line-name text-success fw-bold' : 'sales-line-name text-danger fw-bold';
                nameBox.textContent += count > 0
                    ? ' • ' + count + ' serial(s) selected'
                    : ' • Select serial number';
            } else if (qty > available) {
                nameBox.className = 'sales-line-name text-danger fw-bold';
                nameBox.textContent += ' • Insufficient stock';
            } else {
                nameBox.className = 'sales-line-name text-success fw-bold';
                nameBox.textContent += ' • Stock OK';
            }
        }
    }

    function calculateReceiptTotals() {
        let subtotal = 0;

        document.querySelectorAll('.receipt-line').forEach(function (line) {
            const qty = parseFloat(line.querySelector('.receipt-qty')?.value || 0);
            const price = parseFloat(line.querySelector('.receipt-price')?.value || 0);
            const total = qty * price;

            subtotal += total;

            const lineTotal = line.querySelector('.receipt-line-total');
            if (lineTotal) {
                lineTotal.value = total.toFixed(2);
            }

            updateReceiptLinePreview(line);
        });

        const subtotalBox = document.getElementById('receipt-subtotal');
        const totalBox = document.getElementById('receipt-total');

        if (subtotalBox) subtotalBox.innerText = subtotal.toFixed(2);
        if (totalBox) totalBox.innerText = subtotal.toFixed(2);
    }

    function handleReceiptItemChange(select) {
        const selected = select.options[select.selectedIndex];
        const line = select.closest('.receipt-line');

        resetReceiptSerialSelect(line);

        if (selected && selected.dataset.price) {
            line.querySelector('.receipt-price').value = parseFloat(selected.dataset.price || 0).toFixed(2);
        }

        if (selected && selected.dataset.description && !line.querySelector('.receipt-description').value) {
            line.querySelector('.receipt-description').value = selected.dataset.description;
        }

        syncReceiptSerializedControls(line);
        calculateReceiptTotals();
    }

    function addReceiptLine() {
        const wrapper = document.getElementById('receipt-lines');
        const firstLine = document.querySelector('.receipt-line');

        if (!wrapper || !firstLine) {
            return;
        }

        destroyReceiptSerialSelect2(firstLine);
        destroyReceiptItemSelect2(firstLine);

        const newLine = firstLine.cloneNode(true);

        newLine.querySelectorAll('input, textarea').forEach(function (field) {
            field.classList.remove('is-invalid');

            if (field.classList.contains('receipt-qty')) {
                field.value = 1;
            } else if (field.classList.contains('receipt-price') || field.classList.contains('receipt-line-total')) {
                field.value = '0.00';
            } else {
                field.value = '';
            }
        });

        newLine.querySelectorAll('select').forEach(function (field) {
            field.classList.remove('is-invalid');
            field.selectedIndex = 0;

            if (field.classList.contains('receipt-serial-select')) {
                field.innerHTML = '';
            }
        });

        newLine.querySelectorAll('.receipt-serial-wrap').forEach(function (wrap) {
            wrap.style.display = 'none';
        });

        newLine.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
            feedback.remove();
        });

        newLine.querySelectorAll('.sales-line-code').forEach(function (box) {
            box.textContent = 'Available: 0.00';
        });

        newLine.querySelectorAll('.sales-line-name').forEach(function (box) {
            box.className = 'sales-line-name';
            box.textContent = 'Select source and item';
        });

        wrapper.appendChild(newLine);
        refreshReceiptIndexes();
        bindReceiptEvents();
        loadSelect2Assets(function () {
            initReceiptItemSelect2();
            initReceiptSerialSelect2();
        });

        calculateReceiptTotals();
    }

    function removeReceiptLine(button) {
        const lines = document.querySelectorAll('.receipt-line');

        if (lines.length <= 1) {
            showReceiptNotice('At least one sales receipt item is required.', 'warning');
            return;
        }

        const line = button.closest('.receipt-line');
        destroyReceiptSerialSelect2(line);
        destroyReceiptItemSelect2(line);
        line.remove();

        refreshReceiptIndexes();
        loadSelect2Assets(function () {
            initReceiptItemSelect2();
            initReceiptSerialSelect2();
        });

        calculateReceiptTotals();
    }

    function updateReceiptCustomerPreview() {
        const customerSelect = document.getElementById('receiptCustomer');
        const nameBox = document.getElementById('receiptCustomerPreviewName');
        const addressBox = document.getElementById('receiptCustomerPreviewAddress');

        if (!customerSelect || !nameBox || !addressBox) {
            return;
        }

        const selected = customerSelect.options[customerSelect.selectedIndex];

        if (!selected || !selected.value) {
            nameBox.textContent = 'No customer selected';
            addressBox.textContent = 'Select a customer to preview details.';
            return;
        }

        nameBox.textContent = (selected.dataset.code || '-') + ' - ' + (selected.dataset.name || '-');
        addressBox.textContent = selected.dataset.address || 'No billing address available.';
    }

    function filterReceiptLocations() {
        const branchSelect = document.getElementById('receiptBranch');
        const locationSelect = document.getElementById('receiptLocation');

        if (!branchSelect || !locationSelect) {
            return;
        }

        const branchId = branchSelect.value;
        let selectedLocationIsVisible = false;

        Array.from(locationSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const matches = !branchId || String(option.dataset.branch || '') === String(branchId);
            option.hidden = !matches;

            if (option.selected && matches) {
                selectedLocationIsVisible = true;
            }
        });

        if (!selectedLocationIsVisible && locationSelect.value) {
            locationSelect.value = '';
        }

        document.querySelectorAll('.receipt-line').forEach(function (line) {
            resetReceiptSerialSelect(line);
            syncReceiptSerializedControls(line);
        });
        updateReceiptInventoryPreview();
        calculateReceiptTotals();
    }

    function updateReceiptInventoryPreview() {
        const branchSelect = document.getElementById('receiptBranch');
        const locationSelect = document.getElementById('receiptLocation');
        const nameBox = document.getElementById('receiptInventoryPreviewName');
        const subBox = document.getElementById('receiptInventoryPreviewSub');

        if (!branchSelect || !locationSelect || !nameBox || !subBox) {
            return;
        }

        const branch = branchSelect.options[branchSelect.selectedIndex];
        const location = locationSelect.options[locationSelect.selectedIndex];

        if (!branchSelect.value || !locationSelect.value) {
            nameBox.textContent = 'No source selected';
            subBox.textContent = 'Select a branch and warehouse location.';
            return;
        }

        nameBox.textContent = location.dataset.name || location.textContent.trim();
        subBox.textContent = 'Branch: ' + (branch.dataset.name || branch.textContent.trim());
    }

    function bindReceiptEvents() {
        document.querySelectorAll('.receipt-item-select').forEach(function (select) {
            select.onchange = function () {
                handleReceiptItemChange(this);
            };
        });

        document.querySelectorAll('.receipt-qty, .receipt-price').forEach(function (input) {
            input.oninput = calculateReceiptTotals;
        });

        document.querySelectorAll('.receipt-serial-select').forEach(function (select) {
            select.onchange = function () {
                updateSerializedQuantity(this.closest('.receipt-line'));
                calculateReceiptTotals();
            };
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindReceiptEvents();

        loadSelect2Assets(function () {
            initReceiptItemSelect2();
            initReceiptSerialSelect2();
        });

        updateReceiptCustomerPreview();
        filterReceiptLocations();
        updateReceiptInventoryPreview();

        document.querySelectorAll('.receipt-line').forEach(function (line) {
            syncReceiptSerializedControls(line);
        });

        calculateReceiptTotals();

        const customerSelect = document.getElementById('receiptCustomer');
        const branchSelect = document.getElementById('receiptBranch');
        const locationSelect = document.getElementById('receiptLocation');

        if (customerSelect) {
            customerSelect.addEventListener('change', updateReceiptCustomerPreview);
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', filterReceiptLocations);
        }

        if (locationSelect) {
            locationSelect.addEventListener('change', function () {
                document.querySelectorAll('.receipt-line').forEach(function (line) {
                    resetReceiptSerialSelect(line);
                    syncReceiptSerializedControls(line);
                });
                updateReceiptInventoryPreview();
                calculateReceiptTotals();
            });
        }

        const form = document.getElementById('salesReceiptForm');

        if (form) {
            form.addEventListener('submit', function (event) {
                let hasInsufficientStock = false;

                document.querySelectorAll('.receipt-line').forEach(function (line) {
                    const itemSelect = line.querySelector('.receipt-item-select');
                    const itemId = itemSelect?.value || '';
                    const qty = parseFloat(line.querySelector('.receipt-qty')?.value || 0);
                    const available = getAvailableStock(itemId);

                    if (itemId && isReceiptLineSerialized(line) && selectedSerialCount(line) <= 0) {
                        hasInsufficientStock = true;
                    }

                    if (itemId && qty > available) {
                        hasInsufficientStock = true;
                    }
                });

                if (hasInsufficientStock) {
                    event.preventDefault();
                    showReceiptNotice('One or more items have insufficient stock or missing serial numbers for the selected branch/location.', 'warning');
                }
            });
        }
    });
</script>