<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('sales._nav')

        <div class="card sales-form-panel">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="card-title mb-0 fw-bold">Edit Invoice</h4>

                            @if(isset($invoice))
                                <span class="sales-badge sales-badge-info">
                                    {{ $invoice->invoice_no }}
                                </span>
                            @endif
                        </div>

                        <p class="text-secondary mb-0">
                            Update invoice details and line items before payments are applied.
                        </p>
                    </div>

                    <a href="{{ route('sales.invoices.show', $invoice) }}" class="btn btn-outline-secondary sales-soft-btn">
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

                <form method="POST" action="{{ route('sales.invoices.update', $invoice) }}" id="invoiceForm">
                    @csrf
                    @method('PUT')

                    @include('sales.invoices.form')

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a href="{{ route('sales.invoices.show', $invoice) }}" class="btn btn-outline-secondary px-4 sales-soft-btn">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary px-4 sales-soft-btn">
                            Update Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('sales.invoices._styles')
</x-app-layout>