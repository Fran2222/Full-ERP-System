<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingJournalEntry;
use App\Models\Purchasing\PurchaseBill;
use App\Models\Purchasing\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayBillController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.pay-bills.view');

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', 'unpaid'));

        if (! in_array($status, ['unpaid', 'partial', 'paid', 'all'], true)) {
            $status = 'unpaid';
        }

        $baseQuery = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments', 'payments'])
            ->where('status', 'posted')
            ->search($search)
            ->orderByDesc('id');

        $bills = $baseQuery
            ->get()
            ->filter(function (PurchaseBill $bill) use ($status) {
                $paid = round((float) $bill->paid_amount, 2);
                $total = round((float) $bill->total_amount, 2);

                if ($status === 'unpaid') {
                    return $paid <= 0 && $total > 0;
                }

                if ($status === 'partial') {
                    return $paid > 0 && $paid < $total;
                }

                if ($status === 'paid') {
                    return $total > 0 && $paid >= $total;
                }

                return true;
            })
            ->values();

        $currentPage = max(1, (int) $request->input('page', 1));
        $pagedBills = new \Illuminate\Pagination\LengthAwarePaginator(
            $bills->forPage($currentPage, $perPage)->values(),
            $bills->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $allPostedBills = PurchaseBill::with('postedPayments')->where('status', 'posted')->get();
        $summary = [
            'open_balance' => $allPostedBills->sum(fn ($bill) => (float) $bill->balance),
            'paid_total' => (float) PurchasePayment::where('status', 'posted')->whereNotNull('purchase_bill_id')->sum('amount'),
            'bill_count' => $allPostedBills->count(),
            'open_count' => $allPostedBills->filter(fn ($bill) => (float) $bill->balance > 0)->count(),
        ];

        return view('accounting.pay-bills.index', [
            'bills' => $pagedBills,
            'summary' => $summary,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.pay-bills.create');

        $bills = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
            ->where('status', 'posted')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (PurchaseBill $bill) => (float) $bill->balance > 0)
            ->values();

        $selectedBill = null;

        if ($request->filled('purchase_bill_id')) {
            $selectedBill = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
                ->where('status', 'posted')
                ->find($request->purchase_bill_id);
        }

        $bankAccounts = AccountingBankAccount::with('accountingAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('accounting.pay-bills.create', compact('bills', 'selectedBill', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccountingAccess('accounting.pay-bills.create');

        $validated = $request->validate([
            'purchase_bill_id' => ['required', 'exists:purchase_bills,id'],
            'accounting_bank_account_id' => ['required', 'exists:accounting_bank_accounts,id'],
            'payment_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $bill = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
            ->where('status', 'posted')
            ->findOrFail($validated['purchase_bill_id']);

        $balance = round((float) $bill->balance, 2);
        $amount = round((float) $validated['amount'], 2);

        if ($balance <= 0) {
            return back()
                ->withInput()
                ->with('error', 'This purchase bill has no open balance.');
        }

        if ($amount > $balance) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount cannot exceed the bill balance of ' . number_format($balance, 2) . '.');
        }

        $bankAccount = AccountingBankAccount::with('accountingAccount')
            ->where('is_active', true)
            ->findOrFail($validated['accounting_bank_account_id']);

        $cashBalance = round((float) $bankAccount->current_balance, 2);

        if ($amount > $cashBalance) {
            return back()
                ->withInput()
                ->with('error', 'Insufficient cash/bank balance. Available balance is ' . number_format($cashBalance, 2) . '.');
        }

        $payment = null;

        DB::transaction(function () use ($validated, $bill, $bankAccount, $amount, &$payment) {
            $paymentNo = $this->generatePaymentNo($validated['payment_date']);

            $memo = trim((string) ($validated['description'] ?? ''));
            if ($memo === '') {
                $memo = 'Payment for Purchase Bill ' . $bill->bill_no;
                if ($bill->purchaseOrder) {
                    $memo .= ' / PO ' . $bill->purchaseOrder->po_no;
                }
            }

            $journalEntry = $this->postPaymentJournalEntry(
                $paymentNo,
                $validated['payment_date'],
                $memo,
                $amount,
                $bankAccount
            );

            $payment = PurchasePayment::create([
                'payment_no' => $paymentNo,
                'purchase_order_id' => $bill->purchase_order_id,
                'purchase_bill_id' => $bill->id,
                'supplier_id' => $bill->supplier_id,
                'accounting_bank_account_id' => $bankAccount->id,
                'accounting_journal_entry_id' => $journalEntry->id,
                'payment_date' => $validated['payment_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'amount' => $amount,
                'description' => $memo,
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            $bankAccount->update([
                'current_balance' => round((float) $bankAccount->current_balance - $amount, 2),
            ]);
        });

        if ($payment
            && class_exists(\App\Services\SystemNotificationService::class)
            && method_exists(\App\Services\SystemNotificationService::class, 'notifyPurchaseBillPaymentCreated')) {
            \App\Services\SystemNotificationService::notifyPurchaseBillPaymentCreated(
                $payment->fresh(['purchaseBill.purchaseOrder', 'purchaseBill.supplier']),
                auth()->id()
            );
        }
        return redirect()
            ->route('accounting.pay-bills.show', $payment)
            ->with('success', 'Bill payment posted successfully. Cash/bank balance and journal entry were updated.');
    }

    public function show(PurchasePayment $payment)
    {
        $this->authorizeAccountingAccess('accounting.pay-bills.view');

        $payment->load([
            'purchaseBill.purchaseOrder',
            'purchaseBill.supplier',
            'purchaseOrder',
            'supplier',
            'bankAccount.accountingAccount',
            'journalEntry.lines.account',
            'creator',
        ]);

        return view('accounting.pay-bills.show', compact('payment'));
    }

    private function postPaymentJournalEntry(
        string $paymentNo,
        string $paymentDate,
        string $memo,
        float $amount,
        AccountingBankAccount $bankAccount
    ): AccountingJournalEntry {
        $accountsPayableAccount = $this->findAccountsPayableAccount();

        if (! $accountsPayableAccount || ! $bankAccount->accountingAccount) {
            throw ValidationException::withMessages([
                'accounting' => 'Accounting setup is incomplete. Please make sure Chart of Accounts has an active Accounts Payable account and the selected cash/bank account has a linked asset account.',
            ]);
        }

        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->generateJournalEntryNo($paymentDate),
            'entry_date' => $paymentDate,
            'description' => $memo,
            'status' => 'posted',
            'created_by' => Auth::id(),
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        DB::table('accounting_journal_lines')->insert([
            [
                'accounting_journal_entry_id' => $journalEntry->id,
                'accounting_account_id' => $accountsPayableAccount->id,
                'line_no' => 1,
                'description' => $memo,
                'debit' => $amount,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'accounting_journal_entry_id' => $journalEntry->id,
                'accounting_account_id' => $bankAccount->accountingAccount->id,
                'line_no' => 2,
                'description' => $memo,
                'debit' => 0,
                'credit' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return $journalEntry;
    }

    private function findAccountsPayableAccount(): ?AccountingAccount
    {
        return AccountingAccount::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('code', '2000')
                    ->orWhere('name', 'ilike', '%Accounts Payable%')
                    ->orWhere('name', 'ilike', '%Payable%');
            })
            ->orderByRaw("case when code = '2000' then 0 else 1 end")
            ->first();
    }

    private function generatePaymentNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');
        $count = PurchasePayment::withTrashed()
            ->where('payment_no', 'like', 'PP-' . $dateCode . '-%')
            ->count() + 1;

        return 'PP-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');
        $count = AccountingJournalEntry::withTrashed()
            ->where('entry_no', 'like', 'JE-' . $dateCode . '-%')
            ->count() + 1;

        return 'JE-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeAccountingAccess(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->can('accounting.view')
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin', 'super admin', 'super-admin', 'superadmin', 'admin'])
            ),
            403
        );
    }
}
