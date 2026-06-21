<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">New Bill Payment</h4>
                        <p class="text-secondary mb-0">Pay an unpaid purchase bill from a cash, bank, or e-wallet account.</p>
                    </div>
                    <a href="{{ route('accounting.pay-bills.index') }}" class="btn btn-primary px-4">Back</a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('error'))
                    <div class="alert alert-danger rounded-3">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-3">
                        <strong>Please check the form.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('accounting.pay-bills.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Purchase Bill <span class="text-danger">*</span></label>
                            <select name="purchase_bill_id"
                                    id="purchase_bill_id"
                                    class="form-select @error('purchase_bill_id') is-invalid @enderror"
                                    required>
                                <option value="">Select unpaid purchase bill</option>
                                @foreach($bills as $bill)
                                    @php
                                        $balance = (float) $bill->balance;
                                        $selected = old('purchase_bill_id', $selectedBill->id ?? '') == $bill->id;
                                    @endphp
                                    <option value="{{ $bill->id }}"
                                            data-balance="{{ number_format($balance, 2, '.', '') }}"
                                            data-total="{{ number_format((float) $bill->total_amount, 2, '.', '') }}"
                                            data-paid="{{ number_format((float) $bill->paid_amount, 2, '.', '') }}"
                                            data-supplier="{{ $bill->supplier->supplier_name ?? '' }}"
                                            data-po="{{ $bill->purchaseOrder->po_no ?? '' }}"
                                            {{ $selected ? 'selected' : '' }}>
                                        {{ $bill->bill_no }} — {{ $bill->supplier->supplier_name ?? 'Supplier' }} — Balance {{ number_format($balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('purchase_bill_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Only posted purchase bills with open balances are shown.</small>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Payment Account <span class="text-danger">*</span></label>
                            <select name="accounting_bank_account_id"
                                    id="accounting_bank_account_id"
                                    class="form-select @error('accounting_bank_account_id') is-invalid @enderror"
                                    required>
                                <option value="">Select cash/bank account</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}"
                                            data-balance="{{ number_format((float) $bank->current_balance, 2, '.', '') }}"
                                            {{ old('accounting_bank_account_id') == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }} — {{ number_format((float) $bank->current_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('accounting_bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">The selected account balance will be deducted after posting.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="payment_date"
                                   value="{{ old('payment_date', now()->toDateString()) }}"
                                   class="form-control @error('payment_date') is-invalid @enderror"
                                   required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   name="amount"
                                   id="amount"
                                   value="{{ old('amount', $selectedBill ? number_format((float) $selectedBill->balance, 2, '.', '') : '') }}"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Cannot exceed bill balance or account balance.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference No.</label>
                            <input type="text"
                                   name="reference_no"
                                   value="{{ old('reference_no') }}"
                                   class="form-control @error('reference_no') is-invalid @enderror"
                                   placeholder="Check no., bank ref., OR no., optional">
                            @error('reference_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <small class="text-secondary d-block">Supplier</small>
                                        <strong id="billSupplier">-</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-secondary d-block">Purchase Order</small>
                                        <strong id="billPO">-</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-secondary d-block">Bill Total</small>
                                        <strong id="billTotal">0.00</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-secondary d-block">Paid</small>
                                        <strong id="billPaid" class="text-primary">0.00</strong>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-secondary d-block">Balance</small>
                                        <strong id="billBalance" class="text-danger">0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description / Remarks</label>
                            <textarea name="description"
                                      rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Optional payment memo">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('accounting.pay-bills.index') }}" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Post Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const billSelect = document.getElementById('purchase_bill_id');
            const amountInput = document.getElementById('amount');

            function money(value) {
                const number = parseFloat(value || 0);
                return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function updateBillInfo() {
                const option = billSelect.options[billSelect.selectedIndex];

                if (!option || !option.value) {
                    document.getElementById('billSupplier').textContent = '-';
                    document.getElementById('billPO').textContent = '-';
                    document.getElementById('billTotal').textContent = '0.00';
                    document.getElementById('billPaid').textContent = '0.00';
                    document.getElementById('billBalance').textContent = '0.00';
                    return;
                }

                document.getElementById('billSupplier').textContent = option.dataset.supplier || '-';
                document.getElementById('billPO').textContent = option.dataset.po || '-';
                document.getElementById('billTotal').textContent = money(option.dataset.total);
                document.getElementById('billPaid').textContent = money(option.dataset.paid);
                document.getElementById('billBalance').textContent = money(option.dataset.balance);

                if (!amountInput.value) {
                    amountInput.value = parseFloat(option.dataset.balance || 0).toFixed(2);
                }
            }

            if (billSelect) {
                billSelect.addEventListener('change', function () {
                    amountInput.value = '';
                    updateBillInfo();
                });
                updateBillInfo();
            }
        })();
    </script>
</x-app-layout>
