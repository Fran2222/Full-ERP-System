@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Account Name <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', isset($bankAccount) ? $bankAccount->name : '') }}"
               placeholder="Example: BDO Main Account"
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="">Select Type</option>

            <option value="cash" {{ old('type', isset($bankAccount) ? $bankAccount->type : '') == 'cash' ? 'selected' : '' }}>
                Cash
            </option>

            <option value="bank" {{ old('type', isset($bankAccount) ? $bankAccount->type : '') == 'bank' ? 'selected' : '' }}>
                Bank
            </option>

            <option value="e_wallet" {{ old('type', isset($bankAccount) ? $bankAccount->type : '') == 'e_wallet' ? 'selected' : '' }}>
                E-Wallet
            </option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
            <option value="1" {{ old('is_active', isset($bankAccount) ? (string) $bankAccount->is_active : '1') == '1' ? 'selected' : '' }}>
                Active
            </option>
            <option value="0" {{ old('is_active', isset($bankAccount) ? (string) $bankAccount->is_active : '1') == '0' ? 'selected' : '' }}>
                Inactive
            </option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Linked Chart of Account <span class="text-danger">*</span></label>
        <select name="accounting_account_id" class="form-select @error('accounting_account_id') is-invalid @enderror" required>
            <option value="">Select asset account</option>
            @foreach($chartAccounts as $account)
                <option value="{{ $account->id }}" {{ old('accounting_account_id', isset($bankAccount) ? $bankAccount->accounting_account_id : '') == $account->id ? 'selected' : '' }}>
                    {{ $account->code }} - {{ $account->name }}
                </option>
            @endforeach
        </select>
        @error('accounting_account_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Use an Asset account such as Cash on Hand or Cash in Bank.</small>
    </div>

    <div class="col-md-3">
        <label class="form-label">Bank Name</label>
        <input type="text"
               name="bank_name"
               class="form-control @error('bank_name') is-invalid @enderror"
               value="{{ old('bank_name', isset($bankAccount) ? $bankAccount->bank_name : '') }}"
               placeholder="Example: BDO">
        @error('bank_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Account Number</label>
        <input type="text"
               name="account_number"
               class="form-control @error('account_number') is-invalid @enderror"
               value="{{ old('account_number', isset($bankAccount) ? $bankAccount->account_number : '') }}"
               placeholder="Optional">
        @error('account_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Opening Balance <span class="text-danger">*</span></label>
        <input type="number"
               name="opening_balance"
               class="form-control @error('opening_balance') is-invalid @enderror"
               value="{{ old('opening_balance', isset($bankAccount) ? $bankAccount->opening_balance : 0) }}"
               step="0.01"
               min="0"
               placeholder="0.00"
               required>
        @error('opening_balance')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">Description</label>
        <textarea name="description"
                  rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional notes for this cash/bank account">{{ old('description', isset($bankAccount) ? $bankAccount->description : '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('accounting.bank-accounts.index') }}" class="btn btn-secondary px-4">
        Cancel
    </a>
    <button type="submit" class="btn btn-primary px-4">
        {{ isset($bankAccount) && $bankAccount->exists ? 'Update Account' : 'Save Account' }}
    </button>
</div>
