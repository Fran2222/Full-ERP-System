@php
    $account = $account ?? (object) [
        'id' => null,
        'code' => '',
        'name' => '',
        'type' => 'asset',
        'normal_balance' => 'debit',
        'status' => 'active',
        'description' => '',
    ];

    $typeOptions = [
        'asset' => 'Asset',
        'liability' => 'Liability',
        'equity' => 'Equity',
        'revenue' => 'Revenue',
        'expense' => 'Expense',
    ];

    $normalBalanceOptions = [
        'debit' => 'Debit',
        'credit' => 'Credit',
    ];

    $statusOptions = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Account Code <span class="text-danger">*</span></label>
        <input
            type="text"
            name="code"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $account->code) }}"
            placeholder="Example: 1000"
            required
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">Account Name <span class="text-danger">*</span></label>
        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $account->name) }}"
            placeholder="Example: Cash on Hand"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}" {{ old('type', $account->type) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Normal Balance <span class="text-danger">*</span></label>
        <select name="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror" required>
            @foreach($normalBalanceOptions as $value => $label)
                <option value="{{ $value }}" {{ old('normal_balance', $account->normal_balance) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('normal_balance')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ old('status', $account->status ?: 'active') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Description / Notes</label>
        <textarea
            name="description"
            rows="4"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Optional account description"
        >{{ old('description', $account->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>