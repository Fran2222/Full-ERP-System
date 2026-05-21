<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')
        @php
            $canManageAccounts = auth()->user()?->can('accounting.edit') || auth()->user()?->can('accounting.delete');
        @endphp

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Chart of Accounts</h4>
                        <p class="text-secondary mb-0">
                            Manage accounts used for journal entries, ledgers, and reports.
                        </p>
                    </div>

                    @can('accounting.create')
                        <a href="{{ route('accounting.accounts.create') }}" class="btn btn-primary accounting-soft-btn">
                            <i class="fas fa-plus me-1"></i> Add Account
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('accounting.accounts.index') }}" class="row g-3 align-items-center mb-3">
                    <div class="col-lg-6 col-md-6">
                        <div class="d-inline-flex align-items-center gap-2">
                            <span class="text-secondary">Show</span>
                            <select name="per_page" class="form-select" style="width: 90px;" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="text-secondary">entries</span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <div class="d-flex justify-content-md-end gap-2">
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   style="max-width: 320px;"
                                   placeholder="Search accounts...">
                            <button type="submit" class="btn btn-primary">Search</button>
                            @if($search !== '')
                                <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive accounting-table-wrap">
                    <table class="table table-hover align-middle mb-0 accounting-table">
                        <thead>
                            <tr>
                                @php
                                    $nextDirection = $direction === 'asc' ? 'desc' : 'asc';
                                    $sortUrl = fn ($column) => route('accounting.accounts.index', array_merge(request()->query(), [
                                        'sort' => $column,
                                        'direction' => $sort === $column ? $nextDirection : 'asc',
                                    ]));
                                @endphp
                                <th style="width: 120px;">
                                    <a href="{{ $sortUrl('code') }}" class="text-secondary text-decoration-none">Code</a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('name') }}" class="text-secondary text-decoration-none">Account Name</a>
                                </th>
                                <th style="width: 180px;">
                                    <a href="{{ $sortUrl('type') }}" class="text-secondary text-decoration-none">Type</a>
                                </th>
                                <th style="width: 170px;">
                                    <a href="{{ $sortUrl('normal_balance') }}" class="text-secondary text-decoration-none">Normal Balance</a>
                                </th>
                                <th style="width: 120px;">
                                    <a href="{{ $sortUrl('is_active') }}" class="text-secondary text-decoration-none">Status</a>
                                </th>
                                @if($canManageAccounts)
                                    <th class="text-end" style="width: 160px;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td class="fw-semibold">{{ $account->code }}</td>
                                    <td>
                                        <a href="{{ route('accounting.accounts.show', $account) }}" class="text-decoration-none fw-semibold">
                                            {{ $account->name }}
                                        </a>
                                        @if($account->description)
                                            <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($account->description, 80) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $account->type_label }}</td>
                                    <td>{{ $account->normal_balance_label }}</td>
                                    <td>
                                        <span class="accounting-badge {{ $account->is_active ? 'accounting-badge-success' : 'accounting-badge-muted' }}">
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    @if($canManageAccounts)
                                        <td class="text-end">
                                            <div class="accounting-action-wrap">
                                                @can('accounting.edit')
                                                    {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                        route('accounting.accounts.edit', $account),
                                                        auth()->user()?->can('accounting.delete') ? route('accounting.accounts.destroy', $account) : null,
                                                        $account->name,
                                                        'delete-account-btn',
                                                        'Edit Account',
                                                        'Delete Account'
                                                    ) !!}
                                                @elsecan('accounting.delete')
                                                    {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                        null,
                                                        route('accounting.accounts.destroy', $account),
                                                        $account->name,
                                                        'delete-account-btn',
                                                        'Edit Account',
                                                        'Delete Account'
                                                    ) !!}
                                                @endcan
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageAccounts ? 6 : 5 }}" class="text-center text-secondary py-5">
                                        No accounts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                    <div class="text-secondary small">
                        Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} entries
                    </div>
                    <div>
                        {{ $accounts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).on('click', '.delete-account-btn', function (e) {
                e.preventDefault();

                const form = $(this).closest('form');
                const name = $(this).data('name') || 'this account';

                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Are you sure?',
                        text: 'Delete "' + name + '"?',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Delete "' + name + '"?')) {
                        form.submit();
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
