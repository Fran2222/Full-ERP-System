<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

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
                            Adjust warehouse stock quantity by adding or deducting inventory.
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
                                    Select the item, branch, and location to adjust.
                                </p>

                                <div class="mb-3 warehouse-select2-wrap">
                                    <label class="form-label">Item <span class="text-danger">*</span></label>
                                    <select name="item_id"
                                            class="form-select warehouse-input warehouse-item-select"
                                            data-placeholder="Search item code or name..."
                                            required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            @php
                                                $itemCode = $item->item_code ?? $item->code;
                                                $itemName = $item->item_name ?? $item->name;
                                            @endphp

                                            <option value="{{ $item->id }}"
                                                    data-code="{{ $itemCode }}"
                                                    data-name="{{ $itemName }}"
                                                    {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                                {{ $itemCode }} - {{ $itemName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select warehouse-input" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <select name="location_id" class="form-select warehouse-input" required>
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->location_name ?? $location->name }}
                                                {{ $location->branch ? ' - ' . $location->branch->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="warehouse-section-card h-100">
                                <h5 class="fw-bold mb-1">Quantity Adjustment</h5>
                                <p class="text-secondary mb-4">
                                    Choose whether to add or deduct stock quantity.
                                </p>

                                <div class="mb-3">
                                    <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                                    <select name="adjustment_type" class="form-select warehouse-input" required>
                                        <option value="">Select Type</option>
                                        <option value="add" {{ old('adjustment_type') === 'add' ? 'selected' : '' }}>
                                            Add Stock
                                        </option>
                                        <option value="deduct" {{ old('adjustment_type') === 'deduct' ? 'selected' : '' }}>
                                            Deduct Stock
                                        </option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="quantity"
                                           value="{{ old('quantity') }}"
                                           min="0.01"
                                           step="0.01"
                                           class="form-control warehouse-input"
                                           placeholder="0.00"
                                           required>
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

        .warehouse-section-card {
            padding: 22px;
            background: #ffffff;
        }

        .warehouse-soft-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .warehouse-input {
            min-height: 44px;
            border-radius: 12px;
            border-color: #d9dee8;
        }

        .warehouse-input:focus {
            border-color: #3f5cff;
            box-shadow: 0 0 0 .18rem rgba(63, 92, 255, .12);
        }

        .warehouse-select2-wrap .select2-container {
            width: 100% !important;
        }

        .warehouse-select2-wrap .select2-container--default .select2-selection--single {
            min-height: 44px;
            border-radius: 12px;
            border-color: #d9dee8;
            display: flex;
            align-items: center;
        }

        .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827;
            line-height: 44px;
            padding-left: 14px;
            padding-right: 36px;
            font-weight: 600;
        }

        .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8a94a6;
            font-weight: 500;
        }

        .warehouse-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
            right: 8px;
        }

        .warehouse-select2-dropdown {
            border-color: #d9dee8 !important;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .warehouse-select2-dropdown .select2-search__field {
            border-radius: 10px;
            border-color: #d9dee8 !important;
            min-height: 38px;
            padding: 8px 10px;
            outline: none;
        }

        .warehouse-select2-dropdown .select2-results__option {
            padding: 10px 12px;
            font-weight: 600;
        }

        .warehouse-select2-dropdown .select2-results__option--highlighted {
            background: #315cf6 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('warehouseAdjustmentForm');

            function hasWarehouseSelect2() {
                return window.jQuery && typeof jQuery.fn.select2 === 'function';
            }

            function loadWarehouseSelect2Assets(callback) {
                if (!window.jQuery) {
                    return;
                }

                if (hasWarehouseSelect2()) {
                    callback();
                    return;
                }

                if (!document.getElementById('warehouse-select2-css')) {
                    const css = document.createElement('link');
                    css.id = 'warehouse-select2-css';
                    css.rel = 'stylesheet';
                    css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                    document.head.appendChild(css);
                }

                if (!document.getElementById('warehouse-select2-js')) {
                    const script = document.createElement('script');
                    script.id = 'warehouse-select2-js';
                    script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                    script.onload = callback;
                    document.body.appendChild(script);
                } else {
                    const waitForSelect2 = setInterval(function () {
                        if (hasWarehouseSelect2()) {
                            clearInterval(waitForSelect2);
                            callback();
                        }
                    }, 100);
                }
            }

            function initWarehouseItemSelect2() {
                if (!hasWarehouseSelect2()) {
                    return;
                }

                $('.warehouse-item-select').each(function () {
                    const $select = $(this);

                    if ($select.hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder') || 'Search item code or name...',
                        allowClear: true,
                        dropdownCssClass: 'warehouse-select2-dropdown',
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
                });
            }

            function confirmAdjustment() {
                if (window.Swal) {
                    return Swal.fire({
                        icon: 'question',
                        title: 'Post stock adjustment?',
                        text: 'This will update warehouse inventory and stock movement ledger.',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, save adjustment',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#315cf6',
                        reverseButtons: true
                    }).then(result => result.isConfirmed);
                }

                return Promise.resolve(confirm('Post this stock adjustment?'));
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    confirmAdjustment().then(function (confirmed) {
                        if (confirmed) {
                            form.submit();
                        }
                    });
                });
            }

            loadWarehouseSelect2Assets(function () {
                initWarehouseItemSelect2();
            });
        });
    </script>
</x-app-layout>