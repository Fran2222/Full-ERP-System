<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectMilestone;
use App\Models\ProjectMgmt\ProjectStatus;
use App\Models\ProjectMgmt\Team;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProjectMilestoneController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'list', 'show']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update', 'toggleComplete']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index()
    {
        $projects = Project::select('id', 'code', 'name')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('modules.project-mgmt.milestones.index', compact('projects'));
    }

    public function list(Request $request)
    {
        $query = ProjectMilestone::with(['project', 'team', 'teams']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                $keyword = trim($request->input('search.value', ''));

                if ($keyword !== '') {
                    $query->where('title', 'ilike', "%{$keyword}%");
                }
            })
            ->addColumn('project_name', function ($milestone) {
                if (! $milestone->project) {
                    return '-';
                }

                return $milestone->project->code ?: 'NO-CODE';
            })
           ->addColumn('team_name', function ($milestone) {
                if ($milestone->relationLoaded('teams') && $milestone->teams->isNotEmpty()) {
                    return $milestone->teams
                        ->pluck('name')
                        ->filter()
                        ->implode(', ');
                }

                return $milestone->team->name ?? '-';
            })
            ->addColumn('date_range', function ($milestone) {
                $start = $milestone->start_date ? $milestone->start_date->format('M d, Y') : '-';
                $end = $milestone->end_date ? $milestone->end_date->format('M d, Y') : '-';

                return '
                    <div class="wmc-duration-wrap">
                        <div class="wmc-duration-date">' . e($start) . '</div>
                        <div class="wmc-duration-to">to</div>
                        <div class="wmc-duration-date">' . e($end) . '</div>
                    </div>
                ';
            })
            ->addColumn('completion_checkbox', function ($milestone) {
                $checked = $milestone->is_completed ? 'checked' : '';

                return '
                    <div class="form-check d-flex justify-content-center">
                        <input type="checkbox"
                            class="form-check-input toggle-milestone"
                            data-url="' . route('project-milestones.toggle-complete', $milestone->id) . '"
                            ' . $checked . '>
                    </div>
                ';
            })
            ->addColumn('show_url', function ($milestone) {
                return route('project-milestones.show', $milestone->id);
            })
            ->addColumn('action', function ($milestone) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('project-milestones.edit', $milestone->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('project-milestones.destroy', $milestone->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $milestone->title,
                    'delete-milestone',
                    'Edit Milestone',
                    'Delete Milestone'
                );
            })
            ->rawColumns(['team_name', 'date_range', 'completion_checkbox', 'action'])
            ->make(true);
    }

    public function create()
    {
        $projects = Project::select('id', 'code', 'name', 'start_date', 'target_end_date')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        $teams = Team::where('status', 'active')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('modules.project-mgmt.milestones.create', compact('projects', 'teams'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMilestone($request);

        $this->ensureProjectDateRange($request);
        $this->ensureTotalWeightDoesNotExceed100($request);

        $teamIds = $validated['team_ids'] ?? [];
        unset($validated['team_ids']);

        $validated['team_id'] = $teamIds[0] ?? null;
        $validated['status'] = 'pending';
        $validated['is_completed'] = false;
        $validated['completed_at'] = null;
        $validated['created_by'] = auth()->id();
        $validated['sort_order'] = ProjectMilestone::where('project_id', $request->project_id)->max('sort_order') + 1;

        $milestone = ProjectMilestone::create($validated);
        $milestone->teams()->sync($teamIds);

        $this->recomputeProjectProgress((int) $request->project_id);

        return redirect()
            ->route('project-milestones.index')
            ->with('success', 'Milestone created successfully.');
    }

    public function show(ProjectMilestone $milestone)
    {
        $milestone->load(['project', 'team', 'teams', 'creator']);

        return view('modules.project-mgmt.milestones.show', compact('milestone'));
    }

    public function edit(ProjectMilestone $milestone)
    {
        $milestone->load('teams');

        $projects = Project::select('id', 'code', 'name', 'start_date', 'target_end_date')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        $teams = Team::where('status', 'active')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('modules.project-mgmt.milestones.edit', compact('milestone', 'projects', 'teams'));
    }

    public function update(Request $request, ProjectMilestone $milestone)
    {
        $validated = $this->validateMilestone($request);

        $this->ensureProjectDateRange($request);
        $this->ensureTotalWeightDoesNotExceed100($request, $milestone->id);

        $oldProjectId = (int) $milestone->project_id;

        $teamIds = $validated['team_ids'] ?? [];
        unset($validated['team_ids']);

        $validated['team_id'] = $teamIds[0] ?? null;

        $milestone->update($validated);
        $milestone->teams()->sync($teamIds);

        $this->recomputeProjectProgress($oldProjectId);
        $this->recomputeProjectProgress((int) $milestone->project_id);

        return redirect()
            ->route('project-milestones.index')
            ->with('success', 'Milestone updated successfully.');
    }

    public function toggleComplete(ProjectMilestone $milestone)
    {
        $willBeCompleted = ! $milestone->is_completed;

        $milestone->update([
            'is_completed' => $willBeCompleted,
            'status' => $willBeCompleted ? 'completed' : 'pending',
            'completed_at' => $willBeCompleted ? now() : null,
        ]);

        $this->recomputeProjectProgress((int) $milestone->project_id);

        return response()->json([
            'status' => true,
            'message' => 'Milestone updated successfully.',
        ]);
    }

    public function destroy(ProjectMilestone $milestone)
    {
        $projectId = $milestone->project_id;

        $milestone->teams()->detach();
        $milestone->delete();

        $this->recomputeProjectProgress((int) $projectId);

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Milestone deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-milestones.index')
            ->with('success', 'Milestone deleted successfully.');
    }

    private function validateMilestone(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['exists:teams,id'],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'weight_percent' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function ensureProjectDateRange(Request $request): void
    {
        $project = Project::findOrFail($request->project_id);

        if ($project->start_date && $request->start_date < $project->start_date->format('Y-m-d')) {
            throw ValidationException::withMessages([
                'start_date' => 'Milestone start date must not be earlier than project start date.',
            ]);
        }

        if ($project->target_end_date && $request->end_date > $project->target_end_date->format('Y-m-d')) {
            throw ValidationException::withMessages([
                'end_date' => 'Milestone end date must not be later than project target end date.',
            ]);
        }
    }

    private function ensureTotalWeightDoesNotExceed100(Request $request, ?int $ignoreId = null): void
    {
        $query = ProjectMilestone::where('project_id', $request->project_id);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $currentTotal = (int) $query->sum('weight_percent');
        $newTotal = $currentTotal + (int) $request->weight_percent;

        if ($newTotal > 100) {
            throw ValidationException::withMessages([
                'weight_percent' => 'Total milestone percentage for this project cannot exceed 100%. Current total is ' . $currentTotal . '%.',
            ]);
        }
    }

    private function recomputeProjectProgress(int $projectId): void
    {
        $project = Project::with('projectStatus')->find($projectId);

        if (! $project) {
            return;
        }

        $progress = ProjectMilestone::where('project_id', $projectId)
            ->where('is_completed', true)
            ->sum('weight_percent');

        $progress = min((int) $progress, 100);

        $updateData = [
            'progress_percent' => $progress,
        ];

        if (! $this->isManualProjectStatus($project)) {
            $autoStatus = $this->getAutoProjectStatusByProgress($progress);

            if ($autoStatus) {
                $updateData['status_id'] = $autoStatus->id;
                $updateData['status'] = strtolower($autoStatus->name);
            }
        }

        $project->update($updateData);
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

    private function isManualProjectStatus(Project $project): bool
    {
        $statusName = strtolower(optional($project->projectStatus)->name ?? $project->status ?? '');
        $statusCode = strtolower(optional($project->projectStatus)->code ?? '');

        $manualStatuses = [
            'on hold',
            'hold',
            'cancelled',
            'canceled',
            'archived',
            'archive',
        ];

        return in_array($statusName, $manualStatuses, true)
            || in_array($statusCode, $manualStatuses, true);
    }
}
