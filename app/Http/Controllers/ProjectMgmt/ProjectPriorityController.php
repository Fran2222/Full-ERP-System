<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectPriority;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectPriorityController extends Controller
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
        return view('modules.project-mgmt.project-priorities.index');
    }

    public function list(Request $request)
    {
        $projectPriorities = ProjectPriority::query()
            ->select('project_priorities.*');

        if ($request->filled('status')) {
            $projectPriorities->where('project_priorities.status', $request->status);
        }

        return DataTables::eloquent($projectPriorities)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('project_priorities.code', 'ILIKE', "%{$search}%")
                        ->orWhere('project_priorities.name', 'ILIKE', "%{$search}%")
                        ->orWhere('project_priorities.description', 'ILIKE', "%{$search}%")
                        ->orWhere('project_priorities.status', 'ILIKE', "%{$search}%");
                });
            })

            ->addIndexColumn()

            ->addColumn('created_at_formatted', function ($projectPriority) {
                return optional($projectPriority->created_at)->format('M d, Y') ?: '-';
            })

            ->addColumn('show_url', function ($projectPriority) {
                return route('project-priorities.show', $projectPriority->id);
            })

            ->addColumn('action', function ($projectPriority) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('project-priorities.edit', $projectPriority->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('project-priorities.destroy', $projectPriority->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $projectPriority->name,
                    'delete-project-priority',
                    'Edit Project Priority',
                    'Delete Project Priority'
                );
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('modules.project-mgmt.project-priorities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_priorities,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:1', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ProjectPriority::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-priorities.index')
            ->with('success', 'Project priority created successfully.');
    }

    public function show(ProjectPriority $projectPriority)
    {
        return view('modules.project-mgmt.project-priorities.show', compact('projectPriority'));
    }

    public function edit(ProjectPriority $projectPriority)
    {
        return view('modules.project-mgmt.project-priorities.edit', compact('projectPriority'));
    }

    public function update(Request $request, ProjectPriority $projectPriority)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_priorities,code,' . $projectPriority->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:1', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $projectPriority->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-priorities.index')
            ->with('success', 'Project priority updated successfully.');
    }

    public function destroy(Request $request, ProjectPriority $projectPriority)
    {
        if ($projectPriority->projects()->exists()) {
            $message = 'Cannot delete this project priority because it is already used by projects.';

            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('project-priorities.index')
                ->with('error', $message);
        }

        $projectPriority->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Project priority deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-priorities.index')
            ->with('success', 'Project priority deleted successfully.');
    }
}