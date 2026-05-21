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
                        <h4 class="card-title mb-1 fw-bold">Cash / Bank Accounts</h4>
                        <p class="text-secondary mb-0">Manage company cash, bank, and e-wallet accounts used by accounting transactions.</p>
                    </div>
                    <a href="{{ route('accounting.bank-accounts.create') }}" class="btn btn-primary accounting-soft-btn">
                        Add Cash / Bank Account
                    </a>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Total Accounts</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['total_accounts']) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Active Accounts</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['active_accounts']) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Opening Balance</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['total_opening_balance'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <p class="text-secondary mb-1 small fw-semibold">Current Balance</p>
                            <h4 class="fw-bold mb-0">{{ number_format($summary['total_current_balance'], 2) }}</h4>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.bank-accounts.index') }}" class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary">Show</span>
                            <select name="per_page" class="form-select" style="width: 90px" onchange="this.form.submit()">
                                @foreach([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) $perPage === (int) $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-secondary">entries</span>
                        </div>

                        <div class="d-flex gap-2">
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   style="min-width: 280px"
                                   placeholder="Search cash or bank accounts...">
                            <button type="submit" class="btn btn-primary px-4">Search</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive border rounded-4">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Linked Account</th>
                                <th>Bank / Account No.</th>
                                <th class="text-end">Opening</th>
                                <th class="text-end">Current</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bankAccounts as $bankAccount)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounting.bank-accounts.show', $bankAccount) }}" class="fw-semibold">
                                            {{ $bankAccount->name }}
                                        </a>
                                    </td>
                                    <td>{{ $bankAccount->type_label }}</td>
                                    <td>
                                        @if($bankAccount->accountingAccount)
                                            {{ $bankAccount->accountingAccount->code }} - {{ $bankAccount->accountingAccount->name }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $bankAccount->bank_name ?: '—' }}</div>
                                        <small class="text-muted">{{ $bankAccount->account_number ?: 'No account number' }}</small>
                                    </td>
                                    <td class="text-end">{{ number_format((float) $bankAccount->opening_balance, 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $bankAccount->current_balance, 2) }}</td>
                                    <td class="text-center">
                                        @if($bankAccount->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('accounting.bank-accounts.show', $bankAccount) }}"
                                               class="btn btn-sm btn-info text-white rounded-3"
                                               title="View history">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                            </a>

                                            <a href="{{ route('accounting.bank-accounts.edit', $bankAccount) }}"
                                               class="btn btn-sm btn-primary rounded-3"
                                               title="Edit">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>

                                            <form action="{{ route('accounting.bank-accounts.destroy', $bankAccount) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Delete this cash/bank account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger rounded-3" title="Delete">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                        <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M6 6l1 16h10l1-16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">No cash or bank accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                    <div class="text-secondary small">
                        Showing {{ $bankAccounts->firstItem() ?? 0 }} to {{ $bankAccounts->lastItem() ?? 0 }} of {{ $bankAccounts->total() }} entries
                    </div>
                    {{ $bankAccounts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
