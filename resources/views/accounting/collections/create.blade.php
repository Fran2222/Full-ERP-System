<x-app-layout>
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">New Collection</h3>
                        <p class="text-muted mb-0">Receive money and automatically post the journal entry.</p>
                    </div>
                    <a href="{{ route('accounting.collections.index') }}" class="btn btn-primary px-4">Back</a>
                </div>

                <form method="POST" action="{{ route('accounting.collections.store') }}">
                    @include('accounting.collections.form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
