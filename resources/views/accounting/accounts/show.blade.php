<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">{{ $account->code }} - {{ $account->name }}</h4>
                        <p class="text-secondary mb-0">
                            Chart of account details.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.accounts.edit', $account) }}" class="btn btn-primary">
                            Edit
                        </a>
                        <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary">
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 h-100">
                            <p class="text-secondary mb-1">Account Code</p>
                            <h5 class="fw-bold mb-0">{{ $account->code }}</h5>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 h-100">
                            <p class="text-secondary mb-1">Account Name</p>
                            <h5 class="fw-bold mb-0">{{ $account->name }}</h5>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 h-100">
                            <p class="text-secondary mb-1">Type</p>
                            <h5 class="fw-bold mb-0">{{ $account->type_label }}</h5>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 h-100">
                            <p class="text-secondary mb-1">Normal Balance</p>
                            <h5 class="fw-bold mb-0">{{ $account->normal_balance_label }}</h5>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 h-100">
                            <p class="text-secondary mb-1">Status</p>
                            <span class="accounting-badge {{ $account->is_active ? 'accounting-badge-success' : 'accounting-badge-muted' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-4 p-4">
                            <p class="text-secondary mb-1">Description</p>
                            <p class="mb-0">{{ $account->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
