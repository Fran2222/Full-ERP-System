<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\DocumentControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DocumentControlController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store', 'newRevision']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update', 'publish']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        return view('modules.project-mgmt.document-controls.index');
    }

    public function list(Request $request)
    {
        $documents = DocumentControl::query()->select('document_controls.*');

        if ($request->filled('status')) {
            $documents->where('status', $request->status);
        }

        return DataTables::eloquent($documents)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('form_name', 'ILIKE', "%{$search}%")
                        ->orWhere('type', 'ILIKE', "%{$search}%")
                        ->orWhere('document_no', 'ILIKE', "%{$search}%")
                        ->orWhere('revision_no', 'ILIKE', "%{$search}%")
                        ->orWhere('status', 'ILIKE', "%{$search}%");
                });
            })
            ->addColumn('effective_date_formatted', fn ($doc) => optional($doc->effective_date)->format('M d, Y') ?: '-')
            ->addColumn('status_badge', function ($doc) {
                $status = strtolower((string) $doc->status);
                $class = match ($status) {
                    'active' => 'text-success',
                    'draft' => 'text-primary',
                    'archived' => 'text-secondary',
                    'inactive' => 'text-danger',
                    default => 'text-muted',
                };

                return '<span class="fw-semibold ' . $class . '">' . e(ucfirst($status ?: 'Unknown')) . '</span>';
            })
            ->addColumn('show_url', fn ($doc) => route('document-controls.show', $doc->id))
            ->addColumn('action', function ($doc) {
                $editUrl = auth()->user()->can('projects_mgmt.edit') ? route('document-controls.edit', $doc->id) : null;
                $deleteUrl = auth()->user()->can('projects_mgmt.delete') ? route('document-controls.destroy', $doc->id) : null;
                $html = '<div class="d-flex align-items-center justify-content-end gap-2">';

                if (auth()->user()->can('projects_mgmt.create')) {
                    $html .= '<form action="' . e(route('document-controls.new-revision', $doc->id)) . '" method="POST" class="m-0 d-inline-flex new-revision-form">' . csrf_field() . '
                        <button type="submit" class="btn btn-sm btn-light d-inline-flex align-items-center justify-content-center new-revision-btn" data-name="' . e($doc->document_no) . '" title="New Revision" style="width:34px;height:30px;padding:0;border-radius:6px;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M7 7H17M7 12H17M7 17H12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 17H22M19 14V20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 3H19C20.1 3 21 3.9 21 5V11.2M19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button></form>';
                }

                if (auth()->user()->can('projects_mgmt.edit') && strtolower((string) $doc->status) === 'draft') {
                    $html .= '<form action="' . e(route('document-controls.publish', $doc->id)) . '" method="POST" class="m-0 d-inline-flex publish-revision-form">' . csrf_field() . '
                        <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center justify-content-center publish-revision-btn" data-name="' . e($doc->document_no) . '" title="Publish Revision" style="width:34px;height:30px;padding:0;border-radius:6px;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M5 12L10 17L20 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button></form>';
                }

                $html .= ActionButtonHelper::editDelete($editUrl, $deleteUrl, $doc->document_no, 'delete-document-control', 'Edit Document', 'Delete Document');
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $documentControl = new DocumentControl([
            'revision_no' => '00',
            'effective_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        return view('modules.project-mgmt.document-controls.create', compact('documentControl'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateDocument($request);

        DocumentControl::create(array_merge($validated, [
            'module_name' => $validated['form_name'],
            'revision_no' => '00',
            'type' => strtoupper(trim((string) $validated['type'])),
            'document_no' => strtoupper(trim((string) $validated['document_no'])),
            'code_prefix' => strtoupper(trim((string) ($validated['code_prefix'] ?? ('RFP-' . $validated['type'])))),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        return redirect()->route('document-controls.index')->with('success', 'Document control record created successfully.');
    }

    public function show(DocumentControl $documentControl)
    {
        $documentControl->load(['createdBy', 'updatedBy']);
        return view('modules.project-mgmt.document-controls.show', compact('documentControl'));
    }

    public function edit(DocumentControl $documentControl)
    {
        return view('modules.project-mgmt.document-controls.edit', compact('documentControl'));
    }

    public function update(Request $request, DocumentControl $documentControl)
    {
        $validated = $this->validateDocument($request, $documentControl);

        $documentControl->update(array_merge($validated, [
            'module_name' => $validated['form_name'],
            'type' => strtoupper(trim((string) $validated['type'])),
            'document_no' => strtoupper(trim((string) $validated['document_no'])),
            'code_prefix' => strtoupper(trim((string) ($validated['code_prefix'] ?? ('RFP-' . $validated['type'])))),
            'updated_by' => auth()->id(),
        ]));

        return redirect()->route('document-controls.show', $documentControl->id)->with('success', 'Document control record updated successfully.');
    }

    public function newRevision(DocumentControl $documentControl)
    {
        $newRevisionNo = str_pad((string) (((int) $documentControl->revision_no) + 1), 2, '0', STR_PAD_LEFT);

        $draft = DocumentControl::create([
            'module_name' => $documentControl->module_name ?: $documentControl->form_name,
            'form_name' => $documentControl->form_name,
            'type' => $documentControl->type,
            'document_no' => $this->nextDocumentNo($documentControl->document_no),
            'revision_no' => $newRevisionNo,
            'effective_date' => now()->toDateString(),
            'status' => 'draft',
            'code_prefix' => $documentControl->code_prefix,
            'revision_notes' => 'Created from Revision ' . $documentControl->revision_no . '.',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('document-controls.edit', $draft->id)->with('success', 'New draft revision created. Review and publish when ready.');
    }

    public function publish(DocumentControl $documentControl)
    {
        DB::transaction(function () use ($documentControl) {
            DocumentControl::where('module_name', $documentControl->module_name ?: $documentControl->form_name)
                ->where('form_name', $documentControl->form_name)
                ->where('type', $documentControl->type)
                ->where('status', 'active')
                ->where('id', '!=', $documentControl->id)
                ->update(['status' => 'archived', 'updated_by' => auth()->id(), 'updated_at' => now()]);

            $documentControl->update([
                'status' => 'active',
                'updated_by' => auth()->id(),
            ]);
        });

        return redirect()->route('document-controls.index')->with('success', 'Revision published. Previous active revision was archived automatically.');
    }

    public function destroy(Request $request, DocumentControl $documentControl)
    {
        if (strtolower((string) $documentControl->status) === 'active') {
            $message = 'Cannot delete an active document revision. Create/publish a new revision first or set this record inactive.';
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => $message], 422);
            }
            return redirect()->route('document-controls.index')->with('error', $message);
        }

        $documentControl->delete();

        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => 'Document control record deleted successfully.']);
        }

        return redirect()->route('document-controls.index')->with('success', 'Document control record deleted successfully.');
    }

    private function validateDocument(Request $request, ?DocumentControl $documentControl = null): array
    {
        return $request->validate([
            'form_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:80'],
            'document_no' => ['required', 'string', 'max:100', Rule::unique('document_controls', 'document_no')->ignore(optional($documentControl)->id)],
            'effective_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,inactive,archived'],
            'code_prefix' => ['nullable', 'string', 'max:80'],
            'revision_notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function nextDocumentNo(string $documentNo): string
    {
        if (preg_match('/^(.*?)(\d+)$/', $documentNo, $matches)) {
            $prefix = $matches[1];
            $number = $matches[2];
            return $prefix . str_pad((string) (((int) $number) + 1), strlen($number), '0', STR_PAD_LEFT);
        }

        return $documentNo . '-001';
    }
}
