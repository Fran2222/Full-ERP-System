<x-app-layout :assets="$assets ?? []">
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        @php
            $selectedAccountId = (string) ($accountId ?? request('account_id', ''));
            $selectedDateFrom = $dateFrom ?? request('date_from', '');
            $selectedDateTo = $dateTo ?? request('date_to', '');

            $summaryData = $summary ?? [
                'total_debit' => 0,
                'total_credit' => 0,
                'accounts_with_activity' => 0,
                'posted_lines' => 0,
            ];

            $ledgerCollection = collect($ledgerGroups ?? $ledgerAccounts ?? $accountLedgers ?? $accountsWithActivity ?? []);
        @endphp

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <h3 class="mb-1 fw-bold text-dark">General Ledger</h3>
                        <p class="text-muted mb-0">Review posted journal activity per account with running balances.</p>
                    </div>

                    <a href="{{ route('accounting.journal-entries.create') }}"
                       class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
                        New Journal Entry
                    </a>
                </div>

                <form method="GET" action="{{ route('accounting.general-ledger.index') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label text-muted fw-semibold">Account</label>
                            <select name="account_id" class="form-select rounded-3">
                                <option value="">All accounts</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $selectedAccountId == (string) $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-3">
                            <label class="form-label text-muted fw-semibold">Date From</label>
                            <input type="date" name="date_from" value="{{ $selectedDateFrom }}" class="form-control rounded-3">
                        </div>

                        <div class="col-lg-2 col-md-3">
                            <label class="form-label text-muted fw-semibold">Date To</label>
                            <input type="date" name="date_to" value="{{ $selectedDateTo }}" class="form-control rounded-3">
                        </div>

                        <div class="col-lg-4 col-md-12 d-flex gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
                                Apply Filter
                            </button>

                            <a href="{{ route('accounting.general-ledger.index') }}"
                               class="btn btn-light rounded-3 px-4 py-2 fw-semibold">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <span class="d-block text-muted mb-1">Total Debit</span>
                            <strong class="fs-5 text-dark">{{ number_format((float) data_get($summaryData, 'total_debit', 0), 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <span class="d-block text-muted mb-1">Total Credit</span>
                            <strong class="fs-5 text-dark">{{ number_format((float) data_get($summaryData, 'total_credit', 0), 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <span class="d-block text-muted mb-1">Accounts With Activity</span>
                            <strong class="fs-5 text-dark">{{ number_format((float) data_get($summaryData, 'accounts_with_activity', 0)) }}</strong>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <span class="d-block text-muted mb-1">Posted Lines</span>
                            <strong class="fs-5 text-dark">{{ number_format((float) data_get($summaryData, 'posted_lines', 0)) }}</strong>
                        </div>
                    </div>
                </div>

                @forelse($ledgerCollection as $ledger)
                    @php
                        $account = data_get($ledger, 'account', null);

                        if (! $account && (data_get($ledger, 'code') || data_get($ledger, 'name'))) {
                            $account = $ledger;
                        }

                        $lines = collect(
                            data_get($ledger, 'lines',
                                data_get($ledger, 'entries',
                                    data_get($ledger, 'journalLines',
                                        data_get($ledger, 'journal_entry_lines', [])
                                    )
                                )
                            )
                        );

                        $accountCode = data_get($account, 'code', data_get($ledger, 'code', '—'));
                        $accountName = data_get($account, 'name', data_get($ledger, 'name', 'Account'));
                        $accountType = data_get($account, 'type', data_get($ledger, 'type', ''));
                        $normalBalance = data_get($account, 'normal_balance', data_get($ledger, 'normal_balance', ''));

                        $endingBalance = data_get($ledger, 'ending_balance', data_get($ledger, 'balance', 0));
                    @endphp

                    <div class="border rounded-4 overflow-hidden mb-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 px-3 py-3 bg-light border-bottom">
                            <div>
                                <h5 class="mb-1 fw-bold text-dark">{{ $accountCode }} - {{ $accountName }}</h5>
                                <p class="mb-0 text-muted">
                                    {{ ucfirst(str_replace('_', ' ', (string) $accountType)) }}
                                    @if($normalBalance)
                                        • Normal Balance: {{ ucfirst((string) $normalBalance) }}
                                    @endif
                                </p>
                            </div>

                            <div class="text-end">
                                <span class="d-block text-muted">Ending Balance</span>
                                <h5 class="mb-0 fw-bold text-dark">{{ number_format((float) $endingBalance, 2) }}</h5>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase small fw-bold text-dark">Date</th>
                                        <th class="text-uppercase small fw-bold text-dark">Entry No.</th>
                                        <th class="text-uppercase small fw-bold text-dark">Description</th>
                                        <th class="text-end text-uppercase small fw-bold text-dark">Debit</th>
                                        <th class="text-end text-uppercase small fw-bold text-dark">Credit</th>
                                        <th class="text-end text-uppercase small fw-bold text-dark">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lines as $line)
                                        @php
                                            $entry = data_get($line, 'journalEntry', data_get($line, 'journal_entry', null));

                                            $lineDate = data_get($line, 'date',
                                                data_get($entry, 'entry_date',
                                                    data_get($entry, 'date', null)
                                                )
                                            );

                                            $entryNo = data_get($line, 'entry_no',
                                                data_get($entry, 'entry_no',
                                                    data_get($entry, 'journal_no', '—')
                                                )
                                            );

                                            $entryId = data_get($entry, 'id', data_get($line, 'journal_entry_id', null));

                                            $description = data_get($line, 'description',
                                                data_get($entry, 'description',
                                                    data_get($entry, 'memo', '—')
                                                )
                                            );

                                            $debit = (float) data_get($line, 'debit', 0);
                                            $credit = (float) data_get($line, 'credit', 0);
                                            $runningBalance = data_get($line, 'balance', data_get($line, 'running_balance', 0));
                                        @endphp

                                        <tr>
                                            <td>
                                                @if($lineDate)
                                                    {{ \Carbon\Carbon::parse($lineDate)->format('M d, Y') }}
                                                @else
                                                    —
                                                @endif
                                            </td>

                                            <td>
                                                @if($entryId)
                                                    <a href="{{ route('accounting.journal-entries.show', $entryId) }}" class="text-primary fw-semibold text-decoration-none">
                                                        {{ $entryNo }}
                                                    </a>
                                                @else
                                                    {{ $entryNo }}
                                                @endif
                                            </td>

                                            <td>{{ $description ?: '—' }}</td>
                                            <td class="text-end">{{ $debit > 0 ? number_format($debit, 2) : '0.00' }}</td>
                                            <td class="text-end">{{ $credit > 0 ? number_format($credit, 2) : '0.00' }}</td>
                                            <td class="text-end fw-semibold">{{ number_format((float) $runningBalance, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No ledger activity found for this account.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if($lines->count())
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-semibold">Account Totals</td>
                                            <td class="text-end fw-semibold">{{ number_format((float) $lines->sum('debit'), 2) }}</td>
                                            <td class="text-end fw-semibold">{{ number_format((float) $lines->sum('credit'), 2) }}</td>
                                            <td class="text-end fw-semibold">{{ number_format((float) $endingBalance, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 border rounded-4">
                        No posted ledger activity found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>