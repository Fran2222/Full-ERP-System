<?php

namespace App\Services\Purchasing;

use App\Models\AccountingAccount;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use App\Models\Purchasing\PurchaseBill;
use App\Models\Purchasing\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseBillAutoService
{
    public function createForFullyReceivedPurchaseOrder(PurchaseOrder $purchaseOrder): ?PurchaseBill
    {
        $po = $purchaseOrder->fresh(['items']);

        if (! $po || $po->status !== 'received') {
            return null;
        }

        if (! $this->isFullyReceived($po)) {
            return null;
        }

        $existingBill = PurchaseBill::where('purchase_order_id', $po->id)
            ->where('status', '!=', 'voided')
            ->first();

        if ($existingBill) {
            return $existingBill;
        }

        return DB::transaction(function () use ($po) {
            $existingBill = PurchaseBill::where('purchase_order_id', $po->id)
                ->where('status', '!=', 'voided')
                ->lockForUpdate()
                ->first();

            if ($existingBill) {
                return $existingBill;
            }

            $billDate = now()->toDateString();
            $billNo = $this->generateBillNo($billDate);
            $amount = round((float) $po->total_amount, 2);

            if ($amount <= 0) {
                return null;
            }

            $memo = 'Auto-created Purchase Bill ' . $billNo . ' for fully received PO ' . $po->po_no;

            $journalEntry = $this->postBillJournalEntry($billNo, $billDate, $memo, $amount);

            return PurchaseBill::create([
                'bill_no' => $billNo,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'accounting_journal_entry_id' => $journalEntry->id,
                'bill_date' => $billDate,
                'due_date' => $this->resolveDueDate($po),
                'reference_no' => $po->po_no,
                'subtotal' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'description' => $memo,
                'status' => 'posted',
                'created_by' => Auth::id() ?: ($po->received_by ?: $po->created_by),
            ]);
        });
    }

    public function createMissingBillsForFullyReceivedPurchaseOrders(): int
    {
        $created = 0;

        PurchaseOrder::with('items')
            ->where('status', 'received')
            ->whereDoesntHave('bills', function ($query) {
                $query->where('status', '!=', 'voided');
            })
            ->chunkById(50, function ($purchaseOrders) use (&$created) {
                foreach ($purchaseOrders as $po) {
                    if ($this->createForFullyReceivedPurchaseOrder($po)) {
                        $created++;
                    }
                }
            });

        return $created;
    }

    private function isFullyReceived(PurchaseOrder $po): bool
    {
        if (! $po->relationLoaded('items')) {
            $po->load('items');
        }

        if ($po->items->isEmpty()) {
            return false;
        }

        foreach ($po->items as $item) {
            if ((float) ($item->received_quantity ?? 0) < (float) ($item->quantity ?? 0)) {
                return false;
            }
        }

        return true;
    }

    private function postBillJournalEntry(string $billNo, string $billDate, string $memo, float $amount): AccountingJournalEntry
    {
        $grniAccount = $this->findOrCreateGrniAccount();
        $accountsPayableAccount = $this->findAccountingAccountByCode('2000', 'Accounts Payable');

        if (! $accountsPayableAccount || ! $grniAccount) {
            throw new \RuntimeException('Accounting setup is incomplete. Please make sure Chart of Accounts has 2000 - Accounts Payable and 2100 - Goods Received Not Invoiced.');
        }

        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->generateJournalEntryNo($billDate),
            'entry_date' => $billDate,
            'description' => $memo,
            'status' => 'posted',
            'created_by' => Auth::id(),
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $grniAccount->id,
            'line_no' => 1,
            'description' => $memo,
            'debit' => $amount,
            'credit' => 0,
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $accountsPayableAccount->id,
            'line_no' => 2,
            'description' => $memo,
            'debit' => 0,
            'credit' => $amount,
        ]);

        return $journalEntry;
    }

    private function resolveDueDate(PurchaseOrder $po): ?string
    {
        $terms = strtolower((string) ($po->payment_terms ?? ''));

        if (str_contains($terms, '30')) {
            return now()->addDays(30)->toDateString();
        }

        if (str_contains($terms, '15')) {
            return now()->addDays(15)->toDateString();
        }

        if (str_contains($terms, '7')) {
            return now()->addDays(7)->toDateString();
        }

        return now()->toDateString();
    }

    private function findAccountingAccountByCode(string $code, string $name): ?AccountingAccount
    {
        return AccountingAccount::where('code', $code)
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    private function findOrCreateGrniAccount(): AccountingAccount
    {
        $account = AccountingAccount::where('code', '2100')
            ->where('is_active', true)
            ->first();

        if ($account) {
            return $account;
        }

        return AccountingAccount::create([
            'code' => '2100',
            'name' => 'Goods Received Not Invoiced',
            'type' => 'liability',
            'normal_balance' => 'credit',
            'description' => 'Auto-created clearing account for purchase receiving before supplier billing.',
            'is_active' => true,
        ]);
    }

    private function generateBillNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');
        $count = PurchaseBill::where('bill_no', 'like', 'PB-' . $dateCode . '-%')->withTrashed()->count() + 1;

        return 'PB-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');
        $count = AccountingJournalEntry::where('entry_no', 'like', 'JE-' . $dateCode . '-%')->count() + 1;

        return 'JE-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
