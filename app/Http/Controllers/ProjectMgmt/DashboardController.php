<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Client;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectMilestone;
use App\Models\ProjectMgmt\ProjectPriority;
use App\Models\ProjectMgmt\ProjectTask;
use App\Models\ProjectMgmt\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $weekEnd = $today->copy()->addDays(7);

        $projects = Project::query()
            ->with(['client', 'manager', 'priority', 'type', 'milestones'])
            ->where(function ($query) {
                $query->whereNull('is_archived')->orWhere('is_archived', false);
            })
            ->latest('updated_at')
            ->get();

        $projectStats = $projects->map(function (Project $project) use ($today) {
            $progress = $this->projectProgress($project);
            $deadline = $project->target_end_date ?: $project->end_date;
            $status = $this->projectStatus($progress, $deadline, $today, $project->status);

            return [
                'id' => $project->id,
                'code' => $project->code ?: 'PRJ-' . str_pad((string) $project->id, 4, '0', STR_PAD_LEFT),
                'name' => $project->name ?: 'Untitled Project',
                'client' => $project->client?->name ?: '-',
                'client_initial' => $this->initials($project->client?->name ?: 'N/A'),
                'amount' => (float) ($project->amount ?? 0),
                'manager' => $this->userName($project->manager),
                'priority' => $project->priority?->name ?: 'Normal',
                'type' => $project->type?->name ?: ($project->project_type ? ucwords(str_replace('_', ' ', $project->project_type)) : '-'),
                'progress' => $progress,
                'status' => $status,
                'deadline' => $deadline,
                'updated_at' => $project->updated_at,
            ];
        });

        $activeProjects = $projectStats->filter(fn ($project) => ! in_array($project['status'], ['Completed', 'Cancelled'], true));

        $cards = [
            'total_projects' => $projects->count(),
            'ongoing_projects' => $projectStats->where('status', 'Ongoing')->count(),
            'completed_projects' => $projectStats->where('status', 'Completed')->count(),
            'delayed_projects' => $projectStats->where('status', 'Delayed')->count(),
            'total_tasks' => ProjectTask::count(),
            'due_this_week' => ProjectTask::query()
                ->whereDate('start_date', '<=', $weekEnd)
                ->whereDate('end_date', '>=', $today)
                ->count(),
            'total_clients' => Client::count(),
            'total_teams' => Team::count(),
        ];

        $statusChart = $projectStats
            ->groupBy('status')
            ->map->count()
            ->sortKeys();

        $priorityQuery = ProjectPriority::query()
            ->leftJoin('projects', 'project_priorities.id', '=', 'projects.priority_id')
            ->select('project_priorities.name', DB::raw('COUNT(projects.id) as total'))
            ->groupBy('project_priorities.id', 'project_priorities.name');

        if (Schema::hasColumn('project_priorities', 'level')) {
            $priorityQuery->addSelect('project_priorities.level')
                ->groupBy('project_priorities.level')
                ->orderBy('project_priorities.level');
        } else {
            $priorityQuery->orderBy('project_priorities.name');
        }

        $priorityChart = $priorityQuery
            ->get()
            ->map(fn ($row) => ['name' => $row->name ?: 'No Priority', 'total' => (int) $row->total]);

        if ($priorityChart->isEmpty()) {
            $priorityChart = $projectStats->groupBy('priority')
                ->map(fn ($items, $name) => ['name' => $name ?: 'No Priority', 'total' => $items->count()])
                ->values();
        }

        $topProjects = $activeProjects
            ->sortByDesc('updated_at')
            ->take(100)
            ->values();

        $upcomingMilestones = ProjectMilestone::query()
            ->with('project')
            ->where(function ($query) {
                $query->whereNull('is_completed')->orWhere('is_completed', false);
            })
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->limit(8)
            ->get();

        $upcomingTasks = ProjectTask::query()
            ->with(['project', 'projectType'])
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(8)
            ->get();

        $projectsByClient = Client::query()
            ->withCount('projects')
            ->orderByDesc('projects_count')
            ->limit(8)
            ->get()
            ->map(fn (Client $client) => [
                'name' => $client->name ?: 'Unnamed Client',
                'total' => (int) $client->projects_count,
            ])
            ->filter(fn ($client) => $client['total'] > 0)
            ->values();

        $teamWorkload = Team::query()
            ->withCount(['milestones', 'tasks'])
            ->orderByDesc('tasks_count')
            ->orderByDesc('milestones_count')
            ->limit(8)
            ->get();

        $recentActivities = $this->recentActivities();

        return view('modules.project-mgmt.pm_dashboard', compact(
            'cards',
            'statusChart',
            'priorityChart',
            'topProjects',
            'upcomingMilestones',
            'upcomingTasks',
            'projectsByClient',
            'teamWorkload',
            'recentActivities'
        ));
    }

    private function projectProgress(Project $project): int
    {
        if ($project->relationLoaded('milestones') && $project->milestones->count() > 0) {
            return min(100, (int) $project->milestones->where('is_completed', true)->sum('weight_percent'));
        }

        return min(100, max(0, (int) ($project->progress_percent ?? 0)));
    }

    private function projectStatus(int $progress, $deadline, Carbon $today, ?string $storedStatus = null): string
    {
        $storedStatus = strtolower(trim((string) $storedStatus));

        if (in_array($storedStatus, ['cancelled', 'canceled'], true)) {
            return 'Cancelled';
        }

        if ($progress >= 100) {
            return 'Completed';
        }

        if ($deadline && Carbon::parse($deadline)->lt($today)) {
            return 'Delayed';
        }

        if ($progress > 0) {
            return 'Ongoing';
        }

        return 'Pending';
    }

    private function userName($user): string
    {
        if (! $user) {
            return '-';
        }

        return trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
            ?: ($user->name ?? $user->email ?? '-');
    }


    private function initials(string $name): string
    {
        $name = trim($name);

        if ($name === '' || $name === '-') {
            return 'N/A';
        }

        $words = preg_split('/\s+/', $name) ?: [];
        $letters = collect($words)
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $letters !== '' ? $letters : 'N/A';
    }

    private function recentActivities()
    {
        $projectActivities = Project::query()
            ->with(['creator', 'manager'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Project $project) => [
                'title' => 'Project Updated',
                'description' => ($project->code ?: 'Project') . ' - ' . ($project->name ?: 'Untitled Project'),
                'by' => $this->userName($project->manager ?: $project->creator),
                'date' => $project->updated_at ?: $project->created_at,
                'type' => 'project',
            ]);

        $milestoneActivities = ProjectMilestone::query()
            ->with(['project', 'creator'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (ProjectMilestone $milestone) => [
                'title' => $milestone->is_completed ? 'Milestone Completed' : 'Milestone Updated',
                'description' => ($milestone->project?->code ?: 'Project') . ' - ' . ($milestone->title ?: 'Milestone'),
                'by' => $this->userName($milestone->creator),
                'date' => $milestone->completed_at ?: $milestone->updated_at,
                'type' => 'milestone',
            ]);

        $taskActivities = ProjectTask::query()
            ->with(['project', 'creator'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (ProjectTask $task) => [
                'title' => 'Task Updated',
                'description' => ($task->project?->code ?: 'General') . ' - ' . ($task->title ?: 'Task'),
                'by' => $this->userName($task->creator),
                'date' => $task->updated_at ?: $task->created_at,
                'type' => 'task',
            ]);

        return $projectActivities
            ->merge($milestoneActivities)
            ->merge($taskActivities)
            ->filter(fn ($activity) => ! empty($activity['date']))
            ->sortByDesc(fn ($activity) => optional($activity['date'])->timestamp ?? 0)
            ->take(10)
            ->values();
    }
}
