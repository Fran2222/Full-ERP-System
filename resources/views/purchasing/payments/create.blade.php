<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">New Supplier Payment</h4>
                        <p class="text-muted mb-0">Pay a purchase bill or a received purchase order using cash, bank, or e-wallet account.</p>
                    </div>

                    <a href="{{ route('purchasing.payments.index') }}" class="btn btn-primary">Back</a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger border border-danger text-danger">{{ session('error') }}</div>
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

                @php
                    $source = request('payment_source', request()->filled('purchase_bill_id') ? 'bill' : 'po');
                    $loadedBalance = $selectedBill ? $selectedBill->balance : ($selectedPO->payable_balance ?? 0);
                    $loadedLabel = $selectedBill ? $selectedBill->bill_no : ($selectedPO->po_no ?? null);
                @endphp

                <form method="GET" action="{{ route('purchasing.payments.create') }}" class="mb-4">
                    <div class="border rounded-3 p-3">
                        <h6 class="fw-bold mb-1">Payment Source</h6>
                        <p class="text-muted mb-3">Choose a posted bill first. Direct PO payment is still available for your existing flow.</p>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Source <span class="text-danger">*</span></label>
                                <select name="payment_source" class="form-select" onchange="this.form.submit()">
                                    <option value="bill" {{ $source === 'bill' ? 'selected' : '' }}>Purchase Bill</option>
                                    <option value="po" {{ $source === 'po' ? 'selected' : '' }}>Purchase Order Direct</option>
                                </select>
                            </div>

                            @if($source === 'bill')
                                <div class="col-md-7">
                                    <label class="form-label">Purchase Bill <span class="text-danger">*</span></label>
                                    <select name="purchase_bill_id" class="form-select" required>
                                        <option value="">Select purchase bill</option>
                                        @foreach($purchaseBills as $bill)
                                            <option value="{{ $bill->id }}" {{ (string) old('purchase_bill_id', optional($selectedBill)->id) === (string) $bill->id ? 'selected' : '' }}>
                                                {{ $bill->bill_no }} - {{ $bill->supplier->supplier_name ?? 'No supplier' }} - Balance {{ number_format((float) $bill->balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="col-md-7">
                                    <label class="form-label">Purchase Order <span class="text-danger">*</span></label>
                                    <select name="purchase_order_id" class="form-select" required>
                                        <option value="">Select purchase order</option>
                                        @foreach($purchaseOrders as $po)
                                            <option value="{{ $po->id }}" {{ (string) old('purchase_order_id', optional($selectedPO)->id) === (string) $po->id ? 'selected' : '' }}>
                                                {{ $po->po_no }} - {{ $po->supplier->supplier_name ?? 'No supplier' }} - Payable {{ number_format((float) $po->payable_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary">Load</button>
                            </div>
                        </div>
                    </div>
                </form>

                @if($selectedBill || $selectedPO)
                    <form method="POST" action="{{ route('purchasing.payments.store') }}">
                        @csrf

                        <input type="hidden" name="payment_source" value="{{ $selectedBill ? 'bill' : 'po' }}">

                        @if($selectedBill)
                            <input type="hidden" name="purchase_bill_id" value="{{ $selectedBill->id }}">
                            <input type="hidden" name="purchase_order_id" value="{{ optional($selectedBill->purchaseOrder)->id }}">
                        @else
                            <input type="hidden" name="purchase_order_id" value="{{ $selectedPO->id }}">
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Supplier Information</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Reference</small>
                                            @if($selectedBill)
                                                <a href="{{ route('purchasing.bills.show', $selectedBill) }}" class="fw-bold text-primary">{{ $selectedBill->bill_no }}</a>
                                            @else
                                                <a href="{{ route('purchasing.purchase-orders.show', $selectedPO) }}" class="fw-bold text-primary">{{ $selectedPO->po_no }}</a>
                                            @endif
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Source</small>
                                            <span class="badge bg-info">{{ $selectedBill ? 'Purchase Bill' : 'Direct PO' }}</span>
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Supplier</small>
                                            <strong>{{ $selectedBill->supplier->supplier_name ?? $selectedPO->supplier->supplier_name ?? '—' }}</strong>
                                        </div>

                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Contact</small>
                                            <strong>{{ $selectedBill->supplier->contact_person ?? $selectedPO->supplier->contact_person ?? '—' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Payable Summary</h6>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Total</small>
                                            <h5 class="fw-bold mb-0">
                                                {{ number_format((float) ($selectedBill->total_amount ?? $selectedPO->received_amount), 2) }}
                                            </h5>
                                        </div>

                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Paid</small>
                                            <h5 class="fw-bold mb-0">
                                                {{ number_format((float) ($selectedBill->paid_amount ?? $selectedPO->paid_amount), 2) }}
                                            </h5>
                                        </div>

                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Balance</small>
                                            <h5 class="fw-bold text-danger mb-0">{{ number_format((float) $loadedBalance, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3">
                            <h6 class="fw-bold mb-3">Payment Details</h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Paid Through <span class="text-danger">*</span></label>
                                    <select name="accounting_bank_account_id" class="form-select" required>
                                        <option value="">Select cash/bank account</option>
                                        @foreach($bankAccounts as $bank)
                                            <option value="{{ $bank->id }}" {{ (string) old('accounting_bank_account_id') === (string) $bank->id ? 'selected' : '' }}>
                                                {{ $bank->name }} — {{ number_format((float) $bank->current_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Payment will reduce this cash/bank balance.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" value="{{ old('amount', number_format((float) $loadedBalance, 2, '.', '')) }}" class="form-control" min="0.01" step="0.01" max="{{ number_format((float) $loadedBalance, 2, '.', '') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Reference No.</label>
                                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="OR / Voucher / Bank reference no.">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Description / Memo</label>
                                    <input type="text" name="description" value="{{ old('description', 'Supplier Payment for ' . $loadedLabel) }}" class="form-control" placeholder="Payment notes">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('purchasing.payments.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save & Post Payment</button>
                        </div>
                    </form>
                @else
                    <div class="border rounded-3 p-5 text-center bg-light">
                        <h5 class="fw-bold mb-1">Select a Payment Source</h5>
                        <p class="text-muted mb-0">Choose a purchase bill or received purchase order above to start supplier payment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
