<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectExpense;
use App\Models\ProjectMgmt\ProjectRfp;
use App\Models\ProjectMgmt\StoreName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProjectExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        $cvRecords = $this->releasedCvRecords()->get();
        return view('modules.project-mgmt.expenses.index', compact('cvRecords'));
    }

    public function list(Request $request)
    {
        $query = ProjectExpense::query()
            ->select('project_expenses.*')
            ->with(['rfp', 'storeName'])
            ->with(['receipts' => function ($receipt) {
                $receipt->orderBy('store_receipt_date')->orderBy('project_expense_receipts.id');
            }])
            ->orderByDesc('project_expenses.id');

        if ($request->filled('project_rfp_id')) {
            $query->where('project_rfp_id', $request->project_rfp_id);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->whereHas('storeName', function ($store) use ($search) {
                        $store->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('code', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('receipts', function ($receipt) use ($search) {
                        $receipt->where('store_receipt_no', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('rfp', function ($rfp) use ($search) {
                        $rfp->where('cash_voucher_no', 'ILIKE', "%{$search}%")
                            ->orWhere('rfp_code', 'ILIKE', "%{$search}%");
                    });
                });
            })
            ->addColumn('expense_id', fn ($expense) => $expense->id)
            ->addColumn('show_url', fn ($expense) => route('project-expenses.show', $expense->id) . '?cv=' . urlencode((string) $expense->project_rfp_id) . '&store=' . urlencode((string) $expense->store_name_id))
            ->addColumn('cv_no', fn ($expense) => optional($expense->rfp)->cash_voucher_no ?: '-')
            ->addColumn('store_name', fn ($expense) => optional($expense->storeName)->name ?: '-')
            ->addColumn('receipt_numbers', function ($expense) {
                $numbers = $expense->receipts
                    ->pluck('store_receipt_no')
                    ->filter()
                    ->values();

                if ($numbers->isEmpty()) {
                    $numbers = DB::table('project_expense_receipts')
                        ->where('project_expense_id', $expense->id)
                        ->orderBy('store_receipt_date')
                        ->orderBy('project_expense_receipts.id')
                        ->pluck('store_receipt_no')
                        ->filter()
                        ->values();
                }

                if ($numbers->isEmpty()) {
                    return '-';
                }

                return '<div class="d-flex flex-column align-items-start gap-1">'
                    . $numbers->map(fn ($number) => '<span class="badge bg-soft-primary text-primary">' . e($number) . '</span>')->implode('')
                    . '</div>';
            })
            ->addColumn('receipts_total_amount_formatted', fn ($expense) => '₱ ' . number_format((float) $expense->receipts_total_amount, 2))
            ->addColumn('status_badge', function ($expense) {
                $status = strtolower($expense->status ?? 'pending');
                if (! in_array($status, ['pending', 'liquidated'], true)) {
                    $status = 'pending';
                }

                $class = $status === 'liquidated' ? 'text-success' : 'text-warning';
                return '<span class="' . $class . ' fw-semibold">' . ucfirst($status) . '</span>';
            })
            ->addColumn('action', function ($expense) {
                $querySuffix = '?cv=' . urlencode((string) $expense->project_rfp_id) . '&store=' . urlencode((string) $expense->store_name_id);

                $editUrl = auth()->user()->can('projects_mgmt.edit') && strtolower($expense->status ?? 'pending') !== 'liquidated'
                    ? route('project-expenses.edit', $expense->id) . $querySuffix
                    : null;
                $deleteUrl = auth()->user()->can('projects_mgmt.delete') && strtolower($expense->status ?? 'pending') !== 'liquidated'
                    ? route('project-expenses.destroy', $expense->id) . $querySuffix
                    : null;

                $viewUrl = route('project-expenses.show', $expense->id) . $querySuffix;
                $viewButton = '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-info d-inline-flex align-items-center justify-content-center" title="View Expense" style="width:34px;height:30px;padding:0;border-radius:6px;">'
                    . '<svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M2 12C2 12 5.6 5 12 5C18.4 5 22 12 22 12C22 12 18.4 19 12 19C5.6 19 2 12 2 12Z" stroke="white" stroke-width="1.5"/><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="white" stroke-width="1.5"/></svg></a>';

                return '<div class="d-flex align-items-center justify-content-end gap-2">' . $viewButton . ActionButtonHelper::editDelete($editUrl, $deleteUrl, 'Expense #' . $expense->id, 'delete-project-expense', 'Edit Expense', 'Delete Expense') . '</div>';
            })
            ->rawColumns(['receipt_numbers', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $cvRecords = $this->releasedCvRecords()->get();
        $stores = StoreName::where('status', 'active')->orderBy('name')->get();
        return view('modules.project-mgmt.expenses.create', compact('cvRecords', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedExpense($request);

        DB::transaction(function () use ($request, $validated) {
            $expense = new ProjectExpense();
            $expense->fill([
                'project_rfp_id' => $validated['project_rfp_id'],
                'store_name_id' => $validated['store_name_id'],
                'receipts_total_amount' => $this->receiptTotal($validated['receipts']),
                'status' => 'pending',
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->handleAttachment($request, $expense);
            $expense->save();
            $this->syncReceipts($expense, $validated['receipts']);
        });

        return redirect()->route('project-expenses.index')->with('success', 'Expense created successfully.');
    }

    public function show(Request $request, $projectExpense)
    {
        $projectExpense = $this->resolveExpense($projectExpense, $request);

        if (! $projectExpense) {
            return redirect()
                ->route('project-expenses.index')
                ->with('error', 'Expense record was not found or may have been deleted. Please open it again from the list.');
        }

        $projectExpense->load(['rfp', 'storeName', 'receipts', 'createdBy', 'updatedBy']);

        return view('modules.project-mgmt.expenses.show', compact('projectExpense'));
    }


    public function edit(Request $request, $projectExpense)
    {
        $projectExpense = $this->resolveExpense($projectExpense, $request);

        if (! $projectExpense) {
            return redirect()
                ->route('project-expenses.index')
                ->with('error', 'Expense record was not found or may have been deleted. Please open it again from the list.');
        }

        abort_if(strtolower($projectExpense->status ?? 'pending') === 'liquidated', 403, 'Liquidated expenses can no longer be edited.');

        $projectExpense->load(['receipts', 'rfp', 'storeName']);
        $cvRecords = $this->releasedCvRecords()->get();
        $stores = StoreName::where('status', 'active')->orderBy('name')->get();

        return view('modules.project-mgmt.expenses.edit', compact('projectExpense', 'cvRecords', 'stores'));
    }


    public function update(Request $request, $projectExpense)
    {
        $projectExpense = $this->resolveExpense($projectExpense, $request);

        if (! $projectExpense) {
            return redirect()
                ->route('project-expenses.index')
                ->with('error', 'Expense record was not found or may have been deleted. Please open it again from the list.');
        }

        abort_if(strtolower($projectExpense->status ?? 'pending') === 'liquidated', 403, 'Liquidated expenses can no longer be edited.');

        $validated = $this->validatedExpense($request);

        DB::transaction(function () use ($request, $validated, $projectExpense) {
            $projectExpense->fill([
                'project_rfp_id' => $validated['project_rfp_id'],
                'store_name_id' => $validated['store_name_id'],
                'receipts_total_amount' => $this->receiptTotal($validated['receipts']),
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'pending',
                'updated_by' => auth()->id(),
            ]);

            $this->handleAttachment($request, $projectExpense);
            $projectExpense->save();
            $this->syncReceipts($projectExpense, $validated['receipts']);
        });

        return redirect()
            ->route('project-expenses.show', $projectExpense->id)
            ->with('success', 'Expense updated successfully.');
    }


    public function destroy(Request $request, $projectExpense)
    {
        $projectExpense = $this->resolveExpense($projectExpense, $request);

        if (! $projectExpense) {
            $message = 'Expense record was not found or may have been deleted.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $message], 404);
            }

            return redirect()->route('project-expenses.index')->with('error', $message);
        }

        abort_if(strtolower($projectExpense->status ?? 'pending') === 'liquidated', 422, 'Liquidated expenses can no longer be deleted.');

        if ($projectExpense->attachment_path) {
            Storage::disk('public')->delete($projectExpense->attachment_path);
        }

        $projectExpense->delete();
        $message = 'Expense deleted successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => true, 'message' => $message]);
        }

        return redirect()->route('project-expenses.index')->with('success', $message);
    }


    private function resolveExpense($expenseId, Request $request): ?ProjectExpense
    {
        $expense = ProjectExpense::find($expenseId);

        if ($expense) {
            return $expense;
        }

        if ($request->filled('cv') && $request->filled('store')) {
            return ProjectExpense::query()
                ->where('project_rfp_id', $request->query('cv'))
                ->where('store_name_id', $request->query('store'))
                ->latest('project_expenses.id')
                ->first();
        }

        return null;
    }

    private function releasedCvRecords()
    {
        return ProjectRfp::query()
            ->where('status', 'released')
            ->whereNotNull('cash_voucher_no')
            ->where('cash_voucher_no', '<>', '')
            ->orderByDesc('date_released')
            ->orderByDesc('project_rfps.id');
    }

    private function validatedExpense(Request $request): array
    {
        return $request->validate([
            'project_rfp_id' => ['required', 'exists:project_rfps,id'],
            'store_name_id' => ['required', 'exists:store_names,id'],
            'receipts' => ['required', 'array', 'min:1'],
            'receipts.*.store_receipt_no' => ['required', 'string', 'max:255'],
            'receipts.*.store_receipt_date' => ['required', 'date'],
            'receipts.*.receipts_total_amount' => ['required', 'numeric', 'min:0'],
            'receipts.*.remarks' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function receiptTotal(array $receipts): float
    {
        return collect($receipts)->sum(fn ($receipt) => (float) ($receipt['receipts_total_amount'] ?? 0));
    }

    private function syncReceipts(ProjectExpense $expense, array $receipts): void
    {
        $expense->receipts()->delete();
        foreach ($receipts as $receipt) {
            $expense->receipts()->create([
                'store_receipt_no' => $receipt['store_receipt_no'],
                'store_receipt_date' => $receipt['store_receipt_date'],
                'receipts_total_amount' => $receipt['receipts_total_amount'],
                'remarks' => $receipt['remarks'] ?? null,
            ]);
        }
    }

    private function handleAttachment(Request $request, ProjectExpense $expense): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }

        if ($expense->exists && $expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $file = $request->file('attachment');
        $path = $file->store('project-expenses', 'public');

        $expense->attachment_path = $path;
        $expense->attachment_original_name = $file->getClientOriginalName();
        $expense->attachment_mime_type = $file->getMimeType();
        $expense->attachment_size = $file->getSize();
    }
}
