<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">New Stock Transfer</h4>
                        <p class="text-secondary mb-0">Create a draft transfer order. Inventory will move only when dispatched and received.</p>
                    </div>
                    <a href="{{ route('warehouse.transfer') }}" class="btn btn-outline-secondary">Back To Transfers</a>
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

                <form method="POST" action="{{ route('warehouse.transfer.store') }}" id="transferCreateForm">
                    @csrf

                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <div class="border rounded-4 p-3 h-100">
                                <h5 class="fw-bold mb-3">From Location</h5>

                                <label class="form-label">From Branch <span class="text-secondary small">(optional)</span></label>
                                <select name="from_branch_id" id="from_branch_id" class="form-select mb-3">
                                    <option value="">Central / Unassigned Warehouse</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : ''}}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="form-label">From Location <span class="text-danger">*</span></label>
                                <select name="from_location_id" id="from_location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('from_location_id') == $location->id ? 'selected' : ''}}>
                                            {{ $location->location_name ?? $location->name }}
                                            {{ $location->branch ? ' - ' . $location->branch->name : ' - Central / Unassigned' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded-4 p-3 h-100">
                                <h5 class="fw-bold mb-3">To Location</h5>

                                <label class="form-label">To Branch <span class="text-secondary small">(optional)</span></label>
                                <select name="to_branch_id" id="to_branch_id" class="form-select mb-3">
                                    <option value="">Central / Unassigned Warehouse</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{old('to_branch_id') == $branch->id ? 'selected' : ''}}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="form-label">To Location <span class="text-danger">*</span></label>
                                <select name="to_location_id" id="to_location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->location_name ?? $location->name }}
                                            {{ $location->branch ? ' - ' . $location->branch->name : ' - Central / Unassigned' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-4 p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Transfer Items</h5>
                                <p class="text-secondary mb-0">Add multiple items. Serialized items require selected serial numbers.</p>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="addTransferLine">Add Line</button>
                        </div>

                        <div id="transferLines"></div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-lg-4">
                            <label class="form-label">Transfer Date</label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" class="form-control">
                        </div>
                        <div class="col-lg-8">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control" placeholder="Optional transfer remarks">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('warehouse.transfer') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Draft Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $transferItemsForJson = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code ?: $item->item_code,
                'name' => $item->name ?: $item->item_name,
                'serialized' => (bool) $item->is_serialized,
            ];
        })->values()->toArray();
    @endphp

    @push('scripts')
        <script>
            const transferItems = @json($transferItemsForJson);
            let lineIndex = 0;

            function itemOptions() {
                let html = '<option value="">Search item code or name...</option>';

                transferItems.forEach(item => {
                    html += `<option value="${item.id}" data-serialized="${item.serialized ? 1 : 0}">${item.code} - ${item.name}${item.serialized ? ' - Serialized' : ''}</option>`;
                });

                return html;
            }

            function addLine() {
                const index = lineIndex++;

                const html = `
                    <div class="transfer-line border rounded-4 p-3 mb-3" data-line-index="${index}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Item Line</strong>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-line">Remove</button>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-lg-5">
                                <label class="form-label">Item <span class="text-danger">*</span></label>
                                <select name="items[${index}][item_id]" class="form-select transfer-item-select" required>${itemOptions()}</select>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">Qty <span class="text-danger">*</span></label>
                                <input type="number" name="items[${index}][quantity]" class="form-control transfer-qty" min="0.01" step="0.01" required>
                            </div>

                            <div class="col-lg-5">
                                <label class="form-label">Remarks</label>
                                <input type="text" name="items[${index}][remarks]" class="form-control" placeholder="Optional line remarks">
                            </div>

                            <div class="col-12 transfer-serial-wrap d-none">
                                <label class="form-label">Serial Numbers <span class="text-danger">*</span></label>
                                <select name="items[${index}][serial_ids][]" class="form-select transfer-serial-select" multiple size="6"></select>
                                <div class="form-text">Select specific available serial numbers from the source location. Quantity will follow selected count.</div>
                            </div>
                        </div>
                    </div>`;

                document.getElementById('transferLines').insertAdjacentHTML('beforeend', html);
            }

            async function loadSerials(line) {
                const itemSelect = line.querySelector('.transfer-item-select');
                const serialSelect = line.querySelector('.transfer-serial-select');
                const branchId = document.getElementById('from_branch_id').value;
                const locationId = document.getElementById('from_location_id').value;
                const itemId = itemSelect.value;

                serialSelect.innerHTML = '';

                if (!itemId || !locationId) {
                    return;
                }

                const params = new URLSearchParams({
                    item_id: itemId,
                    location_id: locationId,
                    branch_id: branchId
                });

                const response = await fetch(`{{ route('warehouse.transfer.serials.available') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                data.forEach(serial => {
                    const option = document.createElement('option');
                    option.value = serial.id;
                    option.textContent = serial.text;
                    serialSelect.appendChild(option);
                });
            }

            document.getElementById('addTransferLine').addEventListener('click', addLine);

            document.getElementById('transferLines').addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-line')) {
                    event.target.closest('.transfer-line').remove();

                    if (!document.querySelector('.transfer-line')) {
                        addLine();
                    }
                }
            });

            document.getElementById('transferLines').addEventListener('change', function (event) {
                const line = event.target.closest('.transfer-line');

                if (!line) {
                    return;
                }

                if (event.target.classList.contains('transfer-item-select')) {
                    const selected = event.target.options[event.target.selectedIndex];
                    const isSerialized = selected && selected.dataset.serialized === '1';
                    const wrap = line.querySelector('.transfer-serial-wrap');
                    const qty = line.querySelector('.transfer-qty');

                    wrap.classList.toggle('d-none', !isSerialized);
                    qty.readOnly = isSerialized;

                    if (isSerialized) {
                        qty.value = '';
                        loadSerials(line);
                    }
                }

                if (event.target.classList.contains('transfer-serial-select')) {
                    line.querySelector('.transfer-qty').value = event.target.selectedOptions.length;
                }
            });

            ['from_branch_id', 'from_location_id'].forEach(id => {
                document.getElementById(id).addEventListener('change', function () {
                    document.querySelectorAll('.transfer-line').forEach(line => {
                        const selected = line.querySelector('.transfer-item-select').selectedOptions[0];

                        if (selected && selected.dataset.serialized === '1') {
                            loadSerials(line);
                        }
                    });
                });
            });

            addLine();
        </script>
    @endpush
</x-app-layout>