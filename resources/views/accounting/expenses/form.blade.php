@csrf

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Expense Date <span class="text-danger">*</span></label>
        <input type="date"
               name="expense_date"
               class="form-control @error('expense_date') is-invalid @enderror"
               value="{{ old('expense_date', isset($expense) && $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->toDateString()) }}"
               required>
        @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-5">
        <label class="form-label">Expense Account <span class="text-danger">*</span></label>
        <select name="expense_account_id" class="form-select @error('expense_account_id') is-invalid @enderror" required>
            <option value="">Select expense account</option>
            @foreach($expenseAccounts as $account)
                <option value="{{ $account->id }}" {{ old('expense_account_id', isset($expense) ? $expense->expense_account_id : '') == $account->id ? 'selected' : '' }}>
                    {{ $account->code }} - {{ $account->name }}
                </option>
            @endforeach
        </select>
        @error('expense_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Expense or Cost of Goods Sold account.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Paid Through <span class="text-danger">*</span></label>
        <select name="accounting_bank_account_id" class="form-select @error('accounting_bank_account_id') is-invalid @enderror" required>
            <option value="">Select cash/bank account</option>
            @foreach($bankAccounts as $bankAccount)
                <option value="{{ $bankAccount->id }}" {{ old('accounting_bank_account_id', isset($expense) ? $expense->accounting_bank_account_id : '') == $bankAccount->id ? 'selected' : '' }}>
                    {{ $bankAccount->name }} — {{ number_format($bankAccount->current_balance, 2) }}
                </option>
            @endforeach
        </select>
        @error('accounting_bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Cash/bank current balance is reduced after posting.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Payee</label>
        <input type="text"
               name="payee"
               class="form-control @error('payee') is-invalid @enderror"
               value="{{ old('payee', isset($expense) ? $expense->payee : '') }}"
               placeholder="Example: Supplier or employee name">
        @error('payee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Reference No.</label>
        <input type="text"
               name="reference_no"
               class="form-control @error('reference_no') is-invalid @enderror"
               value="{{ old('reference_no', isset($expense) ? $expense->reference_no : '') }}"
               placeholder="OR / Invoice / Voucher no.">
        @error('reference_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number"
               name="amount"
               class="form-control @error('amount') is-invalid @enderror"
               value="{{ old('amount', isset($expense) ? $expense->amount : 0) }}"
               step="0.01"
               min="0.01"
               required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Description / Memo</label>
        <textarea name="description"
                  class="form-control @error('description') is-invalid @enderror"
                  rows="3"
                  placeholder="Example: Office supplies, transportation, internet bill, etc.">{{ old('description', isset($expense) ? $expense->description : '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('accounting.expenses.index') }}" class="btn btn-soft-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">Save & Post Expense</button>
</div>
