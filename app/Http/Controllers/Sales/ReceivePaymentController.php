<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Payment;
use App\Services\Accounting\SalesAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivePaymentController extends Controller
{
    private function access(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->can($permission)
                || $user->hasAnyRole(['Super Admin', 'Super Administrator', 'Admin'])
            ),
            403,
            'Unauthorized sales payment action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.payments.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $payments = Payment::with(['customer', 'invoice'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('payment_no', 'ilike', '%' . $search . '%')
                        ->orWhere('reference_no', 'ilike', '%' . $search . '%')
                        ->orWhere('payment_method', 'ilike', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('customer_code', 'ilike', '%' . $search . '%')
                                ->orWhere('customer_name', 'ilike', '%' . $search . '%');
                        })
                        ->orWhereHas('invoice', function ($invoice) use ($search) {
                            $invoice->where('invoice_no', 'ilike', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
            ]);

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('sales.receive-payments.index', [
                    'payments' => $payments,
                    'ajaxTableOnly' => true,
                ])->render(),
                'pagination' => $payments->withQueryString()->links()->render(),
                'showing' => $this->paginationShowingText($payments),
                'total' => $payments->total(),
            ]);
        }

        return view('sales.receive-payments.index', compact('payments', 'perPage', 'search'));
    }

    public function create()
    {
        $this->access('sales.payments.create');

        return view('sales.receive-payments.create', [
            'customers' => Customer::where('status', true)->orderBy('customer_name')->get(),
            'invoices' => Invoice::with('customer')
                ->where('balance_due', '>', 0)
                ->orderBy('due_date')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->access('sales.payments.create');

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['required', 'exists:invoices,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $invoice = Invoice::where('id', $data['invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $invoice->customer_id !== (int) $data['customer_id']) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Selected invoice does not belong to the selected customer.',
                ]);
            }

            if ((float) $data['amount'] > (float) $invoice->balance_due) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount cannot be greater than invoice balance.',
                ]);
            }

            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNo(),
                'customer_id' => $data['customer_id'],
                'invoice_id' => $data['invoice_id'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_no' => $data['reference_no'] ?? null,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $newPaidAmount = (float) $invoice->paid_amount + (float) $data['amount'];
            $newBalanceDue = (float) $invoice->total_amount - $newPaidAmount;

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'balance_due' => max($newBalanceDue, 0),
                'status' => $newBalanceDue <= 0 ? 'paid' : 'partially_paid',
            ]);

            app(SalesAccountingService::class)->postPayment($payment->fresh(['customer', 'invoice']));
        });

        return redirect()
            ->route('sales.receive-payments.index')
            ->with('success', 'Payment received successfully.');
    }

    private function generatePaymentNo(): string
    {
        $prefix = 'PAY-' . now()->format('Ymd') . '-';

        $countToday = Payment::whereDate('created_at', today())->count() + 1;

        return $prefix . str_pad($countToday, 4, '0', STR_PAD_LEFT);
    }

    private function paginationShowingText($paginator): string
    {
        if ($paginator->total() <= 0) {
            return 'Showing 0 entries';
        }

        return 'Showing ' . $paginator->firstItem()
            . ' to ' . $paginator->lastItem()
            . ' of ' . $paginator->total()
            . ' entries';
    }
}