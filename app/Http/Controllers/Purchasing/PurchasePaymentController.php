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
        $this->authorizePurchasing('purchasing.payments.view');

        $query = [];

        if ($request->filled('purchase_bill_id')) {
            $query['purchase_bill_id'] = $request->purchase_bill_id;
        }

        return redirect()
            ->route('accounting.pay-bills.create', $query)
            ->with('info', 'Purchasing payment creation has been disabled. Please post supplier payments in Accounting > Pay Bills.');
    }


    public function store(Request $request)
    {
        $this->authorizePurchasing('purchasing.payments.view');

        return redirect()
            ->route('accounting.pay-bills.index')
            ->with('info', 'Purchasing payment posting has been disabled. Please post supplier payments in Accounting > Pay Bills.');
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
        $this->authorizePurchasing('purchasing.payments.view');

        return redirect()
            ->route('purchasing.payments.show', $payment)
            ->with('info', 'Voiding supplier payments from Purchasing is disabled. Please handle payment corrections in Accounting.');
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