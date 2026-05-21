@csrf

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Collection Date <span class="text-danger">*</span></label>
        <input type="date"
               name="collection_date"
               class="form-control @error('collection_date') is-invalid @enderror"
               value="{{ old('collection_date', isset($collection) && $collection->collection_date ? $collection->collection_date->format('Y-m-d') : now()->toDateString()) }}"
               required>
        @error('collection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-5">
        <label class="form-label">Credit Account <span class="text-danger">*</span></label>
        <select name="credit_account_id" class="form-select @error('credit_account_id') is-invalid @enderror" required>
            <option value="">Select revenue or receivable account</option>
            @foreach($creditAccounts as $account)
                <option value="{{ $account->id }}" {{ old('credit_account_id', isset($collection) ? $collection->credit_account_id : '') == $account->id ? 'selected' : '' }}>
                    {{ $account->code }} - {{ $account->name }}
                </option>
            @endforeach
        </select>
        @error('credit_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Use Sales Revenue for direct income or Accounts Receivable for customer payments.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Received In <span class="text-danger">*</span></label>
        <select name="accounting_bank_account_id" class="form-select @error('accounting_bank_account_id') is-invalid @enderror" required>
            <option value="">Select cash/bank account</option>
            @foreach($bankAccounts as $bankAccount)
                <option value="{{ $bankAccount->id }}" {{ old('accounting_bank_account_id', isset($collection) ? $collection->accounting_bank_account_id : '') == $bankAccount->id ? 'selected' : '' }}>
                    {{ $bankAccount->name }} — {{ number_format($bankAccount->current_balance, 2) }}
                </option>
            @endforeach
        </select>
        @error('accounting_bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Cash/bank current balance is increased after posting.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Payer</label>
        <input type="text"
               name="payer"
               class="form-control @error('payer') is-invalid @enderror"
               value="{{ old('payer', isset($collection) ? $collection->payer : '') }}"
               placeholder="Example: Customer name">
        @error('payer')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Reference No.</label>
        <input type="text"
               name="reference_no"
               class="form-control @error('reference_no') is-invalid @enderror"
               value="{{ old('reference_no', isset($collection) ? $collection->reference_no : '') }}"
               placeholder="OR / Invoice / Receipt no.">
        @error('reference_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number"
               name="amount"
               class="form-control @error('amount') is-invalid @enderror"
               value="{{ old('amount', isset($collection) ? $collection->amount : 0) }}"
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
                  placeholder="Example: Customer payment, service income, sales receipt, etc.">{{ old('description', isset($collection) ? $collection->description : '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('accounting.collections.index') }}" class="btn btn-soft-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">Save & Post Collection</button>
</div>
