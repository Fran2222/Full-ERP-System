<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectTask;
use App\Models\ProjectMgmt\ProjectTaskReport;
use App\Models\ProjectMgmt\ProjectType;
use App\Models\ProjectMgmt\Team;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'events', 'show']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['edit', 'update']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $assets = ['calender'];
        $selectedProject = null;

        if ($request->filled('project_id')) {
            $selectedProject = Project::select('id', 'code', 'name', 'project_type_id')
                ->findOrFail((int) $request->input('project_id'));
        }

        $dailyTasks = ProjectTask::with(['project.manager', 'projectType', 'teams.members', 'teams.teamLeader', 'reports.reporter'])
            ->when($selectedProject, fn ($query) => $query->where('project_id', $selectedProject->id))
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->orderBy('task_time')
            ->limit(80)
            ->get();

        $projectTypes = ProjectType::select('id', 'code', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $projects = Project::select('id', 'code', 'name', 'project_type_id')
            ->orderBy('name')
            ->get();

        $taskDetailPayloads = $dailyTasks->mapWithKeys(fn (ProjectTask $task) => [$task->id => $this->taskDetailPayload($task)]);

        return view('modules.project-mgmt.tasks.index', compact('assets', 'dailyTasks', 'projectTypes', 'projects', 'selectedProject', 'taskDetailPayloads'));
    }

    public function events(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $tasks = ProjectTask::with(['project.manager', 'projectType', 'teams.members', 'teams.teamLeader', 'reports.reporter'])
            ->when($request->filled('project_id'), fn ($query) => $query->where('project_id', (int) $request->input('project_id')))
            ->when($start, fn ($query) => $query->whereDate('end_date', '>=', Carbon::parse($start)->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('start_date', '<=', Carbon::parse($end)->toDateString()))
            ->orderBy('start_date')
            ->orderBy('task_time')
            ->get();

        return response()->json($tasks->map(function (ProjectTask $task) {
            $typeColor = $task->type_color;
            $startDateTime = $task->start_date->format('Y-m-d') . 'T' . ($task->task_time ?: '08:00:00');
            $endDate = ($task->end_date ?: $task->start_date)->copy()->addDay()->format('Y-m-d');

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $startDateTime,
                'end' => $endDate,
                'backgroundColor' => $typeColor,
                'textColor' => '#ffffff',
                'borderColor' => $typeColor,
                'extendedProps' => $this->taskDetailPayload($task),
            ];
        }));
    }

    public function create(): View
    {
        return view('modules.project-mgmt.tasks.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);
        $validated = $this->applyProjectTypeFromProject($validated);

        DB::transaction(function () use ($validated) {
            foreach ($validated['task_titles'] as $index => $title) {
                $task = ProjectTask::create([
                    'project_id' => $validated['project_id'] ?? null,
                    'project_type_id' => $validated['project_type_id'],
                    'title' => $title,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? $validated['start_date'],
                    'task_time' => $validated['task_times'][$index] ?? null,
                    'location' => $validated['location'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $task->teams()->sync($validated['team_ids'] ?? []);
            }
        });

        return redirect()
            ->route('project-tasks.index', ($validated['project_id'] ?? null) ? ['project_id' => $validated['project_id']] : [])
            ->with('success', 'Task/s created successfully.');
    }

    public function edit(ProjectTask $projectTask): View
    {
        return view('modules.project-mgmt.tasks.edit', array_merge($this->formData(), [
            'task' => $projectTask->load(['teams']),
        ]));
    }

    public function update(Request $request, ProjectTask $projectTask): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_type_id' => ['required', Rule::exists('project_types', 'id')->where('status', 'active')],
            'title' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'task_time' => ['nullable', 'date_format:H:i'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['exists:teams,id'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated = $this->applyProjectTypeFromProject($validated);

        $projectTask->update([
            'project_id' => $validated['project_id'] ?? null,
            'project_type_id' => $validated['project_type_id'],
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'task_time' => $validated['task_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $projectTask->teams()->sync($validated['team_ids'] ?? []);

        return redirect()
            ->route('project-tasks.index', $projectTask->project_id ? ['project_id' => $projectTask->project_id] : [])
            ->with('success', 'Task updated successfully.');
    }

    public function storeReport(Request $request, ProjectTask $projectTask): RedirectResponse
    {
        $projectTask->loadMissing(['project.manager', 'teams.members', 'teams.teamLeader']);

        abort_unless($this->canUserReport($projectTask), 403);

        $validated = $request->validate([
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'report_details' => ['required', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'photos.max' => 'You can upload up to 5 photos only.',
            'photos.*.image' => 'Each documentation file must be an image.',
            'photos.*.mimes' => 'Photos must be JPG, JPEG, PNG, or WEBP files.',
            'photos.*.max' => 'Each photo must not exceed 4MB.',
        ]);

        $photoPaths = [];

        foreach (($request->file('photos') ?? []) as $photo) {
            $photoPaths[] = $photo->store('project-task-reports/' . $projectTask->id, 'public');
        }

        ProjectTaskReport::create([
            'project_task_id' => $projectTask->id,
            'reported_by' => auth()->id(),
            'progress_percent' => $validated['progress_percent'],
            'report_details' => $validated['report_details'],
            'photo_paths' => $photoPaths,
        ]);

        return redirect()
            ->route('project-tasks.index', $projectTask->project_id ? ['project_id' => $projectTask->project_id] : [])
            ->with('success', 'Task report submitted successfully.');
    }

    public function destroy(ProjectTask $projectTask): RedirectResponse
    {
        $projectTask->delete();

        return redirect()->route('project-tasks.index')->with('success', 'Task deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'projects' => Project::select('id', 'code', 'name', 'project_type_id', 'start_date', 'target_end_date')
                ->with(['type:id,code,name'])
                ->orderBy('name')
                ->get(),
            'projectTypes' => ProjectType::select('id', 'code', 'name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'teams' => Team::select('id', 'code', 'name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_type_id' => ['required', Rule::exists('project_types', 'id')->where('status', 'active')],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'task_titles' => ['required', 'array', 'min:1'],
            'task_titles.*' => ['required', 'string', 'max:150'],
            'task_times' => ['required', 'array', 'min:1'],
            'task_times.*' => ['required', 'date_format:H:i'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['exists:teams,id'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }


    private function taskDetailPayload(ProjectTask $task): array
    {
        $task->loadMissing(['project.manager', 'projectType', 'teams.members', 'teams.teamLeader', 'reports.reporter']);

        return [
            'title' => $task->title,
            'projectId' => $task->project_id,
            'project' => optional($task->project)->name ?: 'No project selected',
            'projectCode' => optional($task->project)->code ?: 'NO-CODE',
            'type' => optional($task->projectType)->name ?: 'Task Type',
            'typeColor' => $task->type_color,
            'time' => $task->task_time ? Carbon::parse($task->task_time)->format('h:i A') : 'No time set',
            'startIso' => $task->start_date->format('Y-m-d'),
            'startDate' => $task->start_date->format('M d, Y'),
            'endDate' => $task->end_date && ! $task->end_date->isSameDay($task->start_date) ? $task->end_date->format('M d, Y') : null,
            'location' => $task->location ?: 'No location set',
            'description' => $task->description ?: 'No description added.',
            'teams' => $task->teams->pluck('name')->implode(', ') ?: 'No team assigned',
            'editUrl' => route('project-tasks.edit', $task->id),
            'deleteUrl' => route('project-tasks.destroy', $task->id),
            'reportUrl' => route('project-tasks.reports.store', $task->id),
            'canReport' => $this->canUserReport($task),
            'reports' => $this->taskReportPayloads($task),
        ];
    }

    private function taskReportPayloads(ProjectTask $task): array
    {
        $task->loadMissing(['reports.reporter']);

        return $task->reports->map(function (ProjectTaskReport $report) {
            $photoPaths = collect($report->photo_paths ?? [])->filter()->values();

            return [
                'id' => $report->id,
                'reportedBy' => optional($report->reporter)->name ?: 'Unknown reporter',
                'progress' => $report->progress_percent,
                'details' => $report->report_details,
                'detailsPreview' => Str::limit($report->report_details, 180),
                'createdAt' => optional($report->created_at)->format('M d, Y h:i A'),
                'photos' => $photoPaths->map(fn ($path) => Storage::disk('public')->url($path))->all(),
            ];
        })->values()->all();
    }

    private function canUserReport(ProjectTask $task): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->can('projects_mgmt.edit') || $user->can('project_management.edit')) {
            return true;
        }

        $task->loadMissing(['project.manager', 'teams.members', 'teams.teamLeader']);

        if ($task->project && (int) $task->project->project_manager_id === (int) $user->id) {
            return true;
        }

        foreach ($task->teams as $team) {
            if ((int) $team->team_leader_id === (int) $user->id) {
                return true;
            }

            if ($team->members->contains('id', $user->id)) {
                return true;
            }
        }

        return false;
    }

    private function applyProjectTypeFromProject(array $validated): array
    {
        if (! empty($validated['project_id'])) {
            $project = Project::select('id', 'project_type_id')->find($validated['project_id']);

            if ($project && $project->project_type_id) {
                $validated['project_type_id'] = $project->project_type_id;
            }
        }

        return $validated;
    }
}
