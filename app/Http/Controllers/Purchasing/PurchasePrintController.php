<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseBill;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchasePayment;

class PurchasePrintController extends Controller
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

    public function purchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchasing('purchasing.po.view');

        $purchaseOrder->load([
            'supplier',
            'location',
            'items.item.unit',
            'bills.postedPayments.bankAccount',
            'bills.journalEntry.lines.account',
            'payments.bankAccount',
            'payments.journalEntry.lines.account',
        ]);

        $paymentSummary = $this->getPurchasePaymentSummary($purchaseOrder);
        $receivings = $this->getPurchaseOrderReceivings($purchaseOrder);
        $journalEntries = $this->getPurchaseOrderJournalEntries($purchaseOrder);

        return view('purchasing.print.purchase-order', compact(
            'purchaseOrder',
            'paymentSummary',
            'receivings',
            'journalEntries'
        ));
    }

    public function bill(PurchaseBill $bill)
    {
        $this->authorizePurchasing('purchasing.bills.view');

        $bill->load([
            'purchaseOrder.supplier',
            'purchaseOrder.location',
            'supplier',
            'postedPayments.bankAccount',
            'journalEntry.lines.account',
            'creator',
            'voider',
        ]);

        return view('purchasing.print.bill', compact('bill'));
    }

    public function payment(PurchasePayment $payment)
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

        return view('purchasing.print.payment', compact('payment'));
    }

    private function getPurchasePaymentSummary(PurchaseOrder $purchaseOrder): array
    {
        $receivedAmount = $this->getReceivedAmount($purchaseOrder);

        $billedAmount = (float) $purchaseOrder->bills()
            ->where('status', 'posted')
            ->sum('total_amount');

        $directPaidAmount = (float) $purchaseOrder->payments()
            ->where('status', 'posted')
            ->whereNull('purchase_bill_id')
            ->sum('amount');

        $billPaidAmount = (float) PurchasePayment::where('purchase_order_id', $purchaseOrder->id)
            ->whereNotNull('purchase_bill_id')
            ->where('status', 'posted')
            ->sum('amount');

        $paidAmount = $directPaidAmount + $billPaidAmount;
        $payableBase = max((float) $purchaseOrder->total_amount, $receivedAmount, $billedAmount);
        $balance = max(0, $payableBase - $paidAmount);

        $paymentStatus = 'unpaid';

        if ($paidAmount > 0 && $balance > 0) {
            $paymentStatus = 'partially_paid';
        }

        if ($paidAmount > 0 && $balance <= 0) {
            $paymentStatus = 'paid';
        }

        return [
            'received_amount' => round($receivedAmount, 2),
            'billed_amount' => round($billedAmount, 2),
            'paid_amount' => round($paidAmount, 2),
            'payable_balance' => round($balance, 2),
            'payment_status' => $paymentStatus,
        ];
    }

    private function getReceivedAmount(PurchaseOrder $purchaseOrder): float
    {
        return (float) \DB::table('warehouse_receivings as wr')
            ->join('warehouse_receiving_items as wri', 'wri.receiving_id', '=', 'wr.id')
            ->where('wr.reference_no', $purchaseOrder->po_no)
            ->whereIn('wr.status', ['posted', 'received'])
            ->sum('wri.total_cost');
    }

    private function getPurchaseOrderReceivings(PurchaseOrder $purchaseOrder)
    {
        return \DB::table('warehouse_receivings as wr')
            ->leftJoin('warehouse_locations as l', 'l.id', '=', 'wr.location_id')
            ->select(
                'wr.*',
                \DB::raw('coalesce(l.location_name, l.name) as location_name'),
                \DB::raw('(select coalesce(sum(total_cost), 0) from warehouse_receiving_items where receiving_id = wr.id) as total_cost')
            )
            ->where('wr.reference_no', $purchaseOrder->po_no)
            ->orderByDesc('wr.id')
            ->get();
    }

    private function getPurchaseOrderJournalEntries(PurchaseOrder $purchaseOrder)
    {
        $journalEntries = collect();

        foreach ($purchaseOrder->bills as $bill) {
            if ($bill->journalEntry) {
                $journalEntries->push($bill->journalEntry);
            }

            foreach ($bill->postedPayments as $payment) {
                if ($payment->journalEntry) {
                    $journalEntries->push($payment->journalEntry);
                }
            }
        }

        foreach ($purchaseOrder->payments as $payment) {
            if ($payment->journalEntry) {
                $journalEntries->push($payment->journalEntry);
            }
        }

        $receivingEntries = \App\Models\AccountingJournalEntry::query()
            ->where('description', 'ilike', '%' . $purchaseOrder->po_no . '%')
            ->where('description', 'ilike', '%Purchase Receiving%')
            ->with('lines.account')
            ->get();

        return $journalEntries
            ->merge($receivingEntries)
            ->unique('id')
            ->sortByDesc('entry_date')
            ->values();
    }
}
