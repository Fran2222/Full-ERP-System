<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')

        <div class="card warehouse-form-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h4 class="card-title mb-0 fw-bold">Edit Supplier</h4>

                            <span class="warehouse-badge {{ $supplier->status ? 'warehouse-badge-success' : 'warehouse-badge-muted' }}">
                                {{ $supplier->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <p class="text-secondary mb-0">
                            Update supplier contact details and status.
                        </p>
                    </div>

                    <a href="{{ route('warehouse.suppliers.index') }}" class="btn btn-outline-secondary warehouse-soft-btn">
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

                <form method="POST" action="{{ route('warehouse.suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')

                    @include('warehouse.suppliers.form')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('warehouse.suppliers.index') }}" class="btn btn-outline-secondary px-4 warehouse-soft-btn">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary px-4 warehouse-soft-btn">
                            Update Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('warehouse.suppliers._form_styles')
</x-app-layout>