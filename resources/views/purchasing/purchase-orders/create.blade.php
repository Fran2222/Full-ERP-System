<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('purchasing._nav')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('purchasing.po.create'), 403);

            $oldItems = old('items');

            if (!$oldItems) {
                $oldItems = [
                    [
                        'item_id' => '',
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 0,
                        'tax_amount' => 0,
                    ],
                ];
            }
        @endphp

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">New Purchase Order</h4>
                        <p class="text-secondary mb-0">
                            Create supplier purchase order with item lines, terms, and expected delivery.
                        </p>
                    </div>

                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn btn-outline-secondary purchasing-soft-btn">
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <div class="fw-bold mb-2">Please fix the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('purchasing.purchase-orders.store') }}" id="purchaseOrderForm">
                    @csrf

                    <div class="row g-4 mb-4">
                        <div class="col-xl-6">
                            <div class="purchase-section h-100">
                                <h6 class="fw-bold mb-1">Supplier Information</h6>
                                <p class="text-secondary mb-3">Select vendor and supplier details.</p>

                                <div class="mb-3">
                                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id"
                                            id="supplier_id"
                                            class="form-select purchasing-input @error('supplier_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                    data-address="{{ $supplier->address }}"
                                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->supplier_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="form-label">Address</label>
                                    <textarea id="supplier_address"
                                              class="form-control purchasing-input"
                                              rows="3"
                                              readonly
                                              placeholder="Supplier address will appear here..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="purchase-section h-100">
                                <h6 class="fw-bold mb-1">Transaction Details</h6>
                                <p class="text-secondary mb-3">PO date, reference, and receiving location.</p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">PO Date <span class="text-danger">*</span></label>
                                        <input type="date"
                                               name="po_date"
                                               value="{{ old('po_date', now()->format('Y-m-d')) }}"
                                               class="form-control purchasing-input @error('po_date') is-invalid @enderror"
                                               required>
                                        @error('po_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Expected Date</label>
                                        <input type="date"
                                               name="expected_date"
                                               value="{{ old('expected_date') }}"
                                               class="form-control purchasing-input @error('expected_date') is-invalid @enderror">
                                        @error('expected_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Reference No.</label>
                                        <input type="text"
                                               name="reference_no"
                                               value="{{ old('reference_no') }}"
                                               class="form-control purchasing-input @error('reference_no') is-invalid @enderror"
                                               placeholder="Optional reference no.">
                                        @error('reference_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <select name="location_id"
                                                class="form-select purchasing-input @error('location_id') is-invalid @enderror">
                                            <option value="">Select Location</option>
                                            @foreach($locations as $location)
                                                @php
                                                    $locationName = $location->location_name ?? $location->name ?? 'Location #' . $location->id;
                                                @endphp
                                                <option value="{{ $location->id }}"
                                                        {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                    {{ $locationName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('location_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-6">
                            <div class="purchase-section h-100">
                                <h6 class="fw-bold mb-1">Shipping</h6>
                                <p class="text-secondary mb-3">Delivery or shipping method.</p>

                                <label class="form-label">Ship Via</label>
                                <input type="text"
                                       name="ship_via"
                                       value="{{ old('ship_via') }}"
                                       class="form-control purchasing-input @error('ship_via') is-invalid @enderror"
                                       placeholder="Example: Delivery / Pickup / Courier">
                                @error('ship_via')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="purchase-section h-100">
                                <h6 class="fw-bold mb-1">Terms</h6>
                                <p class="text-secondary mb-3">Payment terms for this supplier order.</p>

                                <label class="form-label">Payment Terms</label>
                                <select name="payment_terms"
                                        class="form-select purchasing-input @error('payment_terms') is-invalid @enderror">
                                    <option value="">Select Terms</option>
                                    @foreach(['Due on receipt', 'Net 7', 'Net 15', 'Net 30', 'Net 60'] as $term)
                                        <option value="{{ $term }}" {{ old('payment_terms') == $term ? 'selected' : '' }}>
                                            {{ $term }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="purchase-section mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">Products & Services</h6>
                                <p class="text-secondary mb-0">
                                    Add warehouse items to this purchase order. You can search by item code or item name.
                                </p>
                            </div>

                            <button type="button" class="btn btn-outline-primary purchasing-soft-btn" id="addLineBtn">
                                Add Line
                            </button>
                        </div>

                        <div id="poItems">
                            @foreach($oldItems as $index => $line)
                                <div class="po-line">
                                    <div class="po-line-header">
                                        <div>
                                            <div class="purchase-info-label">Line Item</div>
                                            <div class="fw-bold text-dark po-line-title">Item #{{ $loop->iteration }}</div>
                                        </div>

                                        <button type="button" class="btn btn-outline-danger purchasing-soft-btn remove-line-btn">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-xl-4 col-lg-6 purchasing-select2-wrap">
                                            <label class="form-label">Item <span class="text-danger">*</span></label>
                                            <select name="items[{{ $index }}][item_id]"
                                                    class="form-select purchasing-input item-select @error('items.' . $index . '.item_id') is-invalid @enderror"
                                                    data-placeholder="Search item code or name..."
                                                    required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                    @php
                                                        $itemCode = $item->item_code ?? $item->code;
                                                        $itemName = $item->item_name ?? $item->name;
                                                        $unitName = $item->unit?->name ?? $item->unit?->abbreviation;
                                                        $costPrice = $item->cost_price ?? 0;
                                                    @endphp

                                                    <option value="{{ $item->id }}"
                                                            data-code="{{ $itemCode }}"
                                                            data-name="{{ $itemName }}"
                                                            data-description="{{ $item->description }}"
                                                            data-unit="{{ $unitName }}"
                                                            data-cost="{{ $costPrice }}"
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
                                                   name="items[{{ $index }}][quantity]"
                                                   class="form-control purchasing-input qty-input @error('items.' . $index . '.quantity') is-invalid @enderror"
                                                   value="{{ $line['quantity'] ?? 1 }}"
                                                   min="0.01"
                                                   step="0.01"
                                                   required>
                                            @error('items.' . $index . '.quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-4">
                                            <label class="form-label">U/M</label>
                                            <input type="text" class="form-control purchasing-input unit-input" readonly placeholder="-">
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-4">
                                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   name="items[{{ $index }}][unit_price]"
                                                   class="form-control purchasing-input price-input @error('items.' . $index . '.unit_price') is-invalid @enderror"
                                                   value="{{ $line['unit_price'] ?? 0 }}"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                            @error('items.' . $index . '.unit_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-4">
                                            <label class="form-label">Amount</label>
                                            <input type="text"
                                                   class="form-control purchasing-input line-total-input fw-bold text-primary"
                                                   value="0.00"
                                                   readonly>
                                        </div>

                                        <div class="col-xl-10 col-lg-9">
                                            <label class="form-label">Description</label>
                                            <textarea name="items[{{ $index }}][description]"
                                                      class="form-control purchasing-input description-input"
                                                      rows="2"
                                                      placeholder="Optional item description">{{ $line['description'] ?? '' }}</textarea>
                                        </div>

                                        <div class="col-xl-2 col-lg-3">
                                            <label class="form-label">Tax</label>
                                            <input type="number"
                                                   name="items[{{ $index }}][tax_amount]"
                                                   class="form-control purchasing-input tax-input @error('items.' . $index . '.tax_amount') is-invalid @enderror"
                                                   value="{{ $line['tax_amount'] ?? 0 }}"
                                                   min="0"
                                                   step="0.01">
                                            @error('items.' . $index . '.tax_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="purchase-item-preview">
                                                <div class="purchase-preview-code">-</div>
                                                <div class="purchase-preview-name">Search and select an item.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="purchase-section h-100">
                                <label class="form-label">Notes</label>
                                <textarea name="notes"
                                          class="form-control purchasing-input"
                                          rows="7"
                                          placeholder="Enter purchase order notes">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="purchase-summary h-100">
                                <h6 class="fw-bold mb-1">Purchase Summary</h6>
                                <p class="text-secondary mb-3">Computed from purchase order item lines.</p>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-secondary">Subtotal</span>
                                    <strong id="subtotalText">0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-secondary">Input Tax</span>
                                    <strong id="taxText">0.00</strong>
                                </div>

                                <hr>

                                <div class="purchase-total-main">
                                    <span>Total</span>
                                    <strong id="totalText">0.00</strong>
                                </div>

                                <div class="purchase-total-note mt-3">
                                    Purchase orders do not add stock yet. Stock quantity updates when receiving/stock-in is processed.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('purchasing.purchase-orders.index') }}" class="btn btn-outline-secondary purchasing-soft-btn">
                            Cancel
                        </a>

                        @if($canAccess('purchasing.po.create'))
                            <button type="submit" class="btn btn-primary purchasing-soft-btn">
                                Save Purchase Order
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

    </div>

    <style>
        .purchasing-panel,
        .purchase-section,
        .purchase-summary {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045) !important;
        }

        .purchasing-panel {
            overflow: hidden;
        }

        .purchase-section {
            padding: 22px;
        }

        .purchase-summary {
            padding: 22px;
            background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
        }

        .purchasing-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .purchasing-input {
            min-height: 44px;
            border-radius: 12px;
            border-color: #d9dee8;
        }

        .purchasing-input:focus {
            border-color: #3f5cff;
            box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12);
        }

        .purchase-info-label {
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 6px;
        }

        .po-line {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 14px;
            background: linear-gradient(180deg, #fbfcff 0%, #ffffff 100%);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.03);
        }

        .po-line-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px dashed #edf0f5;
        }

        .purchase-item-preview {
            min-height: 44px;
            border: 1px solid #edf0f5;
            border-radius: 12px;
            background: #f8faff;
            padding: 10px 12px;
        }

        .purchase-preview-code {
            color: #111827;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
        }

        .purchase-preview-name {
            color: #8a94a6;
            font-size: 12px;
            font-weight: 600;
            margin-top: 2px;
            line-height: 1.35;
        }

        .purchase-total-main {
            background: #eef4ff;
            color: #315cf6;
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: 900;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
        }

        .purchase-total-main strong {
            color: #315cf6;
        }

        .purchase-total-note {
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 13px;
            font-weight: 600;
        }

        .purchasing-select2-wrap .select2-container {
            width: 100% !important;
        }

        .purchasing-select2-wrap .select2-container--default .select2-selection--single {
            min-height: 44px;
            border-radius: 12px;
            border-color: #d9dee8;
            display: flex;
            align-items: center;
        }

        .purchasing-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827;
            line-height: 44px;
            padding-left: 14px;
            padding-right: 36px;
            font-weight: 600;
        }

        .purchasing-select2-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8a94a6;
            font-weight: 500;
        }

        .purchasing-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
            right: 8px;
        }

        .select2-dropdown.purchasing-select2-dropdown {
            border-color: #d9dee8;
            border-radius: 12px;
            overflow: hidden;
        }

        .select2-dropdown.purchasing-select2-dropdown .select2-search__field {
            border-radius: 10px;
            border-color: #d9dee8 !important;
            min-height: 38px;
            padding: 8px 10px;
            outline: none;
        }

        .select2-dropdown.purchasing-select2-dropdown .select2-results__option {
            padding: 10px 12px;
            font-weight: 600;
        }

        .select2-dropdown.purchasing-select2-dropdown .select2-results__option--highlighted {
            background: #315cf6 !important;
        }

        @media (max-width: 768px) {
            .purchase-section,
            .purchase-summary {
                padding: 18px;
            }

            .po-line-header {
                flex-direction: column;
            }

            .remove-line-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const poItems = document.getElementById('poItems');
            const addLineBtn = document.getElementById('addLineBtn');

            let lineIndex = {{ count($oldItems) }};

            function hasPurchaseSelect2() {
                return window.jQuery && typeof jQuery.fn.select2 === 'function';
            }

            function loadPurchaseSelect2Assets(callback) {
                if (!window.jQuery) {
                    return;
                }

                if (hasPurchaseSelect2()) {
                    callback();
                    return;
                }

                if (!document.getElementById('purchasing-select2-css')) {
                    const css = document.createElement('link');
                    css.id = 'purchasing-select2-css';
                    css.rel = 'stylesheet';
                    css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                    document.head.appendChild(css);
                }

                if (!document.getElementById('purchasing-select2-js')) {
                    const script = document.createElement('script');
                    script.id = 'purchasing-select2-js';
                    script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                    script.onload = callback;
                    document.body.appendChild(script);
                } else {
                    const waitForSelect2 = setInterval(function () {
                        if (hasPurchaseSelect2()) {
                            clearInterval(waitForSelect2);
                            callback();
                        }
                    }, 100);
                }
            }

            function initPurchaseItemSelect2() {
                if (!hasPurchaseSelect2()) {
                    return;
                }

                $('.item-select').each(function () {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder') || 'Search item code or name...',
                        allowClear: true,
                        dropdownCssClass: 'purchasing-select2-dropdown',
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
                        handleItemChange(this);
                    });
                });
            }

            function destroyPurchaseItemSelect2(context) {
                if (!hasPurchaseSelect2()) {
                    return;
                }

                $(context).find('.item-select').each(function () {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                });

                $(context).find('.select2-container').remove();
            }

            function formatMoney(value) {
                return Number(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function showPurchaseNotice(message, type = 'warning') {
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

            function updateSupplierAddress() {
                const supplierSelect = document.getElementById('supplier_id');
                const addressBox = document.getElementById('supplier_address');

                if (!supplierSelect || !addressBox) {
                    return;
                }

                const selected = supplierSelect.options[supplierSelect.selectedIndex];
                addressBox.value = selected?.dataset?.address || '';
            }

            function updateLinePreview(line) {
                const itemSelect = line.querySelector('.item-select');
                const selected = itemSelect ? itemSelect.options[itemSelect.selectedIndex] : null;
                const codeBox = line.querySelector('.purchase-preview-code');
                const nameBox = line.querySelector('.purchase-preview-name');

                if (!selected || !selected.value) {
                    if (codeBox) codeBox.textContent = '-';
                    if (nameBox) {
                        nameBox.className = 'purchase-preview-name';
                        nameBox.textContent = 'Search and select an item.';
                    }

                    return;
                }

                if (codeBox) {
                    codeBox.textContent = selected.dataset.code || '-';
                }

                if (nameBox) {
                    nameBox.className = 'purchase-preview-name text-success fw-bold';
                    nameBox.textContent = selected.dataset.name || selected.textContent.trim();
                }
            }

            function calculateTotals() {
                let subtotal = 0;
                let taxTotal = 0;

                document.querySelectorAll('.po-line').forEach(line => {
                    const qty = parseFloat(line.querySelector('.qty-input')?.value || 0);
                    const price = parseFloat(line.querySelector('.price-input')?.value || 0);
                    const tax = parseFloat(line.querySelector('.tax-input')?.value || 0);

                    const lineSubtotal = qty * price;
                    const lineTotal = lineSubtotal + tax;

                    subtotal += lineSubtotal;
                    taxTotal += tax;

                    const totalInput = line.querySelector('.line-total-input');

                    if (totalInput) {
                        totalInput.value = formatMoney(lineTotal);
                    }

                    updateLinePreview(line);
                });

                document.getElementById('subtotalText').textContent = formatMoney(subtotal);
                document.getElementById('taxText').textContent = formatMoney(taxTotal);
                document.getElementById('totalText').textContent = formatMoney(subtotal + taxTotal);
            }

            function handleItemChange(select) {
                const selected = select.options[select.selectedIndex];
                const line = select.closest('.po-line');

                if (!line) {
                    return;
                }

                line.querySelector('.unit-input').value = selected?.dataset?.unit || '-';
                line.querySelector('.description-input').value = selected?.dataset?.description || '';
                line.querySelector('.price-input').value = selected?.dataset?.cost || 0;

                calculateTotals();
            }

            function refreshLineIndexes() {
                document.querySelectorAll('.po-line').forEach(function (line, index) {
                    line.querySelectorAll('select, input, textarea').forEach(function (field) {
                        const name = field.getAttribute('name');

                        if (!name) {
                            return;
                        }

                        field.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
                    });

                    const title = line.querySelector('.po-line-title');

                    if (title) {
                        title.textContent = 'Item #' + (index + 1);
                    }
                });

                lineIndex = document.querySelectorAll('.po-line').length;
            }

            function bindLineEvents(line) {
                const itemSelect = line.querySelector('.item-select');

                if (itemSelect) {
                    itemSelect.onchange = function () {
                        handleItemChange(this);
                    };
                }

                line.querySelectorAll('.qty-input, .price-input, .tax-input').forEach(input => {
                    input.oninput = calculateTotals;
                });

                const removeBtn = line.querySelector('.remove-line-btn');

                if (removeBtn) {
                    removeBtn.onclick = function () {
                        if (document.querySelectorAll('.po-line').length <= 1) {
                            showPurchaseNotice('At least one item line is required.', 'warning');
                            return;
                        }

                        const targetLine = this.closest('.po-line');
                        destroyPurchaseItemSelect2(targetLine);
                        targetLine.remove();

                        refreshLineIndexes();

                        loadPurchaseSelect2Assets(function () {
                            initPurchaseItemSelect2();
                        });

                        calculateTotals();
                    };
                }
            }

            addLineBtn.addEventListener('click', function () {
                const firstLine = document.querySelector('.po-line');

                if (!firstLine) {
                    return;
                }

                destroyPurchaseItemSelect2(firstLine);

                const clone = firstLine.cloneNode(true);

                clone.querySelectorAll('select, input, textarea').forEach(input => {
                    input.classList.remove('is-invalid');

                    if (input.name) {
                        input.name = input.name.replace(/items\[\d+\]/, `items[${lineIndex}]`);
                    }

                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.classList.contains('qty-input')) {
                        input.value = 1;
                    } else if (input.classList.contains('price-input') || input.classList.contains('tax-input')) {
                        input.value = 0;
                    } else if (input.classList.contains('line-total-input')) {
                        input.value = '0.00';
                    } else if (!input.readOnly) {
                        input.value = '';
                    } else {
                        input.value = '';
                    }
                });

                clone.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
                    feedback.remove();
                });

                clone.querySelectorAll('.purchase-preview-code').forEach(function (box) {
                    box.textContent = '-';
                });

                clone.querySelectorAll('.purchase-preview-name').forEach(function (box) {
                    box.className = 'purchase-preview-name';
                    box.textContent = 'Search and select an item.';
                });

                poItems.appendChild(clone);

                refreshLineIndexes();
                bindLineEvents(clone);

                loadPurchaseSelect2Assets(function () {
                    initPurchaseItemSelect2();
                });

                calculateTotals();
            });

            const supplierSelect = document.getElementById('supplier_id');

            if (supplierSelect) {
                supplierSelect.addEventListener('change', updateSupplierAddress);
            }

            document.querySelectorAll('.po-line').forEach(bindLineEvents);

            loadPurchaseSelect2Assets(function () {
                initPurchaseItemSelect2();
            });

            updateSupplierAddress();
            calculateTotals();
        });
    </script>
</x-app-layout>