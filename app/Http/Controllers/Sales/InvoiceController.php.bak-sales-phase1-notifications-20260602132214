<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\Invoice;
use App\Models\Warehouse\WarehouseItem;
use App\Services\Accounting\SalesAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
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
            'Unauthorized sales invoice action.'
        );
    }

    public function index(Request $request)
    {
        $this->access('sales.invoices.view');

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $invoices = Invoice::with('customer')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'ilike', '%' . $search . '%')
                        ->orWhere('reference_no', 'ilike', '%' . $search . '%')
                        ->orWhere('status', 'ilike', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('customer_code', 'ilike', '%' . $search . '%')
                                ->orWhere('customer_name', 'ilike', '%' . $search . '%');
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
                'tbody' => view('sales.invoices.index', [
                    'invoices' => $invoices,
                    'ajaxTableOnly' => true,
                ])->render(),
                'pagination' => $invoices->withQueryString()->links()->render(),
                'showing' => $this->paginationShowingText($invoices),
                'total' => $invoices->total(),
            ]);
        }

        return view('sales.invoices.index', compact('invoices', 'perPage', 'search'));
    }

    public function create()
    {
        $this->access('sales.invoices.create');

        return view('sales.invoices.create', [
            'customers' => Customer::where('status', true)->orderBy('customer_name')->get(),
            'items' => WarehouseItem::where('status', true)->orderBy('name')->orderBy('item_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->access('sales.invoices.create');

        $data = $this->validated($request);

        DB::transaction(function () use ($data, &$invoice) {
            $invoiceNo = $this->generateInvoiceNo();
            $totals = $this->computeTotals($data['items']);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totals['total'],
                'paid_amount' => 0,
                'balance_due' => $totals['total'],
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $line) {
                $warehouseItem = WarehouseItem::find($line['item_id']);

                $itemCode = $warehouseItem?->code ?: $warehouseItem?->item_code;
                $itemName = $warehouseItem?->name ?: $warehouseItem?->item_name;

                $quantity = (float) $line['quantity'];
                $unitPrice = (float) $line['unit_price'];
                $lineTotal = $quantity * $unitPrice;

                $invoice->items()->create([
                    'item_id' => $line['item_id'],
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'description' => $line['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);
            }

            app(SalesAccountingService::class)->postInvoice($invoice->fresh(['customer']));
        });

        return redirect()
            ->route('sales.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->access('sales.invoices.view');

        $invoice->load(['customer', 'items']);

        return view('sales.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->access('sales.invoices.edit');

        $invoice->load('items');

        return view('sales.invoices.edit', [
            'invoice' => $invoice,
            'customers' => Customer::where('status', true)->orderBy('customer_name')->get(),
            'items' => WarehouseItem::where('status', true)->orderBy('name')->orderBy('item_name')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->access('sales.invoices.edit');

        if ((float) $invoice->paid_amount > 0) {
            return redirect()
                ->route('sales.invoices.show', $invoice)
                ->with('error', 'Invoice with payment cannot be edited.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($data, $invoice) {
            $totals = $this->computeTotals($data['items']);

            $invoice->update([
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totals['total'],
                'balance_due' => $totals['total'] - (float) $invoice->paid_amount,
                'status' => ((float) $invoice->paid_amount > 0) ? 'partially_paid' : 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            foreach ($data['items'] as $line) {
                $warehouseItem = WarehouseItem::find($line['item_id']);

                $itemCode = $warehouseItem?->code ?: $warehouseItem?->item_code;
                $itemName = $warehouseItem?->name ?: $warehouseItem?->item_name;

                $quantity = (float) $line['quantity'];
                $unitPrice = (float) $line['unit_price'];
                $lineTotal = $quantity * $unitPrice;

                $invoice->items()->create([
                    'item_id' => $line['item_id'],
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'description' => $line['description'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);
            }

            app(SalesAccountingService::class)->postInvoice($invoice->fresh(['customer']));
        });

        return redirect()
            ->route('sales.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->access('sales.invoices.delete');

        if ((float) $invoice->paid_amount > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Invoice with payment cannot be deleted.',
                ], 422);
            }

            return redirect()
                ->route('sales.invoices.index')
                ->with('error', 'Invoice with payment cannot be deleted.');
        }

        DB::transaction(function () use ($invoice) {
            app(SalesAccountingService::class)->reverseInvoice($invoice->fresh(['customer']));
            $invoice->delete();
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Invoice deleted and accounting entry reversed successfully.',
            ]);
        }

        return redirect()
            ->route('sales.invoices.index')
            ->with('success', 'Invoice deleted and accounting entry reversed successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:warehouse_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function computeTotals(array $items): array
    {
        $subtotal = 0;

        foreach ($items as $line) {
            $subtotal += (float) $line['quantity'] * (float) $line['unit_price'];
        }

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    private function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $countToday = Invoice::whereDate('created_at', today())->count() + 1;

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