<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('purchasing._nav')

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="fw-bold mb-2">Payment Creation Moved to Accounting</h4>
                        <p class="text-muted mb-0">
                            Purchasing &gt; Payments is now read-only payment history. Actual supplier bill payments must be posted in Accounting &gt; Pay Bills.
                        </p>
                    </div>
                    <span class="badge rounded-pill bg-light text-primary px-3 py-2">Read-only history</span>
                </div>

                <div class="alert alert-info rounded-3 mt-4 mb-4">
                    Use Accounting &gt; Pay Bills to select the Purchase Bill, choose Cash/Bank, post the payment, deduct bank balance, and create the accounting journal entry.
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('accounting.pay-bills.index') }}" class="btn btn-primary px-4">Go to Accounting Pay Bills</a>
                    <a href="{{ route('purchasing.payments.index') }}" class="btn btn-outline-secondary px-4">Back to Payment History</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
