<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectRfp;
use App\Models\ProjectMgmt\ProjectRfpApproval;
use App\Models\ProjectMgmt\RfpType;
use App\Models\ProjectMgmt\DocumentControl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProjectRfpController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show', 'projectDetails']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update', 'approve', 'reject', 'release']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        $types = RfpType::where('status', 'active')->orderBy('name')->get();
        return view('modules.project-mgmt.rfps.index', compact('types'));
    }

    public function list(Request $request)
    {
        $rfps = ProjectRfp::query()
            ->with(['type', 'project', 'client', 'requestedBy'])
            ->select('project_rfps.*');

        if ($request->filled('rfp_type_id')) {
            $rfps->where('rfp_type_id', $request->rfp_type_id);
        }

        if ($request->filled('status')) {
            $rfps->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $rfps->whereDate('date_requested', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $rfps->whereDate('date_requested', '<=', $request->date_to);
        }

        if ($request->filled('modified')) {
            $this->applyModifiedFilter($rfps, $request->modified);
        }

        return DataTables::eloquent($rfps)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('rfp_code', 'ILIKE', "%{$search}%")
                        ->orWhere('payee_name', 'ILIKE', "%{$search}%")
                        ->orWhere('project_code_snapshot', 'ILIKE', "%{$search}%")
                        ->orWhere('project_name_snapshot', 'ILIKE', "%{$search}%")
                        ->orWhere('client_name_snapshot', 'ILIKE', "%{$search}%")
                        ->orWhere('cash_voucher_no', 'ILIKE', "%{$search}%")
                        ->orWhere('notes', 'ILIKE', "%{$search}%")
                        ->orWhere('status', 'ILIKE', "%{$search}%")
                        ->orWhereHas('type', function ($type) use ($search) {
                            $type->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        });
                });
            })
            ->addColumn('display_code', fn ($rfp) => $this->formatRfpCode($rfp->rfp_code))
            ->addColumn('type_name', fn ($rfp) => optional($rfp->type)->name ?? '-')
            ->addColumn('project_label', fn ($rfp) => trim(($rfp->project_code_snapshot ?: 'NO-CODE') . ' - ' . ($rfp->project_name_snapshot ?: '-')))
            ->addColumn('client_name', fn ($rfp) => $rfp->client_name_snapshot ?: '-')
            ->addColumn('requested_amount_formatted', fn ($rfp) => '₱ ' . number_format((float) $rfp->requested_total_amount, 2))
            ->addColumn('released_amount_formatted', fn ($rfp) => $rfp->actual_released_amount !== null ? '₱ ' . number_format((float) $rfp->actual_released_amount, 2) : '-')
            ->addColumn('date_requested_formatted', fn ($rfp) => optional($rfp->date_requested)->format('M d, Y') ?: '-')
            ->addColumn('date_released_formatted', fn ($rfp) => optional($rfp->date_released)->format('M d, Y') ?: '-')
            ->addColumn('status_badge', fn ($rfp) => $this->statusBadge($rfp->status))
            ->addColumn('show_url', fn ($rfp) => route('project-rfps.show', $rfp->id))
            ->addColumn('action', function ($rfp) {
                $editUrl = (auth()->user()->can('projects_mgmt.edit') && $rfp->status === 'pending') ? route('project-rfps.edit', $rfp->id) : null;
                $deleteUrl = auth()->user()->can('projects_mgmt.delete') ? route('project-rfps.destroy', $rfp->id) : null;
                $previewUrl = route('project-rfps.show', $rfp->id) . '?preview=1';

                $previewButton = '
                    <a href="' . e($previewUrl) . '"
                       class="btn btn-sm btn-info d-inline-flex align-items-center justify-content-center"
                       title="Preview / Print RFP"
                       aria-label="Preview / Print RFP"
                       style="width: 34px; height: 30px; padding: 0; border-radius: 6px;">
                        <i class="icon d-inline-flex align-items-center justify-content-center" style="line-height: 1;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2 12C2 12 5.6 5 12 5C18.4 5 22 12 22 12C22 12 18.4 19 12 19C5.6 19 2 12 2 12Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </i>
                    </a>
                ';

                return '<div class="d-flex align-items-center justify-content-end gap-2">' .
                    $previewButton .
                    ActionButtonHelper::editDelete($editUrl, $deleteUrl, $rfp->rfp_code, 'delete-rfp', 'Edit RFP', 'Delete RFP') .
                    '</div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $types = RfpType::where('status', 'active')->orderBy('name')->get();
        $projects = Project::with('client')->orderBy('code')->orderBy('name')->get();
        $approvers = User::orderBy('first_name')->orderBy('last_name')->get();
        $activeDocuments = $this->activeRfpDocuments();
        return view('modules.project-mgmt.rfps.create', compact('types', 'projects', 'approvers', 'activeDocuments'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedRfp($request);

        DB::transaction(function () use ($request, $validated) {
            $type = RfpType::lockForUpdate()->findOrFail($validated['rfp_type_id']);
            $project = ! empty($validated['project_id']) ? Project::with('client')->findOrFail($validated['project_id']) : null;
            $lastSequence = ProjectRfp::where('rfp_type_id', $type->id)
                ->orderByDesc('sequence_no')
                ->value('sequence_no');
            $sequence = ((int) $lastSequence) + 1;
            $rfpCode = $type->code . '-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);

            $rfp = ProjectRfp::create([
                'rfp_type_id' => $type->id,
                'rfp_code' => $rfpCode,
                'sequence_no' => $sequence,
                'date_requested' => $validated['date_requested'],
                'requested_by' => auth()->id(),
                'payee_name' => $validated['payee_name'] ?? $this->currentUserDisplayName(),
                'project_id' => optional($project)->id,
                'project_code_snapshot' => optional($project)->code,
                'project_name_snapshot' => optional($project)->name,
                'project_amount_snapshot' => optional($project)->amount,
                'client_id' => optional(optional($project)->client)->id,
                'client_name_snapshot' => optional(optional($project)->client)->name,
                'client_contact_snapshot' => optional(optional($project)->client)->contact_person ?: optional(optional($project)->client)->contact_number,
                'client_address_snapshot' => optional(optional($project)->client)->address,
                'request_details' => $validated['request_details'],
                'requested_total_amount' => $validated['requested_total_amount'],
                'approved_by' => $validated['approved_by'] ?? null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $items = $request->input('items', []);
            foreach ($items as $index => $item) {
                if (empty($item['description'])) {
                    continue;
                }
                $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
                $cost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : 0;
                $total = isset($item['total_amount']) && $item['total_amount'] !== '' ? (float) $item['total_amount'] : ($qty * $cost);

                $rfp->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'total_amount' => $total,
                    'sort_order' => $index + 1,
                ]);
            }

            $this->createApprovalSteps($rfp);
            $this->storeAttachments($request, $rfp);
        });

        return redirect()->route('project-rfps.index')->with('success', 'RFP created successfully.');
    }

    public function show(ProjectRfp $projectRfp)
    {
        $projectRfp->load(['type', 'items', 'approvals.approver', 'attachments', 'requestedBy', 'approvedBy', 'releasedBy']);
        $documentControl = $this->activeRfpDocumentFor($projectRfp->type);
        return view('modules.project-mgmt.rfps.show', ['rfp' => $projectRfp, 'documentControl' => $documentControl]);
    }

    public function edit(ProjectRfp $projectRfp)
    {
        abort_if($projectRfp->status !== 'pending', 403, 'Only pending RFP records can be edited.');
        $types = RfpType::where('status', 'active')->orderBy('name')->get();
        $projects = Project::with('client')->orderBy('code')->orderBy('name')->get();
        $approvers = User::orderBy('first_name')->orderBy('last_name')->get();
        $projectRfp->load('items');
        $activeDocuments = $this->activeRfpDocuments();
        return view('modules.project-mgmt.rfps.edit', compact('projectRfp', 'types', 'projects', 'approvers', 'activeDocuments'));
    }

    public function update(Request $request, ProjectRfp $projectRfp)
    {
        abort_if($projectRfp->status !== 'pending', 403, 'Only pending RFP records can be edited.');
        $validated = $this->validatedRfp($request, $projectRfp);

        DB::transaction(function () use ($request, $validated, $projectRfp) {
            $project = ! empty($validated['project_id']) ? Project::with('client')->findOrFail($validated['project_id']) : null;

            $projectRfp->update([
                'rfp_type_id' => $validated['rfp_type_id'],
                'date_requested' => $validated['date_requested'],
                'payee_name' => $validated['payee_name'] ?? $this->currentUserDisplayName(),
                'project_id' => optional($project)->id,
                'project_code_snapshot' => optional($project)->code,
                'project_name_snapshot' => optional($project)->name,
                'project_amount_snapshot' => optional($project)->amount,
                'client_id' => optional(optional($project)->client)->id,
                'client_name_snapshot' => optional(optional($project)->client)->name,
                'client_contact_snapshot' => optional(optional($project)->client)->contact_person ?: optional(optional($project)->client)->contact_number,
                'client_address_snapshot' => optional(optional($project)->client)->address,
                'request_details' => $validated['request_details'],
                'requested_total_amount' => $validated['requested_total_amount'],
                'approved_by' => $validated['approved_by'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $projectRfp->items()->delete();
            foreach ($request->input('items', []) as $index => $item) {
                if (empty($item['description'])) {
                    continue;
                }
                $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
                $cost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : 0;
                $total = isset($item['total_amount']) && $item['total_amount'] !== '' ? (float) $item['total_amount'] : ($qty * $cost);
                $projectRfp->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? null,
                    'unit' => $item['unit'] ?? null,
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'total_amount' => $total,
                    'sort_order' => $index + 1,
                ]);
            }

            $this->storeAttachments($request, $projectRfp);
        });

        return redirect()->route('project-rfps.show', $projectRfp->id)->with('success', 'RFP updated successfully.');
    }

    public function approve(ProjectRfp $projectRfp)
    {
        abort_if(! in_array($projectRfp->status, ['pending'], true), 422, 'Only pending RFP records can be approved.');
        $projectRfp->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(), 'updated_by' => auth()->id()]);
        $this->markApproval($projectRfp, 'Accounting Review', 'approved');
        $this->markApproval($projectRfp, 'Approved', 'approved');
        return back()->with('success', 'RFP approved successfully.');
    }

    public function reject(Request $request, ProjectRfp $projectRfp)
    {
        $validated = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:1000']]);
        abort_if(! in_array($projectRfp->status, ['pending'], true), 422, 'Only pending RFP records can be rejected.');
        $reason = $validated['rejection_reason'] ?? null;
        $existingNotes = trim((string) ($projectRfp->notes ?? ''));
        $rejectionNote = $reason ? 'Rejected Reason: ' . $reason : 'Rejected without stated reason.';

        $projectRfp->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'notes' => $existingNotes !== '' ? $existingNotes . PHP_EOL . $rejectionNote : $rejectionNote,
            'updated_by' => auth()->id(),
        ]);

        $this->markApproval($projectRfp, 'Accounting Review', 'rejected', $reason);
        return back()->with('success', 'RFP rejected successfully.');
    }

    public function release(Request $request, ProjectRfp $projectRfp)
    {
        abort_if($projectRfp->status !== 'approved', 422, 'Only approved RFP records can be released.');
        $validated = $request->validate([
            'actual_released_amount' => ['required', 'numeric', 'min:0'],
            'date_released' => ['required', 'date'],
            'cash_voucher_no' => ['required', 'regex:/^[0-9]{1,10}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $projectRfp->update([
            'actual_released_amount' => $validated['actual_released_amount'],
            'date_released' => $validated['date_released'],
            'cash_voucher_no' => $validated['cash_voucher_no'],
            'status' => 'released',
            'released_by' => auth()->id(),
            'released_at' => now(),
            'notes' => $validated['notes'] ?? $projectRfp->notes,
            'updated_by' => auth()->id(),
        ]);
        $this->markApproval($projectRfp, 'Released', 'approved', $validated['notes'] ?? null);

        return back()->with('success', 'Payment release details saved successfully.');
    }

    public function destroy(Request $request, ProjectRfp $projectRfp)
    {
        $projectRfp->delete();
        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'RFP deleted successfully.']);
        }
        return redirect()->route('project-rfps.index')->with('success', 'RFP deleted successfully.');
    }

    public function projectDetails(Project $project)
    {
        $project->load('client');
        return response()->json([
            'id' => $project->id,
            'code' => $project->code,
            'name' => $project->name,
            'amount' => $project->amount !== null ? number_format((float) $project->amount, 2, '.', '') : '',
            'amount_formatted' => $project->amount !== null ? '₱ ' . number_format((float) $project->amount, 2) : 'Not set',
            'client_id' => optional(optional($project)->client)->id,
            'client_name' => optional(optional($project)->client)->name ?: '',
            'client_contact' => optional(optional($project)->client)->contact_person ?: optional(optional($project)->client)->contact_number ?: '',
            'client_address' => optional(optional($project)->client)->address ?: '',
        ]);
    }

    private function validatedRfp(Request $request, ?ProjectRfp $rfp = null): array
    {
        return $request->validate([
            'rfp_type_id' => ['required', 'exists:rfp_types,id'],
            'date_requested' => ['required', 'date'],
            'payee_name' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'request_details' => ['required', 'string', 'max:2000'],
            'requested_total_amount' => ['required', 'numeric', 'min:0'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);
    }



    private function activeRfpDocuments()
    {
        return DocumentControl::where('form_name', 'Request for Payment')
            ->where('status', 'active')
            ->get()
            ->mapWithKeys(function ($doc) {
                $type = strtoupper((string) $doc->type);
                return [$type => [
                    'document_no' => $doc->document_no,
                    'revision_no' => $doc->revision_no,
                    'effective_date' => optional($doc->effective_date)->format('M d, Y'),
                    'code' => $doc->sample_code,
                ]];
            });
    }

    private function activeRfpDocumentFor(?RfpType $type): ?DocumentControl
    {
        if (! $type) {
            return null;
        }

        $suffix = strtoupper(str_replace('RFP-', '', (string) $type->code));

        return DocumentControl::where('form_name', 'Request for Payment')
            ->where('status', 'active')
            ->where(function ($query) use ($suffix, $type) {
                $query->whereRaw('UPPER(type) = ?', [$suffix])
                    ->orWhereRaw('UPPER(code_prefix) = ?', [strtoupper((string) $type->code)]);
            })
            ->first();
    }

    private function currentUserDisplayName(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'Requester';
        }

        return trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
            ?: ($user->name ?? $user->email ?? 'Requester');
    }

    private function createApprovalSteps(ProjectRfp $rfp): void
    {
        $steps = [
            ['Requested', 'approved', auth()->id(), now()],
            ['Accounting Review', 'pending', $rfp->approved_by, null],
            ['Approved', 'pending', $rfp->approved_by, null],
            ['Released', 'pending', null, null],
        ];

        foreach ($steps as $index => $step) {
            $rfp->approvals()->create([
                'step_name' => $step[0],
                'status' => $step[1],
                'approver_id' => $step[2],
                'acted_at' => $step[3],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function markApproval(ProjectRfp $rfp, string $stepName, string $status, ?string $remarks = null): void
    {
        ProjectRfpApproval::where('project_rfp_id', $rfp->id)
            ->where('step_name', $stepName)
            ->update(['status' => $status, 'approver_id' => auth()->id(), 'remarks' => $remarks, 'acted_at' => now()]);
    }

    private function storeAttachments(Request $request, ProjectRfp $rfp): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('project-rfps/' . $rfp->id, 'public');
            $rfp->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function applyModifiedFilter($query, string $modified): void
    {
        if ($modified === 'today') {
            $query->whereDate('updated_at', today());
        } elseif ($modified === 'week') {
            $query->where('updated_at', '>=', now()->subDays(7));
        } elseif ($modified === 'month') {
            $query->where('updated_at', '>=', now()->subDays(30));
        }
    }

    private function formatRfpCode(?string $code): string
    {
        if (! $code) {
            return '-';
        }
        return preg_replace('/-(\d{6})$/', ' #$1', $code) ?: $code;
    }

    private function statusBadge(?string $status): string
    {
        $status = strtolower($status ?: 'pending');
        $classes = [
            'pending' => 'text-warning',
            'approved' => 'text-primary',
            'rejected' => 'text-danger',
            'released' => 'text-success',
            'liquidated' => 'text-info',
            'cancelled' => 'text-secondary',
        ];

        return '<span class="fw-semibold ' . ($classes[$status] ?? 'text-secondary') . '">' . e(ucfirst($status)) . '</span>';
    }
}
