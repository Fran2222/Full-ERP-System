<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('sales._nav')

        <div class="card sales-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Receive Payment</h4>
                        <p class="text-secondary mb-0">
                            Apply a customer payment to an unpaid invoice.
                        </p>
                    </div>

                    <a href="{{ route('sales.receive-payments.index') }}" class="btn btn-outline-secondary sales-soft-btn">
                        Back
                    </a>
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

                <form method="POST" action="{{ route('sales.receive-payments.store') }}">
                    @csrf

                    <div class="sales-form-section mb-4">
                        <div class="sales-section-heading">
                            <h5 class="fw-bold mb-1">Invoice Information</h5>
                            <p class="text-secondary mb-0">Select the customer and unpaid invoice to apply payment.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->customer_code }} - {{ $customer->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label">Invoice <span class="text-danger">*</span></label>
                                <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror">
                                    <option value="">Select Invoice</option>
                                    @foreach($invoices as $invoice)
                                        <option value="{{ $invoice->id }}"
                                                data-customer="{{ $invoice->customer_id }}"
                                                data-balance="{{ $invoice->balance_due }}"
                                                {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                            {{ $invoice->invoice_no }} - {{ $invoice->customer?->customer_name }} - Balance {{ number_format((float) $invoice->balance_due, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('invoice_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="sales-form-section mb-4">
                        <div class="sales-section-heading">
                            <h5 class="fw-bold mb-1">Payment Details</h5>
                            <p class="text-secondary mb-0">Record payment date, method, reference, and amount received.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="payment_date"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                       class="form-control @error('payment_date') is-invalid @enderror">
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
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

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Reference No.</label>
                                <input type="text"
                                       name="reference_no"
                                       value="{{ old('reference_no') }}"
                                       class="form-control @error('reference_no') is-invalid @enderror"
                                       placeholder="OR / Check / Transaction No.">
                                @error('reference_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number"
                                       step="0.01"
                                       min="0.01"
                                       name="amount"
                                       id="amount"
                                       value="{{ old('amount') }}"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       placeholder="0.00">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="sales-balance-hint mt-2">
                                    Invoice Balance:
                                    <span id="invoice-balance">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sales-form-section mb-4">
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-7">
                                <label class="form-label">Notes</label>
                                <textarea name="notes"
                                          rows="5"
                                          class="form-control @error('notes') is-invalid @enderror"
                                          placeholder="Enter payment notes">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-5">
                                <div class="sales-payment-summary h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary">Selected Invoice Balance</span>
                                        <span class="fw-bold" id="summary-balance">0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary">Payment Amount</span>
                                        <span class="fw-bold text-success" id="summary-amount">0.00</span>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Remaining After Payment</span>
                                        <span class="fw-bold text-danger" id="summary-remaining">0.00</span>
                                    </div>

                                    <div class="small text-secondary mt-3">
                                        Amount auto-fills from invoice balance. You may enter a partial payment.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('sales.receive-payments.index') }}" class="btn btn-outline-secondary px-4 sales-soft-btn">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary px-4 sales-soft-btn">
                            Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('sales.invoices._styles')

    <style>
        .sales-panel {
            border-radius: 18px !important;
            border: 1px solid #edf0f5 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055) !important;
            overflow: hidden;
        }

        .sales-soft-btn {
            border-radius: 10px;
            padding-top: 10px;
            padding-bottom: 10px;
            font-weight: 600;
        }

        .sales-form-section {
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.025);
        }

        .sales-section-heading {
            margin-bottom: 18px;
        }

        .sales-balance-hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef4ff;
            color: #315cf6;
            font-size: 12px;
            font-weight: 700;
        }

        .sales-payment-summary {
            border-radius: 16px;
            padding: 20px;
            background: linear-gradient(180deg, #f8f9fb 0%, #eef2f7 100%);
            border: 1px solid #edf0f5;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            min-height: 42px;
        }

        textarea.form-control {
            min-height: 134px;
        }
    </style>

    <script>
        function formatMoney(value) {
            return Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function syncInvoiceOptions() {
            const customerId = document.getElementById('customer_id').value;
            const invoiceSelect = document.getElementById('invoice_id');

            [...invoiceSelect.options].forEach(function(option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = customerId && option.dataset.customer !== customerId;
            });

            const selected = invoiceSelect.options[invoiceSelect.selectedIndex];

            if (selected && selected.hidden) {
                invoiceSelect.value = '';
            }

            syncBalance();
        }

        function syncBalance() {
            const invoiceSelect = document.getElementById('invoice_id');
            const selected = invoiceSelect.options[invoiceSelect.selectedIndex];
            const balance = selected && selected.dataset.balance ? parseFloat(selected.dataset.balance) : 0;

            document.getElementById('invoice-balance').innerText = formatMoney(balance);
            document.getElementById('summary-balance').innerText = formatMoney(balance);

            const amount = document.getElementById('amount');
            if (!amount.value && balance > 0) {
                amount.value = balance.toFixed(2);
            }

            syncPaymentSummary();
        }

        function syncPaymentSummary() {
            const invoiceSelect = document.getElementById('invoice_id');
            const selected = invoiceSelect.options[invoiceSelect.selectedIndex];
            const balance = selected && selected.dataset.balance ? parseFloat(selected.dataset.balance) : 0;
            const amount = parseFloat(document.getElementById('amount').value || 0);
            const remaining = Math.max(balance - amount, 0);

            document.getElementById('summary-amount').innerText = formatMoney(amount);
            document.getElementById('summary-remaining').innerText = formatMoney(remaining);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('customer_id').addEventListener('change', syncInvoiceOptions);
            document.getElementById('invoice_id').addEventListener('change', syncBalance);
            document.getElementById('amount').addEventListener('input', syncPaymentSummary);

            syncInvoiceOptions();
            syncBalance();
        });
    </script>
</x-app-layout>