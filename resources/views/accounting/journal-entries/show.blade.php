<x-app-layout>
    <div class="container-fluid content-inner mt-n5 py-0">
        @include('accounting.partials.nav')

        <div class="card rounded-4 border-0 shadow-sm accounting-card">
            <div class="card-header bg-white border-0 rounded-top-4 px-4 pt-4 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="card-title mb-1 fw-bold">{{ $journalEntry->entry_no }}</h4>
                        <p class="text-secondary mb-0">
                            {{ optional($journalEntry->entry_date)->format('M d, Y') }} · {{ $journalEntry->status_label }}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-primary accounting-back-btn">Back</a>
                        @if($journalEntry->status === 'draft')
                            <a href="{{ route('accounting.journal-entries.edit', $journalEntry) }}" class="btn btn-primary accounting-soft-btn">Edit</a>
                            <form method="POST" action="{{ route('accounting.journal-entries.post', $journalEntry) }}" class="post-journal-entry-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success accounting-soft-btn">Post</button>
                            </form>
                        @elseif($journalEntry->status === 'posted')
                            <form method="POST" action="{{ route('accounting.journal-entries.void', $journalEntry) }}" class="void-journal-entry-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger accounting-soft-btn">Void</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Entry No.</div>
                            <div class="fw-bold">{{ $journalEntry->entry_no }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Entry Date</div>
                            <div class="fw-bold">{{ optional($journalEntry->entry_date)->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Status</div>
                            <span class="accounting-badge accounting-badge-{{ $journalEntry->status }}">{{ $journalEntry->status_label }}</span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-secondary small mb-1">Created By</div>
                            <div class="fw-bold">{{ optional($journalEntry->creator)->full_name ?? optional($journalEntry->creator)->name ?? 'System' }}</div>
                        </div>
                    </div>
                </div>

                @if($journalEntry->description)
                    <div class="border rounded-3 p-3 mb-4">
                        <div class="text-secondary small mb-1">Description / Memo</div>
                        <div>{{ $journalEntry->description }}</div>
                    </div>
                @endif

                <div class="table-responsive accounting-table-wrap">
                    <table class="table align-middle mb-0 accounting-table">
                        <thead>
                            <tr>
                                <th style="width: 180px;">Account Code</th>
                                <th>Account Name</th>
                                <th>Description</th>
                                <th class="text-end" style="width: 160px;">Debit</th>
                                <th class="text-end" style="width: 160px;">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalEntry->lines as $line)
                                <tr>
                                    <td class="fw-semibold">{{ optional($line->account)->code }}</td>
                                    <td>{{ optional($line->account)->name }}</td>
                                    <td>{{ $line->description ?: '—' }}</td>
                                    <td class="text-end fw-semibold">{{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '—' }}</td>
                                    <td class="text-end fw-semibold">{{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Totals</th>
                                <th class="text-end">{{ number_format($journalEntry->total_debit, 2) }}</th>
                                <th class="text-end">{{ number_format($journalEntry->total_credit, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('submit', function (event) {
                const postForm = event.target.closest('.post-journal-entry-form');
                const voidForm = event.target.closest('.void-journal-entry-form');

                if (!postForm && !voidForm) {
                    return;
                }

                if (!window.Swal) {
                    return;
                }

                event.preventDefault();

                const isVoid = Boolean(voidForm);
                const form = isVoid ? voidForm : postForm;

                Swal.fire({
                    icon: isVoid ? 'warning' : 'question',
                    title: isVoid ? 'Void journal entry?' : 'Post journal entry?',
                    text: isVoid
                        ? 'Voiding keeps the record for audit trail but removes it from active accounting reports.'
                        : 'Posted entries will appear in the General Ledger and reports.',
                    showCancelButton: true,
                    confirmButtonText: isVoid ? 'Yes, void' : 'Yes, post',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: isVoid ? '#c03221' : '#198754',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }, true);
        </script>
    @endpush
</x-app-layout>
