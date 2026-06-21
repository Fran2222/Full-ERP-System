<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Borrow / Issue Service Unit</h4>
                        <p class="text-secondary mb-0">Issue a serialized service unit to an employee without treating it as a sale or permanent stock out.</p>
                    </div>
                    <a href="{{ route('warehouse.service-units.index') }}" class="btn btn-outline-secondary">Back</a>
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

                <form method="POST" action="{{ route('warehouse.service-units.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <input type="text" id="employeeSearch" class="form-control mb-2" placeholder="Search employee name or email...">
                            <select name="employee_user_id" id="employeeSelect" class="form-select @error('employee_user_id') is-invalid @enderror" required size="7">
                                @foreach($employees as $employee)
                                    @php
                                        $employeeName = $employee->full_name ?: trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: $employee->email;
                                    @endphp
                                    <option value="{{ $employee->id }}"
                                            data-search="{{ strtolower($employeeName . ' ' . $employee->email) }}"
                                            {{ old('employee_user_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employeeName }} — {{ $employee->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Service Unit Item <span class="text-danger">*</span></label>
                            <select name="item_id" id="itemSelect" class="form-select @error('item_id') is-invalid @enderror" required>
                                <option value="">Select serialized service unit item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->code ?? $item->item_code }} - {{ $item->name ?? $item->item_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            <div class="alert alert-info mt-3 mb-0 py-2 small">
                                Only items marked as <b>Serialized item</b> and <b>Service Unit / Borrowable</b> will appear here.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" id="branchSelect" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">Auto from location</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Warehouse Location <span class="text-danger">*</span></label>
                            <select name="location_id" id="locationSelect" class="form-select @error('location_id') is-invalid @enderror" required>
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}"
                                            data-branch-id="{{ $location->branch_id }}"
                                            {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name ?? $location->name }}{{ $location->branch ? ' - ' . $location->branch->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Serial Number <span class="text-danger">*</span></label>
                            <input type="text" id="serialSearch" class="form-control mb-2" placeholder="Search available serial number...">
                            <select name="serial_id" id="serialSelect" class="form-select @error('serial_id') is-invalid @enderror" required size="5">
                                <option value="">Select item and location first</option>
                            </select>
                            <div class="form-text">Search is realtime and loads only available serial numbers from the selected location.</div>
                            @error('serial_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Borrowed Date <span class="text-danger">*</span></label>
                            <input type="date" name="borrowed_at" value="{{ old('borrowed_at', now()->toDateString()) }}" class="form-control @error('borrowed_at') is-invalid @enderror" required>
                            @error('borrowed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Expected Return Date</label>
                            <input type="date" name="expected_return_at" value="{{ old('expected_return_at') }}" class="form-control @error('expected_return_at') is-invalid @enderror">
                            @error('expected_return_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Condition Out</label>
                            <select name="condition_out" class="form-select @error('condition_out') is-invalid @enderror">
                                @foreach(['Good', 'Brand New', 'Used - Good', 'Used - Fair', 'Needs Checking'] as $condition)
                                    <option value="{{ $condition }}" {{ old('condition_out', 'Good') === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                            @error('condition_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Purpose</label>
                            <textarea name="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" placeholder="Example: Field installation, repair service, temporary assignment...">{{ old('purpose') }}</textarea>
                            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('warehouse.service-units.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Borrow Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const employeeSearch = document.getElementById('employeeSearch');
            const employeeSelect = document.getElementById('employeeSelect');
            const itemSelect = document.getElementById('itemSelect');
            const branchSelect = document.getElementById('branchSelect');
            const locationSelect = document.getElementById('locationSelect');
            const serialSearch = document.getElementById('serialSearch');
            const serialSelect = document.getElementById('serialSelect');
            let serialTimer = null;

            function filterSelect(searchInput, select) {
                const value = (searchInput.value || '').toLowerCase();
                Array.from(select.options).forEach(option => {
                    if (!option.value) return;
                    const haystack = option.dataset.search || option.text.toLowerCase();
                    option.hidden = value && !haystack.includes(value);
                });
            }

            function syncBranchFromLocation() {
                const selected = locationSelect.options[locationSelect.selectedIndex];
                if (selected && selected.dataset.branchId && !branchSelect.value) {
                    branchSelect.value = selected.dataset.branchId;
                }
            }

            function loadSerials() {
                const itemId = itemSelect.value;
                const locationId = locationSelect.value;
                const search = serialSearch.value || '';

                serialSelect.innerHTML = '<option value="">Loading serials...</option>';

                if (!itemId || !locationId) {
                    serialSelect.innerHTML = '<option value="">Select item and location first</option>';
                    return;
                }

                const url = new URL('{{ route('warehouse.service-units.serials.available') }}', window.location.origin);
                url.searchParams.set('item_id', itemId);
                url.searchParams.set('location_id', locationId);
                if (search) url.searchParams.set('search', search);

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(response => response.json())
                    .then(data => {
                        serialSelect.innerHTML = '';
                        const results = data.results || [];

                        if (!results.length) {
                            serialSelect.innerHTML = '<option value="">No available serial found</option>';
                            return;
                        }

                        results.forEach(serial => {
                            const option = document.createElement('option');
                            option.value = serial.id;
                            option.textContent = serial.text;
                            serialSelect.appendChild(option);
                        });
                    })
                    .catch(() => {
                        serialSelect.innerHTML = '<option value="">Serials could not be loaded</option>';
                    });
            }

            if (employeeSearch && employeeSelect) {
                employeeSearch.addEventListener('input', () => filterSelect(employeeSearch, employeeSelect));
            }

            if (locationSelect) {
                locationSelect.addEventListener('change', function () {
                    syncBranchFromLocation();
                    loadSerials();
                });
            }

            if (itemSelect) {
                itemSelect.addEventListener('change', loadSerials);
            }

            if (serialSearch) {
                serialSearch.addEventListener('input', function () {
                    clearTimeout(serialTimer);
                    serialTimer = setTimeout(loadSerials, 350);
                });
            }

            syncBranchFromLocation();
            if (itemSelect.value && locationSelect.value) loadSerials();
        });
    </script>

    {{-- service-units-borrow-item-picker-start --}}
    @php
        $wmcServiceUnitPickerItems = collect();
        try {
            $wmcServiceUnitPickerItems = collect($items ?? [])->map(function ($item) {
                $id = $item->id ?? null;
                $code = $item->code ?? $item->item_code ?? '';
                $name = $item->name ?? $item->item_name ?? '';
                $category = $item->category->name ?? $item->category->category_name ?? '-';
                $unit = $item->unit->name ?? $item->unit->unit_name ?? 'Pieces';
                $image = $item->image_path ?? $item->photo ?? $item->image ?? null;

                $imagePrimary = null;
                $imageFallback = null;
                $imageStorage = null;

                if ($image) {
                    $cleanImage = ltrim($image, '/');
                    if (str_starts_with($cleanImage, 'http')) {
                        $imagePrimary = $cleanImage;
                        $imageFallback = $cleanImage;
                        $imageStorage = $cleanImage;
                    } else {
                        $imagePrimary = asset($cleanImage);
                        $imageFallback = asset('storage/' . $cleanImage);
                        try {
                            $imageStorage = \Illuminate\Support\Facades\Storage::url($cleanImage);
                        } catch (\Throwable $e) {
                            $imageStorage = $imageFallback;
                        }
                    }
                }

                return [
                    'id' => (string) $id,
                    'code' => (string) $code,
                    'name' => (string) $name,
                    'category' => (string) $category,
                    'unit' => (string) $unit,
                    'image_url' => $imagePrimary,
                    'image_fallback_url' => $imageFallback,
                    'image_storage_url' => $imageStorage,
                    'search' => strtolower(trim($code . ' ' . $name . ' ' . $category . ' ' . $unit)),
                ];
            })->filter(fn ($item) => !empty($item['id']))->values();
        } catch (\Throwable $e) {
            $wmcServiceUnitPickerItems = collect();
        }
    @endphp

    <script>
        window.WMC_SERVICE_UNIT_PICKER_ITEMS = @json($wmcServiceUnitPickerItems);
    </script>

    <div id="serviceUnitItemPickerOverlay" class="su-picker-overlay" aria-hidden="true">
        <div class="su-picker-modal" role="dialog" aria-modal="true">
            <div class="su-picker-header">
                <div>
                    <h5 class="su-picker-title">Select Service Unit Item</h5>
                    <div class="su-picker-subtitle">Only serialized and service-unit/borrowable items should appear here.</div>
                </div>
                <button type="button" class="su-picker-close" id="serviceUnitPickerCloseBtn">&times;</button>
            </div>

            <div class="su-picker-toolbar">
                <div>
                    <label class="su-picker-label">Search</label>
                    <input type="text" id="serviceUnitPickerSearch" class="form-control" placeholder="Search item code, name, category, unit...">
                </div>
                <div>
                    <label class="su-picker-label">Selected Location</label>
                    <div id="serviceUnitPickerLocationText" class="su-picker-location">Select location first</div>
                </div>
            </div>

            <div class="su-picker-count-row">
                <span id="serviceUnitPickerCountText">Showing 0 item(s)</span>
                <span class="text-muted">20 items per page</span>
            </div>

            <div class="su-picker-body">
                <div class="table-responsive su-picker-table-wrap">
                    <table class="table align-middle mb-0 su-picker-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="width:150px;">Category</th>
                                <th style="width:120px;">Type</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="serviceUnitPickerRows">
                            <tr><td colspan="4" class="text-muted py-4 text-center">Loading items...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="su-picker-pagination">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="serviceUnitPickerPrev">Previous</button>
                    <span id="serviceUnitPickerPageText" class="small text-muted">Page 1 of 1</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="serviceUnitPickerNext">Next</button>
                </div>
            </div>
        </div>
    </div>

    <div id="serviceUnitImagePreviewOverlay" class="su-image-preview-overlay" aria-hidden="true">
        <div class="su-image-preview-panel">
            <button type="button" class="su-image-preview-close" id="serviceUnitImagePreviewClose">&times;</button>
            <div class="su-image-preview-header">
                <div class="su-image-preview-title">Item Photo Preview</div>
                <div class="su-image-preview-subtitle" id="serviceUnitImagePreviewTitle">Service Unit Item</div>
            </div>
            <div class="su-image-preview-body">
                <img id="serviceUnitImagePreviewImg" src="" alt="Item photo preview">
            </div>
        </div>
    </div>

    <script>
    (function () {
        var perPage = 20;
        var currentPage = 1;
        var filteredItems = [];

        function qs(s, r) { return (r || document).querySelector(s); }
        function qsa(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
        function onPage() { return location.pathname.indexOf('/warehouse/service-units/create') !== -1; }
        function esc(str) {
            return String(str || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
            });
        }

        function itemSelect() { return qs('#itemSelect'); }
        function locationSelect() { return qs('#locationSelect'); }

        function selectedLocationText() {
            var loc = locationSelect();
            if (loc && loc.value && loc.options[loc.selectedIndex]) {
                return loc.options[loc.selectedIndex].textContent.trim();
            }
            return 'Select location first';
        }

        function locationReady() {
            var loc = locationSelect();
            return !!(loc && loc.value);
        }

        function hideNativeItemSelect() {
            var sel = itemSelect();
            if (!sel) return;
            sel.style.display = 'none';
            sel.classList.add('d-none');
            if (sel.id) {
                var c = qs('#select2-' + sel.id + '-container');
                if (c) {
                    var box = c.closest('.select2,.select2-container');
                    if (box) {
                        box.style.display = 'none';
                        box.classList.add('d-none');
                    }
                }
            }
            if (sel.parentElement) {
                qsa('.select2,.select2-container', sel.parentElement).forEach(function (el) {
                    el.style.display = 'none';
                    el.classList.add('d-none');
                });
            }
        }

        function getItems() {
            return Array.isArray(window.WMC_SERVICE_UNIT_PICKER_ITEMS) ? window.WMC_SERVICE_UNIT_PICKER_ITEMS : [];
        }

        function itemById(id) {
            return getItems().find(function (item) { return String(item.id) === String(id); });
        }

        function imageHtml(item, sizeClass) {
            if (item && (item.image_url || item.image_fallback_url || item.image_storage_url)) {
                var src = item.image_url || item.image_fallback_url || item.image_storage_url || '';
                var fallback = item.image_fallback_url || item.image_storage_url || item.image_url || '';
                var storage = item.image_storage_url || item.image_fallback_url || item.image_url || '';
                return '<button type="button" class="su-thumb-btn ' + sizeClass + '" data-preview-src="' + esc(src) + '" data-preview-fallback="' + esc(fallback) + '" data-preview-storage="' + esc(storage) + '" data-preview-title="' + esc((item.code || '') + ' - ' + (item.name || '')) + '">' +
                    '<img src="' + esc(src) + '" data-fallback="' + esc(fallback) + '" data-storage="' + esc(storage) + '" alt="" onerror="window.serviceUnitThumbFallback && window.serviceUnitThumbFallback(this)">' +
                    '<span>Zoom</span>' +
                '</button>';
            }
            return '<div class="su-thumb-empty ' + sizeClass + '">No Photo</div>';
        }

        window.serviceUnitThumbFallback = window.serviceUnitThumbFallback || function (img) {
            if (!img) return;
            var current = img.getAttribute('src') || '';
            var fallback = img.getAttribute('data-fallback') || '';
            var storage = img.getAttribute('data-storage') || '';
            if (fallback && current !== fallback && img.dataset.triedFallback !== '1') {
                img.dataset.triedFallback = '1';
                img.src = fallback;
                var btn = img.closest('.su-thumb-btn');
                if (btn) btn.setAttribute('data-preview-src', fallback);
                return;
            }
            if (storage && current !== storage && img.dataset.triedStorage !== '1') {
                img.dataset.triedStorage = '1';
                img.src = storage;
                var btn2 = img.closest('.su-thumb-btn');
                if (btn2) btn2.setAttribute('data-preview-src', storage);
                return;
            }
            var box = img.closest('.su-thumb-btn');
            if (box) {
                var sizeClass = Array.from(box.classList).find(function (c) { return c.indexOf('su-thumb-') === 0 && c !== 'su-thumb-btn'; }) || '';
                box.outerHTML = '<div class="su-thumb-empty ' + sizeClass + '">No Photo</div>';
            }
        };

        function ensureVisibleBox() {
            var sel = itemSelect();
            if (!sel || sel.dataset.suPickerReady === '1') return;
            sel.dataset.suPickerReady = '1';
            hideNativeItemSelect();
            var box = document.createElement('div');
            box.id = 'serviceUnitPickerVisibleBox';
            box.className = 'su-picker-visible-box';
            box.innerHTML =
                '<div id="serviceUnitPickerSummary" class="su-picker-summary text-muted">No item selected.</div>' +
                '<button type="button" id="serviceUnitOpenPickerBtn" class="btn btn-outline-primary btn-sm mt-2 su-open-picker-btn">Search / Select Service Unit Item</button>' +
                '<div class="small text-muted mt-1">Select Location first, then choose service unit item. Serial list will load below.</div>';
            sel.parentElement.insertBefore(box, sel);

            var btn = qs('#serviceUnitOpenPickerBtn', box);
            if (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!locationReady()) {
                        alert('Please select Warehouse Location first before selecting a service unit item.');
                        var loc = locationSelect();
                        if (loc) loc.focus();
                        return;
                    }
                    openModal();
                });
            }
        }

        function applyFilter() {
            var search = (qs('#serviceUnitPickerSearch') ? qs('#serviceUnitPickerSearch').value : '').trim().toLowerCase();
            var items = getItems();
            filteredItems = items.filter(function (item) {
                var haystack = item.search || ((item.code || '') + ' ' + (item.name || '') + ' ' + (item.category || '') + ' ' + (item.unit || '')).toLowerCase();
                return !search || haystack.indexOf(search) !== -1;
            });
            currentPage = 1;
            renderRows();
        }

        function renderRows() {
            var tbody = qs('#serviceUnitPickerRows');
            var count = qs('#serviceUnitPickerCountText');
            var pageText = qs('#serviceUnitPickerPageText');
            var prev = qs('#serviceUnitPickerPrev');
            var next = qs('#serviceUnitPickerNext');
            if (!tbody) return;
            var total = filteredItems.length;
            var pages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > pages) currentPage = pages;
            if (currentPage < 1) currentPage = 1;
            var start = (currentPage - 1) * perPage;
            var pageItems = filteredItems.slice(start, start + perPage);
            if (count) count.textContent = total ? ('Showing ' + (start + 1) + '-' + (start + pageItems.length) + ' of ' + total + ' item(s)') : 'Showing 0 item(s)';
            if (pageText) pageText.textContent = 'Page ' + currentPage + ' of ' + pages;
            if (prev) prev.disabled = currentPage <= 1;
            if (next) next.disabled = currentPage >= pages;
            if (!pageItems.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted py-4 text-center">No service unit item found.</td></tr>';
                return;
            }
            tbody.innerHTML = pageItems.map(function (item) {
                return '<tr>' +
                    '<td><div class="su-picker-item-cell">' +
                        imageHtml(item, 'su-thumb-list') +
                        '<div class="min-w-0"><div class="fw-semibold text-dark">' + esc(item.code || '') + ' - ' + esc(item.name || '') + '</div>' +
                        '<div class="small text-muted">Unit: ' + esc(item.unit || '-') + '</div>' +
                        '<div class="small text-muted">Location: ' + esc(selectedLocationText()) + '</div></div>' +
                    '</div></td>' +
                    '<td>' + esc(item.category || '-') + '</td>' +
                    '<td><span class="badge bg-soft-primary text-primary">Serialized</span></td>' +
                    '<td><button type="button" class="btn btn-primary btn-sm su-pick-item-btn" data-value="' + esc(item.id) + '">Use Item</button></td>' +
                '</tr>';
            }).join('');
        }

        function openModal() {
            var overlay = qs('#serviceUnitItemPickerOverlay');
            if (!overlay) return;
            var locText = qs('#serviceUnitPickerLocationText');
            if (locText) locText.textContent = selectedLocationText();
            var search = qs('#serviceUnitPickerSearch');
            if (search) search.value = '';
            filteredItems = getItems().slice();
            currentPage = 1;
            renderRows();
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { if (search) search.focus(); }, 100);
        }

        function closeModal() {
            var overlay = qs('#serviceUnitItemPickerOverlay');
            if (!overlay) return;
            overlay.classList.remove('show');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }

        function chooseItem(id) {
            var sel = itemSelect();
            if (!sel) return;
            sel.value = id;
            if (window.jQuery) window.jQuery(sel).val(id).trigger('change');
            sel.dispatchEvent(new Event('input', { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
            var item = itemById(id) || {};
            var summary = qs('#serviceUnitPickerSummary');
            if (summary) {
                summary.innerHTML =
                    '<div class="su-selected-item-summary">' +
                        imageHtml(item, 'su-thumb-selected') +
                        '<div class="min-w-0 flex-grow-1">' +
                            '<div class="fw-semibold text-dark text-truncate">' + esc(item.code || '') + ' - ' + esc(item.name || '') + '</div>' +
                            '<div class="small text-muted text-truncate">' + esc(item.category || '-') + ' | ' + esc(item.unit || '-') + '</div>' +
                            '<div class="small text-muted text-truncate">Location: ' + esc(selectedLocationText()) + '</div>' +
                        '</div><span class="badge bg-primary">Serialized</span></div>';
            }
            closeModal();
        }

        function openImagePreview(src, title) {
            var overlay = qs('#serviceUnitImagePreviewOverlay');
            var img = qs('#serviceUnitImagePreviewImg');
            var titleEl = qs('#serviceUnitImagePreviewTitle');
            if (!overlay || !img || !src) return;
            img.src = src;
            if (titleEl) titleEl.textContent = title || 'Service Unit Item';
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function closeImagePreview() {
            var overlay = qs('#serviceUnitImagePreviewOverlay');
            if (!overlay) return;
            overlay.classList.remove('show');
            overlay.setAttribute('aria-hidden', 'true');
        }

        function bindModal() {
            var close = qs('#serviceUnitPickerCloseBtn');
            var overlay = qs('#serviceUnitItemPickerOverlay');
            var search = qs('#serviceUnitPickerSearch');
            var prev = qs('#serviceUnitPickerPrev');
            var next = qs('#serviceUnitPickerNext');
            var previewOverlay = qs('#serviceUnitImagePreviewOverlay');
            var previewClose = qs('#serviceUnitImagePreviewClose');
            if (close && close.dataset.bound !== '1') { close.dataset.bound = '1'; close.addEventListener('click', closeModal); }
            if (overlay && overlay.dataset.bound !== '1') {
                overlay.dataset.bound = '1';
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) closeModal();
                    var pickBtn = e.target.closest('.su-pick-item-btn');
                    if (pickBtn) { e.preventDefault(); chooseItem(pickBtn.getAttribute('data-value')); }
                    var thumb = e.target.closest('.su-thumb-btn');
                    if (thumb) {
                        e.preventDefault(); e.stopPropagation();
                        openImagePreview(thumb.getAttribute('data-preview-src') || thumb.getAttribute('data-preview-fallback') || thumb.getAttribute('data-preview-storage'), thumb.getAttribute('data-preview-title'));
                    }
                });
            }
            if (search && search.dataset.bound !== '1') { search.dataset.bound = '1'; search.addEventListener('input', applyFilter); }
            if (prev && prev.dataset.bound !== '1') { prev.dataset.bound = '1'; prev.addEventListener('click', function () { currentPage--; renderRows(); }); }
            if (next && next.dataset.bound !== '1') { next.dataset.bound = '1'; next.addEventListener('click', function () { currentPage++; renderRows(); }); }
            if (previewOverlay && previewOverlay.dataset.bound !== '1') { previewOverlay.dataset.bound = '1'; previewOverlay.addEventListener('click', function (e) { if (e.target === previewOverlay) closeImagePreview(); }); }
            if (previewClose && previewClose.dataset.bound !== '1') { previewClose.dataset.bound = '1'; previewClose.addEventListener('click', closeImagePreview); }
            if (document.body.dataset.serviceUnitPickerEscBound !== '1') {
                document.body.dataset.serviceUnitPickerEscBound = '1';
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeImagePreview(); closeModal(); } });
            }
        }

        function boot() {
            if (!onPage()) return;
            ensureVisibleBox();
            bindModal();
            hideNativeItemSelect();
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
        else boot();
        setTimeout(boot, 300);
        setTimeout(boot, 1000);
        setInterval(function () { if (onPage()) hideNativeItemSelect(); }, 1200);
    })();
    </script>

    <style>
        .su-picker-visible-box{border:1px solid rgba(58,87,232,.18);border-radius:10px;padding:10px;background:linear-gradient(145deg,#fbfcff,#fff);box-shadow:0 8px 18px rgba(35,45,66,.045)}
        .su-picker-summary{min-height:46px}.su-open-picker-btn{width:100%;border-color:#3a57e8!important;color:#3a57e8!important;font-weight:600;border-radius:8px}.su-open-picker-btn:hover{background:#3a57e8!important;color:#fff!important;box-shadow:0 8px 18px rgba(58,87,232,.22)}
        .su-selected-item-summary{display:flex;align-items:center;gap:10px}.su-picker-overlay,.su-image-preview-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(20,24,38,.72);backdrop-filter:blur(3px)}
        .su-picker-overlay{z-index:30000}.su-image-preview-overlay{z-index:40000;background:rgba(20,24,38,.76)}.su-picker-overlay.show,.su-image-preview-overlay.show{display:flex!important}
        .su-picker-modal{width:min(980px,96vw);max-height:88vh;overflow:hidden;background:#fff;border:1px solid rgba(58,87,232,.24);border-top:5px solid #3a57e8;border-radius:18px;box-shadow:0 30px 90px rgba(0,0,0,.34)}
        .su-picker-header,.su-image-preview-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:18px 22px;background:linear-gradient(90deg,rgba(58,87,232,.10),#fff);border-bottom:1px solid rgba(58,87,232,.14)}
        .su-picker-title{margin:0;font-weight:700;color:#232d42}.su-picker-subtitle,.su-image-preview-subtitle{font-size:13px;color:#6c757d;margin-top:3px}
        .su-picker-close,.su-image-preview-close{width:36px;height:36px;border:0;border-radius:999px;background:rgba(58,87,232,.10);color:#232d42;font-size:26px;line-height:28px;cursor:pointer}
        .su-picker-toolbar{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:14px;padding:16px 22px 8px}.su-picker-label{font-size:12px;color:#6c757d;margin-bottom:5px}
        .su-picker-location{min-height:38px;border:1px solid #e9ecef;border-radius:8px;padding:9px 12px;color:#232d42;background:#f8f9fa}.su-picker-count-row{display:flex;justify-content:space-between;gap:10px;padding:0 22px 10px;font-size:12px;color:#6c757d}
        .su-picker-body{padding:0 22px 20px}.su-picker-table-wrap{max-height:50vh;overflow:auto;border:1px solid rgba(58,87,232,.10);border-radius:12px}.su-picker-table thead th{position:sticky;top:0;z-index:1;background:#f4f6fa;color:#6c757d;font-size:12px;text-transform:uppercase;letter-spacing:.02em;border-bottom:1px solid rgba(58,87,232,.10)}
        .su-picker-table tbody tr:hover{background:rgba(58,87,232,.045)}.su-picker-item-cell{display:flex;align-items:center;gap:12px;min-width:0}.su-thumb-btn,.su-thumb-empty{flex:0 0 auto;border-radius:10px;border:1px solid rgba(58,87,232,.25);background:#fff;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
        .su-thumb-btn{cursor:pointer;padding:0}.su-thumb-btn img{width:100%;height:100%;object-fit:contain}.su-thumb-btn span{position:absolute;right:3px;bottom:3px;background:#3a57e8;color:#fff;border-radius:999px;font-size:9px;font-weight:700;padding:3px 5px}
        .su-thumb-empty{color:#8a92a6;background:#f7f8fb;font-size:10px}.su-thumb-list{width:52px;height:52px}.su-thumb-selected{width:54px;height:54px}.su-picker-pagination{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding-top:12px}.bg-soft-primary{background:rgba(58,87,232,.10)!important}
        .su-image-preview-panel{width:min(850px,96vw);max-height:90vh;background:#fff;border:1px solid rgba(58,87,232,.24);border-top:5px solid #3a57e8;border-radius:18px;box-shadow:0 30px 90px rgba(0,0,0,.36);overflow:hidden;position:relative}.su-image-preview-close{position:absolute;top:13px;right:16px;z-index:2}
        .su-image-preview-title{font-size:18px;font-weight:700;color:#232d42}.su-image-preview-body{min-height:380px;max-height:calc(90vh - 90px);overflow:auto;padding:20px;display:flex;align-items:center;justify-content:center;background:linear-gradient(45deg,rgba(58,87,232,.04) 25%,transparent 25%),linear-gradient(-45deg,rgba(58,87,232,.04) 25%,transparent 25%),linear-gradient(45deg,transparent 75%,rgba(58,87,232,.04) 75%),linear-gradient(-45deg,transparent 75%,rgba(58,87,232,.04) 75%);background-size:24px 24px;background-position:0 0,0 12px,12px -12px,-12px 0}
        .su-image-preview-body img{max-width:100%;max-height:calc(90vh - 150px);object-fit:contain;border-radius:12px;background:#fff}@media(max-width:768px){.su-picker-toolbar{grid-template-columns:1fr}}
    </style>
    {{-- service-units-borrow-item-picker-end --}}

</x-app-layout>
