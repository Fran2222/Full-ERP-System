<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">New Purchase Bill</h4>
                        <p class="text-muted mb-0">Create an AP bill from a received purchase order.</p>
                    </div>

                    <a href="{{ route('purchasing.bills.index') }}" class="btn btn-primary">
                        Back
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger border border-danger text-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border border-danger text-danger">
                        <strong>Please check the form.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" action="{{ route('purchasing.bills.create') }}" class="mb-4">
                    <div class="border rounded-3 p-3">
                        <h6 class="fw-bold mb-1">Purchase Order Selection</h6>
                        <p class="text-muted mb-3">Choose a received purchase order with billable balance.</p>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-10">
                                <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                                <select name="purchase_order_id" class="form-select" required>
                                    <option value="">Select purchase order</option>
                                    @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}"
                                            {{ (string) old('purchase_order_id', optional($selectedPO)->id) === (string) $po->id ? 'selected' : '' }}>
                                            {{ $po->po_no }}
                                            - {{ $po->supplier->supplier_name ?? 'No supplier' }}
                                            - Billable {{ number_format((float) $po->billable_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary">Load PO</button>
                            </div>
                        </div>
                    </div>
                </form>

                @if($selectedPO)
                    <form method="POST" action="{{ route('purchasing.bills.store') }}">
                        @csrf

                        <input type="hidden" name="purchase_order_id" value="{{ $selectedPO->id }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Supplier Information</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">PO No.</small>
                                            <a href="{{ route('purchasing.purchase-orders.show', $selectedPO) }}" class="fw-bold text-primary">
                                                {{ $selectedPO->po_no }}
                                            </a>
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Receiving Status</small>
                                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $selectedPO->status)) }}</span>
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Supplier</small>
                                            <strong>{{ $selectedPO->supplier->supplier_name ?? '—' }}</strong>
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Contact</small>
                                            <strong>{{ $selectedPO->supplier->contact_person ?? '—' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Billable Summary</h6>

                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Received Amount</small>
                                            <h5 class="fw-bold mb-0">{{ number_format((float) $selectedPO->received_amount, 2) }}</h5>
                                        </div>

                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Billed Amount</small>
                                            <h5 class="fw-bold mb-0">{{ number_format((float) $selectedPO->billed_amount, 2) }}</h5>
                                        </div>

                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Direct Paid</small>
                                            <h5 class="fw-bold text-primary mb-0">{{ number_format((float) ($selectedPO->direct_paid_amount ?? 0), 2) }}</h5>
                                        </div>

                                        <div class="col-md-3">
                                            <small class="text-muted d-block">Billable Balance</small>
                                            <h5 class="fw-bold text-danger mb-0">{{ number_format((float) $selectedPO->billable_balance, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3">
                            <h6 class="fw-bold mb-3">Bill Details</h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           name="bill_date"
                                           value="{{ old('bill_date', now()->toDateString()) }}"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Due Date</label>
                                    <input type="date"
                                           name="due_date"
                                           value="{{ old('due_date') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="amount"
                                           value="{{ old('amount', number_format((float) $selectedPO->billable_balance, 2, '.', '')) }}"
                                           class="form-control"
                                           min="0.01"
                                           step="0.01"
                                           max="{{ number_format((float) $selectedPO->billable_balance, 2, '.', '') }}"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Reference No.</label>
                                    <input type="text"
                                           name="reference_no"
                                           value="{{ old('reference_no') }}"
                                           class="form-control"
                                           placeholder="Supplier invoice no. / AP voucher no.">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Description / Memo</label>
                                    <input type="text"
                                           name="description"
                                           value="{{ old('description', 'Purchase Bill for PO ' . $selectedPO->po_no) }}"
                                           class="form-control"
                                           placeholder="Bill notes">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('purchasing.bills.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Save & Post Bill
                            </button>
                        </div>
                    </form>
                @else
                    <div class="border rounded-3 p-5 text-center bg-light">
                        <h5 class="fw-bold mb-1">Select a Purchase Order</h5>
                        <p class="text-muted mb-0">
                            Choose a received purchase order above to create an AP bill.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
