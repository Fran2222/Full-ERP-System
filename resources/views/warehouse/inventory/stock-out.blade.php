<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">

        @include('warehouse.partials.nav')
        @include('warehouse.inventory._alerts')

        @php
            $user = auth()->user();

            $canAccess = function ($permission) use ($user) {
                return $user && (
                    $user->can($permission)
                    || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
                );
            };

            abort_unless($canAccess('warehouse.stock_out.create'), 403);
        @endphp

        <div class="card rounded-4 border-0 shadow-sm">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Stock Out / Issuance</h4>
                        <p class="text-secondary mb-0">
                            Issue stock out from a selected warehouse location.
                        </p>
                    </div>

                    @can('warehouse.inventory.view')
                        <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary">
                            Back to Inventory
                        </a>
                    @endcan
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

                <form method="POST" action="{{ route('warehouse.stock-out.store') }}">
                    @csrf

                    @include('warehouse.inventory._movement-form-fields')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        @can('warehouse.inventory.view')
                            <a href="{{ route('warehouse.inventory') }}" class="btn btn-outline-secondary px-4">
                                Cancel
                            </a>
                        @endcan

                        @can('warehouse.stock_out.create')
                            <button type="submit" class="btn btn-primary px-4">
                                Save Stock Out
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>