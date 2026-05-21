<x-app-layout>
    <div class="container-fluid py-4">
        @include('accounting.partials.nav')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Expenses</h3>
                        <p class="text-muted mb-0">Record expenses paid through cash, bank, or e-wallet accounts.</p>
                    </div>
                    @can('accounting.create')
                        <a href="{{ route('accounting.expenses.create') }}" class="btn btn-primary px-4">New Expense</a>
                    @endcan
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Posted Expenses</p>
                            <h3 class="mb-0">{{ number_format($postedTotal, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">This Month</p>
                            <h3 class="mb-0">{{ number_format($thisMonthTotal, 2) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <p class="text-muted mb-1">Voided Expenses</p>
                            <h3 class="mb-0">{{ number_format($voidedTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('accounting.expenses.index') }}" class="row g-2 align-items-center mb-3">
                    <div class="col-md-2">
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="posted" {{ $status === 'posted' ? 'selected' : '' }}>Posted</option>
                            <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Voided</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search expense no., payee, reference, description...">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Expense No.</th>
                                <th>Date</th>
                                <th>Payee</th>
                                <th>Expense Account</th>
                                <th>Paid Through</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>
                                        <a href="{{ route('accounting.expenses.show', $expense) }}" class="fw-semibold">
                                            {{ $expense->expense_no }}
                                        </a>
                                    </td>
                                    <td>{{ optional($expense->expense_date)->format('M d, Y') }}</td>
                                    <td>{{ $expense->payee ?: '—' }}</td>
                                    <td>{{ optional($expense->expenseAccount)->code }} - {{ optional($expense->expenseAccount)->name }}</td>
                                    <td>{{ optional($expense->bankAccount)->name }}</td>
                                    <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $expense->status === 'posted' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('accounting.expenses.show', $expense) }}" class="btn btn-primary btn-sm">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">No expenses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
