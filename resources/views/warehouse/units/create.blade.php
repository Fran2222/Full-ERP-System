<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Add Unit</h4>
                        <p class="text-secondary mb-0">
                            Create a unit of measurement for warehouse items.
                        </p>
                    </div>

                    <a href="{{ route('warehouse.units.index') }}" class="btn btn-outline-secondary">
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

                <form method="POST" action="{{ route('warehouse.units.store') }}">
                    @csrf

                    @include('warehouse.units.form')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('warehouse.units.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary px-4">
                            Save Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>