<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectStatus;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectStatusController extends Controller
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
        return view('modules.project-mgmt.project-statuses.index');
    }

    public function list(Request $request)
    {
        $projectStatuses = ProjectStatus::query()
            ->select('project_statuses.*');

        if ($request->filled('status')) {
            $projectStatuses->where('project_statuses.status', $request->status);
        }

        return DataTables::eloquent($projectStatuses)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('project_statuses.code', 'ILIKE', "%{$search}%")
                        ->orWhere('project_statuses.name', 'ILIKE', "%{$search}%")
                        ->orWhere('project_statuses.description', 'ILIKE', "%{$search}%")
                        ->orWhere('project_statuses.status', 'ILIKE', "%{$search}%");
                });
            })
            ->addIndexColumn()
            ->addColumn('created_at_formatted', function ($projectStatus) {
                return optional($projectStatus->created_at)->format('M d, Y') ?: '-';
            })
            ->addColumn('show_url', function ($projectStatus) {
                return route('project-statuses.show', $projectStatus->id);
            })
            ->addColumn('action', function ($projectStatus) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('project-statuses.edit', $projectStatus->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('project-statuses.destroy', $projectStatus->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $projectStatus->name,
                    'delete-project-status',
                    'Edit Project Status',
                    'Delete Project Status'
                );
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('modules.project-mgmt.project-statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_statuses,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ProjectStatus::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-statuses.index')
            ->with('success', 'Project status created successfully.');
    }

    public function show(ProjectStatus $projectStatus)
    {
        return view('modules.project-mgmt.project-statuses.show', compact('projectStatus'));
    }

    public function edit(ProjectStatus $projectStatus)
    {
        return view('modules.project-mgmt.project-statuses.edit', compact('projectStatus'));
    }

    public function update(Request $request, ProjectStatus $projectStatus)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_statuses,code,' . $projectStatus->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $projectStatus->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-statuses.index')
            ->with('success', 'Project status updated successfully.');
    }

    public function destroy(Request $request, ProjectStatus $projectStatus)
    {
        if ($projectStatus->projects()->exists()) {
            $message = 'Cannot delete this project status because it is already used by projects.';

            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('project-statuses.index')
                ->with('error', $message);
        }

        $projectStatus->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Project status deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-statuses.index')
            ->with('success', 'Project status deleted successfully.');
    }
}