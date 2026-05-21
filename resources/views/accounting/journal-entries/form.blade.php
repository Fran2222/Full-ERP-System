@php
    $oldLines = old('lines');
    $formLines = $oldLines ?: ($lines ?? []);
@endphp

<div class="row g-3">
    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold">Entry Date <span class="text-danger">*</span></label>
        <input type="date"
               name="entry_date"
               value="{{ old('entry_date', optional($journalEntry->entry_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
               class="form-control @error('entry_date') is-invalid @enderror"
               required>
        @error('entry_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-8 col-md-6">
        <label class="form-label fw-semibold">Description / Memo</label>
        <input type="text"
               name="description"
               value="{{ old('description', $journalEntry->description) }}"
               class="form-control @error('description') is-invalid @enderror"
               placeholder="Example: Record sales payment, inventory adjustment, or expense entry">
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@if($errors->has('lines'))
    <div class="alert alert-danger rounded-3 mt-4 mb-0">
        {{ $errors->first('lines') }}
    </div>
@endif

<div class="accounting-table-wrap mt-4">
    <table class="table align-middle mb-0 accounting-table" id="journal-lines-table">
        <thead>
            <tr>
                <th style="width: 280px;">Account</th>
                <th>Description</th>
                <th class="text-end" style="width: 170px;">Debit</th>
                <th class="text-end" style="width: 170px;">Credit</th>
                <th class="text-end" style="width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formLines as $index => $line)
                <tr class="journal-line-row">
                    <td>
                        <select name="lines[{{ $index }}][accounting_account_id]" class="form-select journal-account-select" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}"
                                    @selected((string) data_get($line, 'accounting_account_id') === (string) $account->id)>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text"
                               name="lines[{{ $index }}][description]"
                               value="{{ data_get($line, 'description') }}"
                               class="form-control"
                               placeholder="Line description">
                    </td>
                    <td>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="lines[{{ $index }}][debit]"
                               value="{{ data_get($line, 'debit') }}"
                               class="form-control text-end journal-debit"
                               placeholder="0.00">
                    </td>
                    <td>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="lines[{{ $index }}][credit]"
                               value="{{ data_get($line, 'credit') }}"
                               class="form-control text-end journal-credit"
                               placeholder="0.00">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-journal-line" title="Remove Line">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M9 7V4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Totals</th>
                <th class="text-end" id="journal-total-debit">0.00</th>
                <th class="text-end" id="journal-total-credit">0.00</th>
                <th></th>
            </tr>
            <tr>
                <th colspan="2" class="text-end">Difference</th>
                <th colspan="2" class="text-end" id="journal-difference">0.00</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <button type="button" class="btn btn-light accounting-soft-btn" id="add-journal-line">
        + Add Line
    </button>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('accounting.journal-entries.index') }}" class="btn btn-light accounting-soft-btn">Cancel</a>
        <button type="submit" name="action" value="draft" class="btn btn-secondary accounting-soft-btn">
            Save Draft
        </button>
        <button type="submit" name="action" value="post" class="btn btn-primary accounting-soft-btn">
            Save & Post
        </button>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            let lineIndex = {{ count($formLines) }};
            const accounts = @json($accounts->map(fn ($account) => [
                'id' => $account->id,
                'label' => $account->code . ' - ' . $account->name,
            ])->values());

            function money(value) {
                const number = parseFloat(value || 0);
                return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function calculateTotals() {
                let debit = 0;
                let credit = 0;

                document.querySelectorAll('.journal-debit').forEach(function (input) {
                    debit += parseFloat(input.value || 0);
                });

                document.querySelectorAll('.journal-credit').forEach(function (input) {
                    credit += parseFloat(input.value || 0);
                });

                const difference = debit - credit;

                document.getElementById('journal-total-debit').textContent = money(debit);
                document.getElementById('journal-total-credit').textContent = money(credit);
                document.getElementById('journal-difference').textContent = money(Math.abs(difference));
                document.getElementById('journal-difference').classList.toggle('text-danger', Math.abs(difference) > 0.009);
                document.getElementById('journal-difference').classList.toggle('text-success', Math.abs(difference) <= 0.009 && debit > 0 && credit > 0);
            }

            function accountOptions() {
                return accounts.map(function (account) {
                    return '<option value="' + account.id + '">' + account.label + '</option>';
                }).join('');
            }

            function addLine() {
                const tbody = document.querySelector('#journal-lines-table tbody');
                const row = document.createElement('tr');
                row.className = 'journal-line-row';
                row.innerHTML = `
                    <td>
                        <select name="lines[${lineIndex}][accounting_account_id]" class="form-select journal-account-select" required>
                            <option value="">Select Account</option>
                            ${accountOptions()}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="lines[${lineIndex}][description]" class="form-control" placeholder="Line description">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${lineIndex}][debit]" class="form-control text-end journal-debit" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${lineIndex}][credit]" class="form-control text-end journal-credit" placeholder="0.00">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-journal-line" title="Remove Line">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M4 7H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M9 7V4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
                lineIndex++;
                calculateTotals();
            }

            document.addEventListener('click', function (event) {
                if (event.target.closest('#add-journal-line')) {
                    addLine();
                }

                if (event.target.closest('.remove-journal-line')) {
                    const rows = document.querySelectorAll('.journal-line-row');
                    if (rows.length <= 2) {
                        return;
                    }
                    event.target.closest('tr').remove();
                    calculateTotals();
                }
            });

            document.addEventListener('input', function (event) {
                if (event.target.classList.contains('journal-debit') && parseFloat(event.target.value || 0) > 0) {
                    const row = event.target.closest('tr');
                    row.querySelector('.journal-credit').value = '';
                }

                if (event.target.classList.contains('journal-credit') && parseFloat(event.target.value || 0) > 0) {
                    const row = event.target.closest('tr');
                    row.querySelector('.journal-debit').value = '';
                }

                if (event.target.classList.contains('journal-debit') || event.target.classList.contains('journal-credit')) {
                    calculateTotals();
                }
            });

            calculateTotals();
        })();
    </script>
@endpush
