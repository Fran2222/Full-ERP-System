<?php

namespace App\Services\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingJournalEntry;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Models\Sales\SalesReceipt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SalesAccountingService
{
    public function postInvoice(Invoice $invoice): ?AccountingJournalEntry
    {
        if (! Schema::hasColumn($invoice->getTable(), 'accounting_journal_entry_id')) {
            return null;
        }

        if ($invoice->accounting_journal_entry_id) {
            return AccountingJournalEntry::find($invoice->accounting_journal_entry_id);
        }

        $amount = round((float) $invoice->total_amount, 2);

        if ($amount <= 0) {
            return null;
        }

        $receivable = $this->accountByCode('1100', 'Accounts Receivable');
        $revenue = $this->accountByCode('4000', 'Sales Revenue');

        $journalEntry = $this->createPostedEntry(
            $invoice->invoice_date,
            'Sales Invoice ' . $invoice->invoice_no,
            [
                [
                    'accounting_account_id' => $receivable->id,
                    'description' => 'Sales Invoice ' . $invoice->invoice_no,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $revenue->id,
                    'description' => 'Sales Invoice ' . $invoice->invoice_no,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );

        $invoice->forceFill([
            'accounting_journal_entry_id' => $journalEntry->id,
        ])->save();

        return $journalEntry;
    }

    public function reverseInvoice(Invoice $invoice): ?AccountingJournalEntry
    {
        if (! Schema::hasColumn($invoice->getTable(), 'accounting_journal_entry_id')) {
            return null;
        }

        if (! $invoice->accounting_journal_entry_id) {
            return null;
        }

        $amount = round((float) $invoice->total_amount, 2);

        if ($amount <= 0) {
            return null;
        }

        $receivable = $this->accountByCode('1100', 'Accounts Receivable');
        $revenue = $this->accountByCode('4000', 'Sales Revenue');

        return $this->createPostedEntry(
            now()->toDateString(),
            'Reversal of Sales Invoice ' . $invoice->invoice_no,
            [
                [
                    'accounting_account_id' => $revenue->id,
                    'description' => 'Reversal of Sales Invoice ' . $invoice->invoice_no,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $receivable->id,
                    'description' => 'Reversal of Sales Invoice ' . $invoice->invoice_no,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );
    }

    public function postPayment(Payment $payment): ?AccountingJournalEntry
    {
        if (! Schema::hasColumn($payment->getTable(), 'accounting_journal_entry_id')) {
            return null;
        }

        if ($payment->accounting_journal_entry_id) {
            return AccountingJournalEntry::find($payment->accounting_journal_entry_id);
        }

        $amount = round((float) $payment->amount, 2);

        if ($amount <= 0) {
            return null;
        }

        $receivable = $this->accountByCode('1100', 'Accounts Receivable');
        $bankAccount = $this->defaultBankAccountForMethod((string) $payment->payment_method);
        $cashAccount = $bankAccount->accountingAccount;

        if (! $cashAccount) {
            throw ValidationException::withMessages([
                'payment_method' => 'Selected cash/bank account is not linked to a Chart of Account.',
            ]);
        }

        $description = 'Receive Payment ' . $payment->payment_no;

        if ($payment->invoice) {
            $description .= ' for ' . $payment->invoice->invoice_no;
        }

        $journalEntry = $this->createPostedEntry(
            $payment->payment_date,
            $description,
            [
                [
                    'accounting_account_id' => $cashAccount->id,
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $receivable->id,
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );

        $this->increaseBankBalance($bankAccount, $amount);

        $payment->forceFill([
            'accounting_bank_account_id' => $bankAccount->id,
            'accounting_journal_entry_id' => $journalEntry->id,
        ])->save();

        return $journalEntry;
    }

    public function postSalesReceipt(SalesReceipt $salesReceipt): ?AccountingJournalEntry
    {
        if (! Schema::hasColumn($salesReceipt->getTable(), 'accounting_journal_entry_id')) {
            return null;
        }

        if ($salesReceipt->accounting_journal_entry_id) {
            return AccountingJournalEntry::find($salesReceipt->accounting_journal_entry_id);
        }

        $amount = round((float) ($salesReceipt->paid_amount ?: $salesReceipt->total_amount), 2);

        if ($amount <= 0) {
            return null;
        }

        $revenue = $this->accountByCode('4000', 'Sales Revenue');
        $bankAccount = $this->defaultBankAccountForMethod((string) $salesReceipt->payment_method);
        $cashAccount = $bankAccount->accountingAccount;

        if (! $cashAccount) {
            throw ValidationException::withMessages([
                'payment_method' => 'Selected cash/bank account is not linked to a Chart of Account.',
            ]);
        }

        $description = 'Sales Receipt ' . $salesReceipt->receipt_no;

        $journalEntry = $this->createPostedEntry(
            $salesReceipt->receipt_date,
            $description,
            [
                [
                    'accounting_account_id' => $cashAccount->id,
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $revenue->id,
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );

        $this->increaseBankBalance($bankAccount, $amount);

        $salesReceipt->forceFill([
            'accounting_bank_account_id' => $bankAccount->id,
            'accounting_journal_entry_id' => $journalEntry->id,
        ])->save();

        return $journalEntry;
    }

    public function reverseSalesReceipt(SalesReceipt $salesReceipt): ?AccountingJournalEntry
    {
        if (! Schema::hasColumn($salesReceipt->getTable(), 'accounting_journal_entry_id')) {
            return null;
        }

        if (! $salesReceipt->accounting_journal_entry_id) {
            return null;
        }

        $amount = round((float) ($salesReceipt->paid_amount ?: $salesReceipt->total_amount), 2);

        if ($amount <= 0) {
            return null;
        }

        $bankAccount = $salesReceipt->accounting_bank_account_id
            ? AccountingBankAccount::with('accountingAccount')->find($salesReceipt->accounting_bank_account_id)
            : $this->defaultBankAccountForMethod((string) $salesReceipt->payment_method);

        if (! $bankAccount || ! $bankAccount->accountingAccount) {
            return null;
        }

        $revenue = $this->accountByCode('4000', 'Sales Revenue');
        $cashAccount = $bankAccount->accountingAccount;
        $description = 'Reversal of Sales Receipt ' . $salesReceipt->receipt_no;

        $journalEntry = $this->createPostedEntry(
            now()->toDateString(),
            $description,
            [
                [
                    'accounting_account_id' => $revenue->id,
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $cashAccount->id,
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );

        $this->decreaseBankBalance($bankAccount, $amount);

        return $journalEntry;
    }

    private function createPostedEntry($entryDate, string $description, array $lines): AccountingJournalEntry
    {
        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->nextEntryNo(),
            'entry_date' => $entryDate,
            'description' => $description,
            'status' => 'posted',
            'created_by' => auth()->id(),
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);

        foreach ($lines as $index => $line) {
            $journalEntry->lines()->create($line + [
                'line_no' => $index + 1,
            ]);
        }

        return $journalEntry;
    }

    private function accountByCode(string $code, string $label): AccountingAccount
    {
        $account = AccountingAccount::where('code', $code)->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'accounting' => "Missing accounting account: {$code} - {$label}.",
            ]);
        }

        return $account;
    }

    private function defaultBankAccountForMethod(string $paymentMethod): AccountingBankAccount
    {
        $method = strtolower($paymentMethod);

        $query = AccountingBankAccount::with('accountingAccount')
            ->where('is_active', true);

        if (str_contains($method, 'bank')) {
            $preferred = (clone $query)->where('type', 'bank')->orderBy('id')->first();

            if ($preferred) {
                return $preferred;
            }
        }

        if (str_contains($method, 'wallet') || str_contains($method, 'gcash') || str_contains($method, 'maya')) {
            $preferred = (clone $query)->where('type', 'e_wallet')->orderBy('id')->first();

            if ($preferred) {
                return $preferred;
            }
        }

        if (str_contains($method, 'cash')) {
            $preferred = (clone $query)->where('type', 'cash')->orderBy('id')->first();

            if ($preferred) {
                return $preferred;
            }
        }

        $bankAccount = $query->orderBy('id')->first();

        if (! $bankAccount) {
            throw ValidationException::withMessages([
                'payment_method' => 'Please create an active Cash / Bank account in Accounting before posting sales payments.',
            ]);
        }

        return $bankAccount;
    }

    private function increaseBankBalance(AccountingBankAccount $bankAccount, float $amount): void
    {
        $locked = AccountingBankAccount::whereKey($bankAccount->id)->lockForUpdate()->firstOrFail();

        $locked->current_balance = round((float) $locked->current_balance + $amount, 2);
        $locked->save();
    }

    private function decreaseBankBalance(AccountingBankAccount $bankAccount, float $amount): void
    {
        $locked = AccountingBankAccount::whereKey($bankAccount->id)->lockForUpdate()->firstOrFail();

        $locked->current_balance = round((float) $locked->current_balance - $amount, 2);
        $locked->save();
    }

    private function nextEntryNo(): string
    {
        $prefix = 'JE-' . now()->format('Ymd') . '-';

        $lastEntry = AccountingJournalEntry::withTrashed()
            ->where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('entry_no')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_no, -4)) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
