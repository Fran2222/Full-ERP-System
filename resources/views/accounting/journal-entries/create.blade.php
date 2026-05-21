<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">New Journal Entry</h4>
                        <p class="text-secondary mb-0">Create a balanced debit and credit accounting entry.</p>
                    </div>
                    <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-primary accounting-back-btn">Back</a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <form method="POST" action="{{ route('accounting.journal-entries.store') }}">
                    @csrf
                    @include('accounting.journal-entries.form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
