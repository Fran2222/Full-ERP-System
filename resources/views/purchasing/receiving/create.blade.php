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

            abort_unless($canAccess('purchasing.receiving.post'), 403);

            $canViewReceiving = $canAccess('purchasing.receiving.view');
        @endphp

        <div class="card purchasing-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Receive Purchase Order Items</h4>
                        <p class="text-secondary mb-0">
                            Select an ordered purchase order and receive items into warehouse inventory.
                        </p>
                    </div>

                    @if($canViewReceiving)
                        <a href="{{ route('purchasing.receiving.index') }}" class="btn btn-primary purchasing-soft-btn">
                            Back
                        </a>
                    @endif
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

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('purchasing.receiving.create') }}" class="purchase-section mb-4">
                    <h6 class="fw-bold mb-1">Purchase Order Selection</h6>
                    <p class="text-secondary mb-3">Choose an ordered or partially received purchase order. You can search by PO number or supplier name.</p>

                    <div class="row g-3 align-items-end">
                        <div class="col-lg-10 purchasing-select2-wrap">
                            <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                            <select name="po_id"
                                    id="receivingPoSelect"
                                    class="form-select purchasing-input"
                                    data-placeholder="Search PO no or supplier..."
                                    required>
                                <option value="">Select Purchase Order</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}"
                                            data-po="{{ $po->po_no }}"
                                            data-supplier="{{ $po->supplier?->supplier_name }}"
                                            data-location="{{ $po->location?->location_name ?? $po->location?->name }}"
                                            data-total="{{ number_format((float) $po->total_amount, 2) }}"
                                            {{ request('po_id') == $po->id ? 'selected' : '' }}>
                                        {{ $po->po_no }} - {{ $po->supplier?->supplier_name ?? '-' }} - {{ number_format((float) $po->total_amount, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-primary w-100 purchasing-soft-btn">
                                Load PO
                            </button>
                        </div>
                    </div>
                </form>

                @if($selectedPO)
                    <form method="POST" action="{{ route('purchasing.receiving.store') }}" id="receivingPostForm">
                        @csrf

                        <input type="hidden" name="purchase_order_id" value="{{ $selectedPO->id }}">

                        <div class="row g-4 mb-4">
                            <div class="col-xl-6">
                                <div class="purchase-section h-100">
                                    <h6 class="fw-bold mb-1">Supplier Information</h6>
                                    <p class="text-secondary mb-3">Supplier and PO reference details.</p>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="purchase-info-label">PO No.</div>
                                            <div class="fw-bold text-primary">{{ $selectedPO->po_no }}</div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="purchase-info-label">Status</div>
                                            <span class="purchasing-badge purchasing-badge-info">
                                                {{ ucwords(str_replace('_', ' ', $selectedPO->status)) }}
                                            </span>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="purchase-info-label">Supplier</div>
                                            <div class="fw-semibold">{{ $selectedPO->supplier?->supplier_name ?? '-' }}</div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="purchase-info-label">Contact</div>
                                            <div class="fw-semibold">{{ $selectedPO->supplier?->contact_person ?? '-' }}</div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="purchase-info-label">Receiving Location</div>
                                            <div class="fw-semibold">
                                                {{ $selectedPO->location?->location_name ?? $selectedPO->location?->name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="purchase-section h-100">
                                    <h6 class="fw-bold mb-1">Receiving Details</h6>
                                    <p class="text-secondary mb-3">Date, reference, and remarks for this receiving.</p>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Received Date <span class="text-danger">*</span></label>
                                            <input type="date"
                                                   name="received_date"
                                                   value="{{ old('received_date', now()->format('Y-m-d')) }}"
                                                   class="form-control purchasing-input"
                                                   required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Reference No.</label>
                                            <input type="text"
                                                   name="reference_no"
                                                   value="{{ old('reference_no', $selectedPO->po_no) }}"
                                                   class="form-control purchasing-input"
                                                   placeholder="Reference no.">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks"
                                                      rows="3"
                                                      class="form-control purchasing-input"
                                                      placeholder="Receiving remarks">{{ old('remarks') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-section mb-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Items to Receive</h6>
                                    <p class="text-secondary mb-0">
                                        Enter quantity received. You may receive partial quantities, but not more than remaining quantity.
                                    </p>
                                </div>
                            </div>

                            <div class="table-responsive purchasing-table-wrap">
                                <table class="table table-hover align-middle mb-0 purchasing-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Description</th>
                                            <th class="text-end">Ordered</th>
                                            <th class="text-end">Received</th>
                                            <th class="text-end">Remaining</th>
                                            <th class="text-end">Receive Qty</th>
                                            <th class="text-end">Unit Cost</th>
                                            <th class="text-end">Line Total</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php $visibleIndex = 0; @endphp

                                        @foreach($selectedPO->items as $item)
                                            @php
                                                $ordered = (float) $item->quantity;
                                                $received = (float) $item->received_quantity;
                                                $remaining = max(0, $ordered - $received);
                                            @endphp

                                            @if($remaining > 0)
                                                <tr class="receive-line">
                                                    <td class="text-secondary">{{ ++$visibleIndex }}</td>

                                                    <td>
                                                        <input type="hidden"
                                                               name="items[{{ $loop->index }}][purchase_order_item_id]"
                                                               value="{{ $item->id }}">

                                                        <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                                        <div class="small text-secondary">{{ $item->item_code }}</div>
                                                    </td>

                                                    <td class="text-secondary">
                                                        {{ $item->description ?: '-' }}
                                                    </td>

                                                    <td class="text-end">{{ number_format($ordered, 2) }}</td>
                                                    <td class="text-end text-success fw-semibold">{{ number_format($received, 2) }}</td>
                                                    <td class="text-end text-danger fw-semibold remaining-qty">{{ number_format($remaining, 2, '.', '') }}</td>

                                                    <td style="min-width: 140px;">
                                                        <input type="number"
                                                               name="items[{{ $loop->index }}][receive_quantity]"
                                                               value="{{ old('items.' . $loop->index . '.receive_quantity', $remaining) }}"
                                                               min="0"
                                                               max="{{ $remaining }}"
                                                               step="0.01"
                                                               data-remaining="{{ $remaining }}"
                                                               class="form-control purchasing-input text-end receive-qty"
                                                               required>
                                                    </td>

                                                    <td style="min-width: 140px;">
                                                        <input type="number"
                                                               name="items[{{ $loop->index }}][unit_cost]"
                                                               value="{{ old('items.' . $loop->index . '.unit_cost', $item->unit_price) }}"
                                                               min="0"
                                                               step="0.01"
                                                               class="form-control purchasing-input text-end unit-cost"
                                                               required>
                                                    </td>

                                                    <td class="text-end fw-bold line-total">
                                                        0.00
                                                    </td>

                                                    <td style="min-width: 180px;">
                                                        <input type="text"
                                                               name="items[{{ $loop->index }}][remarks]"
                                                               value="{{ old('items.' . $loop->index . '.remarks') }}"
                                                               class="form-control purchasing-input"
                                                               placeholder="Optional">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @if($visibleIndex === 0)
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-secondary">
                                                    All items in this purchase order have already been received.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="purchase-section h-100">
                                    <h6 class="fw-bold mb-1">Receiving Reminder</h6>
                                    <p class="text-secondary mb-0">
                                        Posting this receiving will update Warehouse Inventory and create stock movement ledger records.
                                    </p>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="purchase-summary h-100">
                                    <h6 class="fw-bold mb-1">Receiving Summary</h6>
                                    <p class="text-secondary mb-3">Computed from receive quantities.</p>

                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary">Total Quantity</span>
                                        <strong id="receiveTotalQty">0.00</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-secondary">Total Cost</span>
                                        <strong id="receiveTotalCost">0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            @if($canViewReceiving)
                                <a href="{{ route('purchasing.receiving.index') }}" class="btn btn-outline-secondary purchasing-soft-btn">
                                    Cancel
                                </a>
                            @endif

                            @if($canAccess('purchasing.receiving.post'))
                                <button type="submit" class="btn btn-primary purchasing-soft-btn">
                                    Post Receiving
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="purchase-empty-state">
                        <h5 class="fw-bold mb-2">Select a Purchase Order</h5>
                        <p class="text-secondary mb-0">
                            Choose an ordered purchase order above to start receiving items.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .purchasing-panel,
        .purchase-section,
        .purchase-empty-state,
        .purchase-summary {
            background: #ffffff;
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045) !important;
        }

        .purchase-section,
        .purchase-summary {
            padding: 20px;
        }

        .purchase-empty-state {
            padding: 45px 20px;
            text-align: center;
            background: #f8fafc;
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

        .purchasing-table-wrap {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            overflow: hidden;
        }

        .purchasing-table {
            min-width: 1180px;
        }

        .purchasing-table thead th {
            background: #f4f6fb;
            color: #8a94a6;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 0;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .purchasing-table tbody td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
        }

        .purchasing-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }

        .purchasing-badge-info {
            background: #eef4ff;
            color: #315cf6;
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

        .purchasing-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
            right: 8px;
        }

        .select2-dropdown.purchasing-select2-dropdown {
            border-color: #d9dee8;
            border-radius: 12px;
            overflow: hidden;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('receivingPostForm');

            function hasPurchaseSelect2() {
                return window.jQuery && typeof jQuery.fn.select2 === 'function';
            }

            function loadPurchaseSelect2Assets(callback) {
                if (!window.jQuery) return;

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

            function initPoSelect2() {
                if (!hasPurchaseSelect2()) return;

                $('#receivingPoSelect').select2({
                    width: '100%',
                    placeholder: $('#receivingPoSelect').data('placeholder') || 'Search PO no or supplier...',
                    allowClear: true,
                    dropdownCssClass: 'purchasing-select2-dropdown',
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') return data;
                        if (typeof data.text === 'undefined') return null;

                        const term = params.term.toLowerCase();
                        const text = data.text.toLowerCase();
                        const element = data.element;
                        const po = element ? String($(element).data('po') || '').toLowerCase() : '';
                        const supplier = element ? String($(element).data('supplier') || '').toLowerCase() : '';

                        if (text.indexOf(term) > -1 || po.indexOf(term) > -1 || supplier.indexOf(term) > -1) {
                            return data;
                        }

                        return null;
                    }
                });
            }

            function formatMoney(value) {
                return Number(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function calculateReceivingTotals() {
                let totalQty = 0;
                let totalCost = 0;

                document.querySelectorAll('.receive-line').forEach(function (line) {
                    const qty = parseFloat(line.querySelector('.receive-qty')?.value || 0);
                    const cost = parseFloat(line.querySelector('.unit-cost')?.value || 0);
                    const lineTotal = qty * cost;

                    totalQty += qty;
                    totalCost += lineTotal;

                    const lineTotalBox = line.querySelector('.line-total');

                    if (lineTotalBox) {
                        lineTotalBox.textContent = formatMoney(lineTotal);
                    }
                });

                const qtyBox = document.getElementById('receiveTotalQty');
                const costBox = document.getElementById('receiveTotalCost');

                if (qtyBox) qtyBox.textContent = formatMoney(totalQty);
                if (costBox) costBox.textContent = formatMoney(totalCost);
            }

            function showNotice(message, type = 'warning') {
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

            function confirmPost() {
                if (window.Swal) {
                    return Swal.fire({
                        icon: 'question',
                        title: 'Post Receiving?',
                        text: 'This will update warehouse inventory and stock movement ledger.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, post receiving',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#315cf6',
                        reverseButtons: true
                    }).then(result => result.isConfirmed);
                }

                return Promise.resolve(confirm('Post this receiving and update warehouse inventory?'));
            }

            document.querySelectorAll('.receive-qty, .unit-cost').forEach(function (input) {
                input.addEventListener('input', calculateReceivingTotals);
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    let hasQty = false;
                    let hasOverReceive = false;

                    document.querySelectorAll('.receive-line').forEach(function (line) {
                        const qtyInput = line.querySelector('.receive-qty');
                        const qty = parseFloat(qtyInput?.value || 0);
                        const remaining = parseFloat(qtyInput?.dataset.remaining || 0);

                        if (qty > 0) {
                            hasQty = true;
                        }

                        if (qty > remaining) {
                            hasOverReceive = true;
                        }
                    });

                    if (!hasQty) {
                        showNotice('Please enter at least one quantity to receive.', 'warning');
                        return;
                    }

                    if (hasOverReceive) {
                        showNotice('Receive quantity cannot be greater than remaining quantity.', 'warning');
                        return;
                    }

                    confirmPost().then(function (confirmed) {
                        if (confirmed) {
                            form.submit();
                        }
                    });
                });
            }

            loadPurchaseSelect2Assets(function () {
                initPoSelect2();
            });

            calculateReceivingTotals();
        });
    </script>
</x-app-layout>