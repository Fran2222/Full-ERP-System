<x-app-layout>
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">{{ $collection->collection_no }}</h3>
                        <p class="text-muted mb-0">
                            {{ optional($collection->collection_date)->format('M d, Y') }} · {{ ucfirst($collection->status) }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.collections.index') }}" class="btn btn-primary px-4">Back</a>

                        @if($collection->status === 'posted')
                            <form method="POST"
                                  action="{{ route('accounting.collections.void', $collection) }}"
                                  onsubmit="return confirm('Void this collection? This will reverse the receipt and reduce the cash/bank balance.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger px-4">Void</button>
                            </form>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Collection Date</p>
                            <h6 class="mb-0">{{ optional($collection->collection_date)->format('M d, Y') }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Amount</p>
                            <h6 class="mb-0">{{ number_format($collection->amount, 2) }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Status</p>
                            <h6 class="mb-0">{{ ucfirst($collection->status) }}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Journal Entry</p>
                            @if($collection->journalEntry)
                                <a href="{{ route('accounting.journal-entries.show', $collection->journalEntry) }}" class="fw-semibold">
                                    {{ $collection->journalEntry->entry_no }}
                                </a>
                            @else
                                <h6 class="mb-0">—</h6>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Payer</th>
                                <td>{{ $collection->payer ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Reference No.</th>
                                <td>{{ $collection->reference_no ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Credit Account</th>
                                <td>{{ optional($collection->creditAccount)->code }} - {{ optional($collection->creditAccount)->name }}</td>
                            </tr>
                            <tr>
                                <th>Received In</th>
                                <td>{{ optional($collection->bankAccount)->name }} ({{ optional(optional($collection->bankAccount)->accountingAccount)->code }} - {{ optional(optional($collection->bankAccount)->accountingAccount)->name }})</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $collection->description ?: '—' }}</td>
                            </tr>
                            @if($collection->status === 'voided')
                                <tr>
                                    <th>Voided At</th>
                                    <td>{{ optional($collection->voided_at)->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Void Reason</th>
                                    <td>{{ $collection->void_reason ?: '—' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($collection->journalEntry)
                    <h5 class="mb-3">Journal Entry Lines</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collection->journalEntry->lines as $line)
                                    <tr>
                                        <td>{{ optional($line->account)->code }}</td>
                                        <td>{{ optional($line->account)->name }}</td>
                                        <td>{{ $line->description ?: '—' }}</td>
                                        <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                                        <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Totals</td>
                                    <td class="text-end">{{ number_format($collection->journalEntry->total_debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($collection->journalEntry->total_credit, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
