<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')
        @php
            $canManageJournalEntries = auth()->user()?->can('accounting.edit') || auth()->user()?->can('accounting.delete');
        @endphp

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">Journal Entries</h4>
                        <p class="text-secondary mb-0">
                            Record balanced debit and credit accounting transactions.
                        </p>
                    </div>

                    @can('accounting.create')
                        <a href="{{ route('accounting.journal-entries.create') }}" class="btn btn-primary accounting-soft-btn">
                            <i class="fas fa-plus me-1"></i> New Journal Entry
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger rounded-3 mb-4">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('accounting.journal-entries.index') }}" class="row g-3 align-items-center mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-5">
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

                    <div class="col-xl-8 col-lg-8 col-md-7">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <select name="status" class="form-select" style="max-width: 170px;" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                @foreach(\App\Models\AccountingJournalEntry::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   style="max-width: 320px;"
                                   placeholder="Search journal entries...">
                            <button type="submit" class="btn btn-primary">Search</button>
                            @if($search !== '' || $status !== '')
                                <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-outline-secondary">Clear</a>
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
                                    $sortUrl = fn ($column) => route('accounting.journal-entries.index', array_merge(request()->query(), [
                                        'sort' => $column,
                                        'direction' => $sort === $column ? $nextDirection : 'asc',
                                    ]));
                                @endphp
                                <th style="width: 170px;"><a href="{{ $sortUrl('entry_no') }}" class="text-secondary text-decoration-none">Entry No.</a></th>
                                <th style="width: 150px;"><a href="{{ $sortUrl('entry_date') }}" class="text-secondary text-decoration-none">Date</a></th>
                                <th>Description</th>
                                <th class="text-end" style="width: 150px;">Debit</th>
                                <th class="text-end" style="width: 150px;">Credit</th>
                                <th style="width: 120px;"><a href="{{ $sortUrl('status') }}" class="text-secondary text-decoration-none">Status</a></th>
                                <th class="text-end" style="width: 170px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalEntries as $journalEntry)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounting.journal-entries.show', $journalEntry) }}" class="text-decoration-none fw-semibold">
                                            {{ $journalEntry->entry_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($journalEntry->entry_date)->format('M d, Y') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($journalEntry->description ?: 'No memo', 90) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($journalEntry->total_debit, 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($journalEntry->total_credit, 2) }}</td>
                                    <td>
                                        <span class="accounting-badge accounting-badge-{{ $journalEntry->status }}">
                                            {{ $journalEntry->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="accounting-action-wrap">
                                            @if($journalEntry->status === 'draft' && $canManageJournalEntries)
                                                @can('accounting.edit')
                                                    {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                        route('accounting.journal-entries.edit', $journalEntry),
                                                        auth()->user()?->can('accounting.delete') ? route('accounting.journal-entries.destroy', $journalEntry) : null,
                                                        $journalEntry->entry_no,
                                                        'delete-journal-entry-btn',
                                                        'Edit Journal Entry',
                                                        'Delete Journal Entry'
                                                    ) !!}
                                                @elsecan('accounting.delete')
                                                    {!! \App\Helpers\ActionButtonHelper::editDelete(
                                                        null,
                                                        route('accounting.journal-entries.destroy', $journalEntry),
                                                        $journalEntry->entry_no,
                                                        'delete-journal-entry-btn',
                                                        'Edit Journal Entry',
                                                        'Delete Journal Entry'
                                                    ) !!}
                                                @endcan
                                            @else
                                                {!! \App\Helpers\ActionButtonHelper::viewEdit(
                                                    route('accounting.journal-entries.show', $journalEntry),
                                                    null,
                                                    'View Journal Entry',
                                                    'Edit Journal Entry'
                                                ) !!}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-5">No journal entries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                    <div class="text-secondary small">
                        Showing {{ $journalEntries->firstItem() ?? 0 }} to {{ $journalEntries->lastItem() ?? 0 }} of {{ $journalEntries->total() }} entries
                    </div>
                    <div>{{ $journalEntries->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).on('click', '.delete-journal-entry-btn', function (e) {
                e.preventDefault();

                const form = $(this).closest('form');
                const name = $(this).data('name') || 'this journal entry';

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
