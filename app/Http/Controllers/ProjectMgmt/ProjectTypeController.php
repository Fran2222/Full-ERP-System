<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\ProjectType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectTypeController extends Controller
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
        return view('modules.project-mgmt.project-types.index');
    }

    public function list(Request $request)
    {
        $projectTypes = ProjectType::query()
            ->select('project_types.*');

        if ($request->filled('status')) {
            $projectTypes->where('project_types.status', $request->status);
        }

        return DataTables::eloquent($projectTypes)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('project_types.code', 'ILIKE', "%{$search}%")
                        ->orWhere('project_types.name', 'ILIKE', "%{$search}%")
                        ->orWhere('project_types.description', 'ILIKE', "%{$search}%")
                        ->orWhere('project_types.status', 'ILIKE', "%{$search}%");
                });
            })

            ->addIndexColumn()

            ->addColumn('created_at_formatted', function ($projectType) {
                return optional($projectType->created_at)->format('M d, Y') ?: '-';
            })

            ->addColumn('show_url', function ($projectType) {
                return route('project-types.show', $projectType->id);
            })

            ->addColumn('action', function ($projectType) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('project-types.edit', $projectType->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('project-types.destroy', $projectType->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $projectType->name,
                    'delete-project-type',
                    'Edit Project Type',
                    'Delete Project Type'
                );
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('modules.project-mgmt.project-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        ProjectType::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-types.index')
            ->with('success', 'Project type created successfully.');
    }

    public function show(ProjectType $projectType)
    {
        return view('modules.project-mgmt.project-types.show', compact('projectType'));
    }

    public function edit(ProjectType $projectType)
    {
        return view('modules.project-mgmt.project-types.edit', compact('projectType'));
    }

    public function update(Request $request, ProjectType $projectType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_types,code,' . $projectType->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $projectType->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('project-types.index')
            ->with('success', 'Project type updated successfully.');
    }

    public function destroy(Request $request, ProjectType $projectType)
    {
        if ($projectType->projects()->exists()) {
            $message = 'Cannot delete this project type because it is already used by projects.';

            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('project-types.index')
                ->with('error', $message);
        }

        $projectType->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Project type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-types.index')
            ->with('success', 'Project type deleted successfully.');
    }
}