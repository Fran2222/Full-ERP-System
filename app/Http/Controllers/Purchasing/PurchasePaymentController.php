<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingBankAccount;
use App\Models\AccountingJournalEntry;
use App\Models\AccountingJournalLine;
use App\Models\Purchasing\PurchaseBill;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchasePaymentController extends Controller
{
    private function authorizePurchasing(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission) ||
                $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized purchasing action.'
        );
    }

    public function index(Request $request)
    {
        $this->authorizePurchasing('purchasing.payments.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $payments = PurchasePayment::with(['purchaseOrder', 'supplier', 'bankAccount'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->search($search)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $postedTotal = (float) PurchasePayment::where('status', 'posted')->sum('amount');

        $monthTotal = (float) PurchasePayment::where('status', 'posted')
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $voidedTotal = (float) PurchasePayment::where('status', 'voided')->sum('amount');

        return view('purchasing.payments.index', compact(
            'payments',
            'perPage',
            'search',
            'status',
            'postedTotal',
            'monthTotal',
            'voidedTotal'
        ));
    }

    public function create(Request $request)
    {
        $this->authorizePurchasing('purchasing.payments.create');

        $purchaseOrders = PurchaseOrder::with(['supplier'])
            ->whereIn('status', ['partially_received', 'received'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($po) {
                $po->received_amount = $this->getReceivedAmount($po);
                $po->paid_amount = $this->getPaidAmount($po);
                $po->payable_balance = max(0, $po->received_amount - $po->paid_amount);

                return $po;
            })
            ->filter(function ($po) {
                return $po->payable_balance > 0;
            })
            ->values();

        $purchaseBills = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
            ->where('status', 'posted')
            ->orderByDesc('id')
            ->get()
            ->filter(function ($bill) {
                return $bill->balance > 0;
            })
            ->values();

        $selectedBill = null;

        if ($request->filled('purchase_bill_id')) {
            $selectedBill = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
                ->where('status', 'posted')
                ->find($request->purchase_bill_id);
        }

        $selectedPO = null;

        if (! $selectedBill && $request->filled('purchase_order_id')) {
            $selectedPO = PurchaseOrder::with(['supplier'])
                ->whereIn('status', ['partially_received', 'received'])
                ->find($request->purchase_order_id);

            if ($selectedPO) {
                $selectedPO->received_amount = $this->getReceivedAmount($selectedPO);
                $selectedPO->paid_amount = $this->getPaidAmount($selectedPO);
                $selectedPO->payable_balance = max(0, $selectedPO->received_amount - $selectedPO->paid_amount);
            }
        }

        $bankAccounts = AccountingBankAccount::with('accountingAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchasing.payments.create', compact(
            'purchaseOrders',
            'purchaseBills',
            'selectedBill',
            'selectedPO',
            'bankAccounts'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizePurchasing('purchasing.payments.create');

        $validated = $request->validate([
            'payment_source' => ['required', 'in:bill,po'],
            'purchase_bill_id' => ['nullable', 'required_if:payment_source,bill', 'exists:purchase_bills,id'],
            'purchase_order_id' => ['nullable', 'required_if:payment_source,po', 'exists:purchase_orders,id'],
            'accounting_bank_account_id' => ['required', 'exists:accounting_bank_accounts,id'],
            'payment_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $bill = null;
        $po = null;
        $payableBalance = 0;

        if (($validated['payment_source'] ?? 'po') === 'bill') {
            $bill = PurchaseBill::with(['purchaseOrder', 'supplier', 'postedPayments'])
                ->where('status', 'posted')
                ->findOrFail($validated['purchase_bill_id'] ?? null);

            $po = $bill->purchaseOrder;
            $payableBalance = $bill->balance;

            if ($payableBalance <= 0) {
                return back()
                    ->withInput()
                    ->with('error', 'This purchase bill has no payable balance.');
            }
        } else {
            $po = PurchaseOrder::with(['supplier'])->findOrFail($validated['purchase_order_id'] ?? null);

            $receivedAmount = $this->getReceivedAmount($po);
            $paidAmount = $this->getPaidAmount($po);
            $payableBalance = max(0, $receivedAmount - $paidAmount);

            if ($payableBalance <= 0) {
                return back()
                    ->withInput()
                    ->with('error', 'This purchase order has no payable balance.');
            }
        }

        $amount = round((float) $validated['amount'], 2);

        if ($amount > round($payableBalance, 2)) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount cannot exceed the payable balance of ' . number_format($payableBalance, 2) . '.');
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

        DB::transaction(function () use ($validated, $bill, $po, $bankAccount, $amount) {
            $paymentNo = $this->generatePaymentNo($validated['payment_date']);

            $memo = trim((string) ($validated['description'] ?? ''));

            if ($memo === '') {
                $memo = 'Supplier Payment ' . $paymentNo . ' for PO ' . $po->po_no;
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
                'purchase_order_id' => $po?->id,
                'purchase_bill_id' => $bill?->id,
                'supplier_id' => $bill?->supplier_id ?? $po->supplier_id,
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

        return redirect()
            ->route('purchasing.payments.index')
            ->with('success', 'Supplier payment recorded and accounting journal entry posted successfully.');
    }

    public function show(PurchasePayment $payment)
    {
        $this->authorizePurchasing('purchasing.payments.view');

        $payment->load([
            'purchaseOrder.supplier',
            'purchaseBill.purchaseOrder',
            'purchaseBill.supplier',
            'supplier',
            'bankAccount.accountingAccount',
            'journalEntry.lines.account',
            'creator',
            'voider',
        ]);

        return view('purchasing.payments.show', compact('payment'));
    }

    public function void(Request $request, PurchasePayment $payment)
    {
        $this->authorizePurchasing('purchasing.payments.void');

        $validated = $request->validate([
            'void_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($payment->status !== 'posted') {
            return back()->with('error', 'Only posted supplier payments can be voided.');
        }

        $payment->load(['bankAccount.accountingAccount', 'purchaseOrder']);

        DB::transaction(function () use ($payment, $validated) {
            $amount = round((float) $payment->amount, 2);
            $bankAccount = $payment->bankAccount;

            if (! $bankAccount) {
                throw ValidationException::withMessages([
                    'payment' => 'The selected payment has no linked cash/bank account.',
                ]);
            }

            $memo = 'Reversal of ' . $payment->payment_no;

            $journalEntry = $this->postPaymentVoidJournalEntry(
                $payment,
                $memo,
                $amount,
                $bankAccount
            );

            $bankAccount->update([
                'current_balance' => round((float) $bankAccount->current_balance + $amount, 2),
            ]);

            $payment->update([
                'status' => 'voided',
                'voided_at' => now(),
                'voided_by' => Auth::id(),
                'void_reason' => $validated['void_reason'] ?? null,
            ]);
        });

        return redirect()
            ->route('purchasing.payments.show', $payment)
            ->with('success', 'Supplier payment voided successfully. Reversal journal entry posted and cash/bank balance restored.');
    }

    private function postPaymentJournalEntry(
        string $paymentNo,
        string $paymentDate,
        string $memo,
        float $amount,
        AccountingBankAccount $bankAccount
    ): AccountingJournalEntry {
        $accountsPayableAccount = $this->findAccountingAccountByCode('2000', 'Accounts Payable');

        if (! $accountsPayableAccount || ! $bankAccount->accountingAccount) {
            throw ValidationException::withMessages([
                'accounting' => 'Accounting setup is incomplete. Please make sure Chart of Accounts has 2000 - Accounts Payable and the selected cash/bank account has a linked asset account.',
            ]);
        }

        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->generateJournalEntryNo($paymentDate),
            'entry_date' => $paymentDate,
            'description' => $memo,
            'status' => 'posted',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'created_by' => Auth::id(),
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $accountsPayableAccount->id,
            'line_no' => 1,
            'description' => $memo,
            'debit' => $amount,
            'credit' => 0,
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $bankAccount->accountingAccount->id,
            'line_no' => 2,
            'description' => $memo,
            'debit' => 0,
            'credit' => $amount,
        ]);

        return $journalEntry;
    }

    private function postPaymentVoidJournalEntry(
        PurchasePayment $payment,
        string $memo,
        float $amount,
        AccountingBankAccount $bankAccount
    ): AccountingJournalEntry {
        $accountsPayableAccount = $this->findAccountingAccountByCode('2000', 'Accounts Payable');

        if (! $accountsPayableAccount || ! $bankAccount->accountingAccount) {
            throw ValidationException::withMessages([
                'accounting' => 'Accounting setup is incomplete. Please make sure Chart of Accounts has 2000 - Accounts Payable and the selected cash/bank account has a linked asset account.',
            ]);
        }

        $journalEntry = AccountingJournalEntry::create([
            'entry_no' => $this->generateJournalEntryNo(now()->toDateString()),
            'entry_date' => now()->toDateString(),
            'description' => $memo,
            'status' => 'posted',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'created_by' => Auth::id(),
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        AccountingJournalLine::create([
            'accounting_journal_entry_id' => $journalEntry->id,
            'accounting_account_id' => $bankAccount->accountingAccount->id,
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

    private function getReceivedAmount(PurchaseOrder $po): float
    {
        return (float) DB::table('warehouse_receiving_items as ri')
            ->join('warehouse_receivings as r', 'r.id', '=', 'ri.receiving_id')
            ->where(function ($query) use ($po) {
                $query->where('r.reference_no', $po->po_no)
                    ->orWhere('r.remarks', 'ilike', '%' . $po->po_no . '%');
            })
            ->sum('ri.total_cost');
    }

    private function getPaidAmount(PurchaseOrder $po): float
    {
        return (float) PurchasePayment::where('purchase_order_id', $po->id)
            ->where('status', 'posted')
            ->sum('amount');
    }

    private function findAccountingAccountByCode(string $code, string $name): ?AccountingAccount
    {
        return AccountingAccount::where('code', $code)
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    private function generatePaymentNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');

        $count = PurchasePayment::where('payment_no', 'like', 'PP-' . $dateCode . '-%')->count() + 1;

        return 'PP-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function generateJournalEntryNo(string $date): string
    {
        $dateCode = Carbon::parse($date)->format('Ymd');

        $count = AccountingJournalEntry::where('entry_no', 'like', 'JE-' . $dateCode . '-%')->count() + 1;

        return 'JE-' . $dateCode . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}