<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\ProjectMgmt\Client;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectPriority;
use App\Models\ProjectMgmt\ProjectStatus;
use App\Models\ProjectMgmt\ProjectMilestone;
use App\Models\ProjectMgmt\ProjectType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProjectsController extends Controller
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
        $projectTypes = ProjectType::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('modules.project-mgmt.projects.index', compact('projectTypes'));
    }

    public function list(Request $request)
    {
        $user = auth()->user();

        $projects = Project::query()
            ->select('projects.*')
            ->with([
                'branch',
                'department',
                'users',
                'client',
                'type',
                'priority',
                'projectStatus',
                'manager',
            ]);

        if (! $user->hasRole('super-admin')) {
            $projects->where(function ($query) use ($user) {
                $query->where('project_manager_id', $user->id)
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            });
        }

        if ($request->filled('project_type_id')) {
            $projects->where('project_type_id', $request->project_type_id);
        }

        return DataTables::eloquent($projects)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($q) use ($search) {
                    $q->where('projects.name', 'ILIKE', "%{$search}%")
                        ->orWhere('projects.code', 'ILIKE', "%{$search}%")
                        ->orWhere('projects.description', 'ILIKE', "%{$search}%")
                        ->orWhere('projects.location', 'ILIKE', "%{$search}%")
                        ->orWhere('projects.project_type', 'ILIKE', "%{$search}%")
                        ->orWhere('projects.status', 'ILIKE', "%{$search}%")
                        ->orWhereHas('client', function ($client) use ($search) {
                            $client->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('type', function ($type) use ($search) {
                            $type->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('priority', function ($priority) use ($search) {
                            $priority->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('projectStatus', function ($status) use ($search) {
                            $status->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('branch', function ($branch) use ($search) {
                            $branch->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('department', function ($department) use ($search) {
                            $department->where('name', 'ILIKE', "%{$search}%")
                                ->orWhere('code', 'ILIKE', "%{$search}%");
                        })
                        ->orWhereHas('users', function ($users) use ($search) {
                            $users->where('first_name', 'ILIKE', "%{$search}%")
                                ->orWhere('last_name', 'ILIKE', "%{$search}%")
                                ->orWhere('email', 'ILIKE', "%{$search}%");
                        });
                });
            })
            ->addIndexColumn()
            ->editColumn('description', fn ($project) => $project->description ?: '-')
            ->addColumn('created_at_formatted', fn ($project) => optional($project->created_at)->format('M d, Y') ?: '-')
            ->addColumn('show_url', fn ($project) => route('projects.show', $project->id))
            ->addColumn('amount_formatted', function ($project) {
                return $project->amount !== null
                    ? '₱ ' . number_format((float) $project->amount, 2)
                    : 'Not set';
            })
            ->addColumn('project_type_label', fn ($project) => optional($project->type)->name ?? ucfirst($project->project_type ?? '-'))
            ->addColumn('client_name', fn ($project) => optional($project->client)->name ?? '-')
            ->addColumn('priority_name', fn ($project) => optional($project->priority)->name ?? '-')
            ->addColumn('manager_name', function ($project) {
                if (! $project->manager) {
                    return '-';
                }

                return trim(($project->manager->first_name ?? '') . ' ' . ($project->manager->last_name ?? '')) ?: $project->manager->email;
            })
            ->editColumn('status', function ($project) {
                return $this->getComputedProjectStatusName((int) $project->id);
            })
            ->addColumn('status_badge', function ($project) {
                $statusName = $this->getComputedProjectStatusName((int) $project->id);

                $statusClass = $this->getProjectStatusTextClass($statusName);

                return '<span class="fw-semibold ' . $statusClass . '">' . e($statusName) . '</span>';
            })
            ->addColumn('action', function ($project) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('projects.edit', $project->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('projects.destroy', $project->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $project->name,
                    'delete-project',
                    'Edit Project',
                    'Delete Project'
                );
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $authUser = auth()->user();
        $isSuperAdmin = $authUser->hasRole('super-admin');

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('first_name')->orderBy('last_name')->get();

        $projectManagers = $isSuperAdmin
            ? User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['project manager', 'project-manager', 'Project Manager']);
                })
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
            : collect([$authUser]);

        if ($isSuperAdmin && $projectManagers->isEmpty()) {
            $projectManagers = User::orderBy('first_name')->orderBy('last_name')->get();
        }

        $currentProjectManagerId = old('project_manager_id', $authUser->id);
        $currentProjectManagerName = trim(($authUser->first_name ?? '') . ' ' . ($authUser->last_name ?? '')) ?: $authUser->email;

        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $projectTypes = ProjectType::where('status', 'active')->orderBy('name')->get();
        $priorities = ProjectPriority::where('status', 'active')->orderBy('level')->get();

        return view('modules.project-mgmt.projects.create', compact(
            'branches',
            'departments',
            'users',
            'projectManagers',
            'isSuperAdmin',
            'currentProjectManagerId',
            'currentProjectManagerName',
            'clients',
            'projectTypes',
            'priorities',
        ));
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();
        $isSuperAdmin = $authUser->hasRole('super-admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:300'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['required', 'exists:clients,id'],
            'project_type_id' => ['required', 'exists:project_types,id'],
            'priority_id' => ['required', 'exists:project_priorities,id'],
            'project_manager_id' => [$isSuperAdmin ? 'required' : 'nullable', 'exists:users,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:180'],
            'start_date' => ['required', 'date'],
            'target_end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $projectType = ProjectType::find($validated['project_type_id']);
        $projectStatus = $this->getAutoProjectStatusByProgress(0);

        $projectManagerId = $isSuperAdmin
            ? (int) $validated['project_manager_id']
            : (int) $authUser->id;

        $assignedUsers = collect($validated['users'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->push($projectManagerId)
            ->unique()
            ->values()
            ->all();

        $project = Project::create([
            'code' => $this->generateProjectCode(),
            'name' => $validated['name'],
            'amount' => $validated['amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'client_id' => $validated['client_id'],
            'project_type_id' => $validated['project_type_id'],
            'priority_id' => $validated['priority_id'],
            'status_id' => optional($projectStatus)->id,
            'project_manager_id' => $projectManagerId,
            'branch_id' => $validated['branch_id'],
            'department_id' => $validated['department_id'],
            'location' => $validated['location'] ?? null,
            'start_date' => $validated['start_date'],
            'target_end_date' => $validated['target_end_date'],
            'end_date' => $validated['end_date'] ?? $validated['target_end_date'],
            'progress_percent' => 0,
            'project_type' => $projectType ? strtolower($projectType->name) : null,
            'status' => strtolower($this->getAutoProjectStatusNameByProgress(0)),
            'created_by' => auth()->id(),
        ]);

        $project->users()->sync($assignedUsers);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load([
            'branch',
            'department',
            'users',
            'client',
            'type',
            'priority',
            'projectStatus',
            'manager',
            'creator',
            'milestones' => function ($query) {
                $query->orderBy('sort_order')
                    ->orderBy('start_date')
                    ->orderBy('id');
            },
            'milestones.creator',
        ])->loadCount(['tasks', 'files']);

        $user = auth()->user();

        if (! $user->hasRole('super-admin')) {
            $isManager = $project->project_manager_id === $user->id;
            $isAssigned = $project->users->contains('id', $user->id);

            if (! $isManager && ! $isAssigned) {
                abort(403);
            }
        }

        $userName = function ($user): string {
            if (! $user) {
                return 'System';
            }

            return trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->email ?? 'System');
        };

        $projectActivities = collect();

        if ($project->created_at) {
            $projectActivities->push([
                'title' => 'Project Created',
                'description' => 'Project record was created: ' . $project->code . ' - ' . $project->name . '.',
                'by' => $userName($project->creator),
                'date' => $project->created_at,
            ]);
        }

        if ($project->updated_at && $project->created_at && $project->updated_at->gt($project->created_at)) {
            $projectActivities->push([
                'title' => 'Project Updated',
                'description' => 'Project details were recently updated.',
                'by' => $userName($project->manager),
                'date' => $project->updated_at,
            ]);
        }

        foreach ($project->milestones as $milestone) {
            if ($milestone->created_at) {
                $projectActivities->push([
                    'title' => 'Milestone Added',
                    'description' => 'Milestone added: ' . $milestone->title . '.',
                    'by' => $userName($milestone->creator),
                    'date' => $milestone->created_at,
                ]);
            }

            if ($milestone->is_completed && $milestone->completed_at) {
                $projectActivities->push([
                    'title' => 'Milestone Completed',
                    'description' => 'Milestone completed: ' . $milestone->title . '.',
                    'by' => $userName($milestone->creator),
                    'date' => $milestone->completed_at,
                ]);
            }
        }

        $projectActivities = $projectActivities
            ->sortByDesc(fn ($activity) => optional($activity['date'])->timestamp ?? 0)
            ->values();

        return view('modules.project-mgmt.projects.show', compact('project', 'projectActivities'));
    }

    public function edit(Project $project)
    {
        $project->load(['users']);

        $authUser = auth()->user();
        $isSuperAdmin = $authUser->hasRole('super-admin');

        if (! $isSuperAdmin && (int) $project->project_manager_id !== (int) $authUser->id) {
            abort(403);
        }

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('first_name')->orderBy('last_name')->get();

        $projectManagers = $isSuperAdmin
            ? User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['project manager', 'project-manager', 'Project Manager']);
                })
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
            : collect([$authUser]);

        if ($isSuperAdmin && $projectManagers->isEmpty()) {
            $projectManagers = User::orderBy('first_name')->orderBy('last_name')->get();
        }

        $managerUser = $project->manager ?: $authUser;
        $currentProjectManagerId = old('project_manager_id', $isSuperAdmin ? $project->project_manager_id : $authUser->id);
        $currentProjectManagerName = trim(($managerUser->first_name ?? '') . ' ' . ($managerUser->last_name ?? '')) ?: $managerUser->email;

        $clients = Client::where(function ($query) use ($project) {
            $query->where('status', 'active')
                ->orWhere('id', $project->client_id);
        })->orderBy('name')->get();

        $projectTypes = ProjectType::where(function ($query) use ($project) {
            $query->where('status', 'active')
                ->orWhere('id', $project->project_type_id);
        })->orderBy('name')->get();

        $priorities = ProjectPriority::where(function ($query) use ($project) {
            $query->where('status', 'active')
                ->orWhere('id', $project->priority_id);
        })->orderBy('level')->get();

        return view('modules.project-mgmt.projects.edit', compact(
            'project',
            'branches',
            'departments',
            'users',
            'projectManagers',
            'isSuperAdmin',
            'currentProjectManagerId',
            'currentProjectManagerName',
            'clients',
            'projectTypes',
            'priorities',
        ));
    }

    public function update(Request $request, Project $project)
    {
        $authUser = auth()->user();
        $isSuperAdmin = $authUser->hasRole('super-admin');

        if (! $isSuperAdmin && (int) $project->project_manager_id !== (int) $authUser->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:300'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'client_id' => ['required', 'exists:clients,id'],
            'project_type_id' => ['required', 'exists:project_types,id'],
            'priority_id' => [
                'required',
                Rule::exists('project_priorities', 'id')->where(function ($query) use ($project) {
                    $query->where('status', 'active')
                        ->orWhere('id', $project->priority_id);
                }),
            ],
            'project_manager_id' => [$isSuperAdmin ? 'required' : 'nullable', 'exists:users,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:180'],
            'start_date' => ['required', 'date'],
            'target_end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $projectType = ProjectType::find($validated['project_type_id']);
        $progress = $this->calculateProjectProgress((int) $project->id);
        $projectStatus = $this->getAutoProjectStatusByProgress($progress);

        $projectManagerId = $isSuperAdmin
            ? (int) $validated['project_manager_id']
            : (int) $authUser->id;

        $assignedUsers = collect($validated['users'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->push($projectManagerId)
            ->unique()
            ->values()
            ->all();

        $project->update([
            'name' => $validated['name'],
            'amount' => $validated['amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'client_id' => $validated['client_id'],
            'project_type_id' => $validated['project_type_id'],
            'priority_id' => $validated['priority_id'],
            'status_id' => optional($projectStatus)->id,
            'project_manager_id' => $projectManagerId,
            'branch_id' => $validated['branch_id'],
            'department_id' => $validated['department_id'],
            'location' => $validated['location'] ?? null,
            'start_date' => $validated['start_date'],
            'target_end_date' => $validated['target_end_date'],
            'end_date' => $validated['actual_end_date'] ?? $validated['target_end_date'],
            'progress_percent' => $progress,
            'project_type' => $projectType ? strtolower($projectType->name) : null,
            'status' => strtolower($this->getAutoProjectStatusNameByProgress($progress)),
        ]);

        $project->users()->sync($assignedUsers);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->users()->detach();
        $project->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Project deleted successfully.',
            ]);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    private function generateProjectCode(): string
    {
        $year = now()->year;
        $prefix = 'PRJ-' . $year . '-';

        $lastProject = Project::where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($lastProject && preg_match('/^PRJ-' . $year . '-(\d+)$/', $lastProject->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function calculateProjectProgress(int $projectId): int
    {
        $progress = ProjectMilestone::where('project_id', $projectId)
            ->where('is_completed', true)
            ->sum('weight_percent');

        return min((int) $progress, 100);
    }

    private function getComputedProjectStatusName(int $projectId): string
    {
        $progress = $this->calculateProjectProgress($projectId);

        return $this->getAutoProjectStatusNameByProgress($progress);
    }

    private function getAutoProjectStatusNameByProgress(int $progress): string
    {
        $projectStatus = $this->getAutoProjectStatusByProgress($progress);

        if ($projectStatus) {
            return $projectStatus->name;
        }

        if ($progress >= 100) {
            return 'Completed';
        }

        if ($progress > 0) {
            return 'Ongoing';
        }

        return 'Pending';
    }

    private function getProjectStatusTextClass(string $statusName): string
    {
        return match (strtolower(trim($statusName))) {
            'completed', 'complete', 'done' => 'text-success',
            'pending', 'not started', 'new' => 'text-info',
            'ongoing', 'on going', 'in progress', 'in-progress', 'on schedule' => 'text-primary',
            'on hold' => 'text-warning',
            'cancelled', 'canceled' => 'text-danger',
            default => 'text-secondary',
        };
    }

    private function getAutoProjectStatusByProgress(int $progress): ?ProjectStatus
    {
        if ($progress >= 100) {
            return $this->findActiveProjectStatus(['completed', 'complete', 'done']);
        }

        if ($progress > 0) {
            return $this->findActiveProjectStatus(['ongoing', 'on going', 'in progress', 'in-progress']);
        }

        return $this->findActiveProjectStatus(['pending', 'not started', 'new']);
    }

    private function findActiveProjectStatus(array $keywords): ?ProjectStatus
    {
        $normalizedKeywords = collect($keywords)
            ->map(fn ($keyword) => strtolower(trim($keyword)))
            ->values()
            ->all();

        return ProjectStatus::where('status', 'active')
            ->where(function ($query) use ($normalizedKeywords) {
                foreach ($normalizedKeywords as $keyword) {
                    $query->orWhereRaw('LOWER(name) = ?', [$keyword])
                        ->orWhereRaw('LOWER(code) = ?', [$keyword])
                        ->orWhereRaw('LOWER(code) = ?', [strtoupper($keyword)]);
                }
            })
            ->orderBy('sort_order')
            ->first();
    }
}

