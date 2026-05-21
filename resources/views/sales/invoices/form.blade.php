@php
    $oldItems = old('items');

    if (!$oldItems && isset($invoice)) {
        $oldItems = $invoice->items->map(function ($line) {
            return [
                'item_id' => $line->item_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
            ];
        })->toArray();
    }

    if (!$oldItems) {
        $oldItems = [
            ['item_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0],
        ];
    }

    $selectedCustomerId = old('customer_id', $invoice->customer_id ?? '');
    $selectedPaymentTerms = old('payment_terms', $invoice->payment_terms ?? 'Due on receipt');
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
</style>

<div class="sales-form-section mb-4">
    <div class="sales-section-heading">
        <div>
            <h5 class="fw-bold mb-1">Invoice Information</h5>
            <p class="text-secondary mb-0">Select customer, invoice date, due date, and payment terms.</p>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-6 col-lg-6">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            <select name="customer_id"
                    id="invoiceCustomer"
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
            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
            <input type="date"
                   name="invoice_date"
                   id="invoiceDate"
                   value="{{ old('invoice_date', isset($invoice) ? optional($invoice->invoice_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="form-control sales-input @error('invoice_date') is-invalid @enderror">
            @error('invoice_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6">
            <label class="form-label">Due Date</label>
            <input type="date"
                   name="due_date"
                   id="invoiceDueDate"
                   value="{{ old('due_date', isset($invoice) ? optional($invoice->due_date)->format('Y-m-d') : '') }}"
                   class="form-control sales-input @error('due_date') is-invalid @enderror">
            @error('due_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Reference No.</label>
            <input type="text"
                   name="reference_no"
                   value="{{ old('reference_no', $invoice->reference_no ?? '') }}"
                   class="form-control sales-input @error('reference_no') is-invalid @enderror"
                   placeholder="Optional reference no.">
            @error('reference_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <label class="form-label">Payment Terms</label>
            <select name="payment_terms"
                    id="invoicePaymentTerms"
                    class="form-select sales-input @error('payment_terms') is-invalid @enderror">
                @foreach(['Due on receipt', 'Net 7', 'Net 15', 'Net 30', 'Net 60'] as $term)
                    <option value="{{ $term }}" {{ $selectedPaymentTerms === $term ? 'selected' : '' }}>
                        {{ $term }}
                    </option>
                @endforeach
            </select>
            @error('payment_terms')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-xl-4 col-lg-4">
            <div class="sales-customer-preview" id="invoiceCustomerPreview">
                <div class="sales-info-label">Selected Customer</div>
                <div class="sales-preview-name" id="invoiceCustomerPreviewName">No customer selected</div>
                <div class="sales-preview-sub" id="invoiceCustomerPreviewAddress">Select a customer to preview details.</div>
            </div>
        </div>
    </div>
</div>

<div class="sales-form-section mb-4">
    <div class="sales-section-heading">
        <div>
            <h5 class="fw-bold mb-1">Invoice Items</h5>
            <p class="text-secondary mb-0">Add warehouse items to this invoice. You can search by item code or item name.</p>
        </div>

        <button type="button" class="btn btn-outline-primary sales-soft-btn" onclick="addInvoiceLine()">
            Add Line
        </button>
    </div>

    <div id="invoice-lines" class="mt-3">
        @foreach($oldItems as $index => $line)
            <div class="invoice-line-card invoice-line">
                <div class="invoice-line-top">
                    <div>
                        <div class="sales-info-label">Line Item</div>
                        <div class="fw-bold text-dark invoice-line-title">Item #{{ $loop->iteration }}</div>
                    </div>

                    <button type="button"
                            class="btn btn-outline-danger sales-soft-btn invoice-remove-btn"
                            onclick="removeInvoiceLine(this)">
                        Remove
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4 col-lg-6 sales-select2-wrap">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="items[{{ $index }}][item_id]"
                                class="form-select sales-input invoice-item-select @error('items.' . $index . '.item_id') is-invalid @enderror"
                                data-placeholder="Search item code or name...">
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                @php
                                    $itemCode = $item->code ?: $item->item_code;
                                    $itemName = $item->name ?: $item->item_name;
                                    $price = $item->selling_price ?? 0;
                                @endphp
                                <option value="{{ $item->id }}"
                                        data-price="{{ $price }}"
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
                               class="form-control sales-input invoice-qty @error('items.' . $index . '.quantity') is-invalid @enderror">
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
                               class="form-control sales-input invoice-price @error('items.' . $index . '.unit_price') is-invalid @enderror">
                        @error('items.' . $index . '.unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Line Total</label>
                        <input type="text"
                               class="form-control sales-input invoice-line-total fw-bold text-primary"
                               readonly
                               value="0.00">
                    </div>

                    <div class="col-xl-2 col-lg-9">
                        <label class="form-label">Item Preview</label>
                        <div class="sales-line-preview">
                            <div class="sales-line-code">-</div>
                            <div class="sales-line-name">Search and select an item</div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="items[{{ $index }}][description]"
                                  rows="2"
                                  class="form-control sales-input invoice-description"
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
                    <p class="text-secondary mb-0">Optional remarks or invoice instructions.</p>
                </div>
            </div>

            <textarea name="notes"
                      rows="7"
                      class="form-control sales-input @error('notes') is-invalid @enderror"
                      placeholder="Enter invoice notes">{{ old('notes', $invoice->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-lg-5">
        <div class="sales-total-card h-100">
            <div class="sales-section-heading mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Invoice Summary</h5>
                    <p class="text-secondary mb-0">Computed from invoice line items.</p>
                </div>
            </div>

            <div class="sales-total-row">
                <span>Subtotal</span>
                <strong id="invoice-subtotal">0.00</strong>
            </div>

            <hr>

            <div class="sales-total-row sales-total-main">
                <span>Total</span>
                <strong id="invoice-total">0.00</strong>
            </div>

            <div class="sales-total-note mt-3">
                This invoice will create receivables only. Stock deduction happens in Sales Receipts.
            </div>
        </div>
    </div>
</div>

<script>
    let invoiceLineIndex = {{ count($oldItems) }};

    function hasInvoiceSelect2() {
        return window.jQuery && typeof jQuery.fn.select2 === 'function';
    }

    function loadInvoiceSelect2Assets(callback) {
        if (!window.jQuery) {
            return;
        }

        if (hasInvoiceSelect2()) {
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
                if (hasInvoiceSelect2()) {
                    clearInterval(waitForSelect2);
                    callback();
                }
            }, 100);
        }
    }

    function initInvoiceItemSelect2() {
        if (!hasInvoiceSelect2()) {
            return;
        }

        $('.invoice-item-select').each(function () {
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
                handleInvoiceItemChange(this);
            });
        });
    }

    function destroyInvoiceItemSelect2(context) {
        if (!hasInvoiceSelect2()) {
            return;
        }

        $(context).find('.invoice-item-select').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        });

        $(context).find('.select2-container').remove();
    }

    function showInvoiceNotice(message, type = 'warning') {
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

    function refreshInvoiceIndexes() {
        document.querySelectorAll('.invoice-line').forEach(function (line, index) {
            line.querySelectorAll('select, input, textarea').forEach(function (field) {
                const name = field.getAttribute('name');
                if (!name) return;

                field.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
            });

            const title = line.querySelector('.invoice-line-title');
            if (title) {
                title.textContent = 'Item #' + (index + 1);
            }
        });

        invoiceLineIndex = document.querySelectorAll('.invoice-line').length;
    }

    function updateLinePreview(line) {
        const select = line.querySelector('.invoice-item-select');
        const selected = select ? select.options[select.selectedIndex] : null;
        const codeBox = line.querySelector('.sales-line-code');
        const nameBox = line.querySelector('.sales-line-name');

        if (!selected || !selected.value) {
            if (codeBox) codeBox.textContent = '-';
            if (nameBox) {
                nameBox.className = 'sales-line-name';
                nameBox.textContent = 'Search and select an item';
            }
            return;
        }

        if (codeBox) codeBox.textContent = selected.dataset.code || '-';

        if (nameBox) {
            nameBox.className = 'sales-line-name text-success fw-bold';
            nameBox.textContent = selected.dataset.name || selected.textContent.trim();
        }
    }

    function calculateInvoiceTotals() {
        let subtotal = 0;

        document.querySelectorAll('.invoice-line').forEach(function (line) {
            const qty = parseFloat(line.querySelector('.invoice-qty')?.value || 0);
            const price = parseFloat(line.querySelector('.invoice-price')?.value || 0);
            const total = qty * price;

            subtotal += total;

            const lineTotal = line.querySelector('.invoice-line-total');
            if (lineTotal) {
                lineTotal.value = total.toFixed(2);
            }

            updateLinePreview(line);
        });

        const subtotalBox = document.getElementById('invoice-subtotal');
        const totalBox = document.getElementById('invoice-total');

        if (subtotalBox) subtotalBox.innerText = subtotal.toFixed(2);
        if (totalBox) totalBox.innerText = subtotal.toFixed(2);
    }

    function handleInvoiceItemChange(select) {
        const selected = select.options[select.selectedIndex];
        const line = select.closest('.invoice-line');

        if (selected && selected.dataset.price) {
            line.querySelector('.invoice-price').value = parseFloat(selected.dataset.price || 0).toFixed(2);
        }

        if (selected && selected.dataset.description && !line.querySelector('.invoice-description').value) {
            line.querySelector('.invoice-description').value = selected.dataset.description;
        }

        calculateInvoiceTotals();
    }

    function addInvoiceLine() {
        const wrapper = document.getElementById('invoice-lines');
        const firstLine = document.querySelector('.invoice-line');

        if (!wrapper || !firstLine) {
            return;
        }

        destroyInvoiceItemSelect2(firstLine);

        const newLine = firstLine.cloneNode(true);

        newLine.querySelectorAll('input, textarea').forEach(function (field) {
            field.classList.remove('is-invalid');

            if (field.classList.contains('invoice-qty')) {
                field.value = 1;
            } else if (field.classList.contains('invoice-price') || field.classList.contains('invoice-line-total')) {
                field.value = '0.00';
            } else {
                field.value = '';
            }
        });

        newLine.querySelectorAll('select').forEach(function (field) {
            field.classList.remove('is-invalid');
            field.selectedIndex = 0;
        });

        newLine.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
            feedback.remove();
        });

        newLine.querySelectorAll('.sales-line-code').forEach(function (box) {
            box.textContent = '-';
        });

        newLine.querySelectorAll('.sales-line-name').forEach(function (box) {
            box.className = 'sales-line-name';
            box.textContent = 'Search and select an item';
        });

        wrapper.appendChild(newLine);
        refreshInvoiceIndexes();
        bindInvoiceEvents();

        loadInvoiceSelect2Assets(function () {
            initInvoiceItemSelect2();
        });

        calculateInvoiceTotals();
    }

    function removeInvoiceLine(button) {
        const lines = document.querySelectorAll('.invoice-line');

        if (lines.length <= 1) {
            showInvoiceNotice('At least one invoice item is required.', 'warning');
            return;
        }

        const line = button.closest('.invoice-line');
        destroyInvoiceItemSelect2(line);
        line.remove();

        refreshInvoiceIndexes();

        loadInvoiceSelect2Assets(function () {
            initInvoiceItemSelect2();
        });

        calculateInvoiceTotals();
    }

    function bindInvoiceEvents() {
        document.querySelectorAll('.invoice-item-select').forEach(function (select) {
            select.onchange = function () {
                handleInvoiceItemChange(this);
            };
        });

        document.querySelectorAll('.invoice-qty, .invoice-price').forEach(function (input) {
            input.oninput = calculateInvoiceTotals;
        });
    }

    function updateCustomerPreview() {
        const customerSelect = document.getElementById('invoiceCustomer');
        const nameBox = document.getElementById('invoiceCustomerPreviewName');
        const addressBox = document.getElementById('invoiceCustomerPreviewAddress');

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

    function addDaysToDate(dateValue, days) {
        if (!dateValue) {
            return '';
        }

        const date = new Date(dateValue + 'T00:00:00');
        date.setDate(date.getDate() + days);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    }

    function syncDueDateFromTerms(force = false) {
        const invoiceDate = document.getElementById('invoiceDate');
        const dueDate = document.getElementById('invoiceDueDate');
        const paymentTerms = document.getElementById('invoicePaymentTerms');

        if (!invoiceDate || !dueDate || !paymentTerms) {
            return;
        }

        if (!force && dueDate.value) {
            return;
        }

        const terms = paymentTerms.value;
        let days = 0;

        if (terms === 'Net 7') days = 7;
        if (terms === 'Net 15') days = 15;
        if (terms === 'Net 30') days = 30;
        if (terms === 'Net 60') days = 60;

        dueDate.value = addDaysToDate(invoiceDate.value, days);
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindInvoiceEvents();

        loadInvoiceSelect2Assets(function () {
            initInvoiceItemSelect2();
        });

        calculateInvoiceTotals();
        updateCustomerPreview();

        const customerSelect = document.getElementById('invoiceCustomer');
        const invoiceDate = document.getElementById('invoiceDate');
        const paymentTerms = document.getElementById('invoicePaymentTerms');

        if (customerSelect) {
            customerSelect.addEventListener('change', updateCustomerPreview);
        }

        if (invoiceDate) {
            invoiceDate.addEventListener('change', function () {
                syncDueDateFromTerms(true);
            });
        }

        if (paymentTerms) {
            paymentTerms.addEventListener('change', function () {
                syncDueDateFromTerms(true);
            });
        }

        syncDueDateFromTerms(false);
    });
</script>