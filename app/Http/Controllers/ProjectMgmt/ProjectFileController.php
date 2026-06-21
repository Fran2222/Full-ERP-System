<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Client;
use App\Models\ProjectMgmt\Project;
use App\Models\ProjectMgmt\ProjectFile;
use App\Models\ProjectMgmt\ProjectFileActivity;
use App\Models\ProjectMgmt\ProjectFileFolder;
use App\Models\ProjectMgmt\ProjectType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:projects_mgmt.view')->only(['index', 'show', 'download', 'preview']);
        $this->middleware('permission:projects_mgmt.create')->only(['create', 'store']);
        $this->middleware('permission:projects_mgmt.edit')->only(['rename', 'move', 'folderColor']);
        $this->middleware('permission:projects_mgmt.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $query = Project::query()
            ->with(['client', 'type', 'projectStatus', 'fileFolder'])
            ->withCount('files')
            ->withMax('files', 'updated_at');

        $this->applyUserScope($query);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('projects.code', 'ILIKE', "%{$search}%")
                    ->orWhere('projects.name', 'ILIKE', "%{$search}%")
                    ->orWhereHas('client', function ($client) use ($search) {
                        $client->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('code', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('type', function ($type) use ($search) {
                        $type->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('code', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('projectStatus', function ($status) use ($search) {
                        $status->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('code', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('files', function ($files) use ($search) {
                        $files->where('file_name', 'ILIKE', "%{$search}%")
                            ->orWhere('original_name', 'ILIKE', "%{$search}%")
                            ->orWhere('extension', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', (int) $request->input('client_id'));
        }

        if ($request->filled('project_type_id')) {
            $query->where('project_type_id', (int) $request->input('project_type_id'));
        }

        $this->applyModifiedFilterToProjects($query, (string) $request->input('modified'));

        $sort = $request->input('sort', 'modified_desc');

        if ($sort === 'name') {
            $query->orderBy('name');
        } elseif ($sort === 'code') {
            $query->orderBy('code');
        } else {
            $query->orderByDesc('files_max_updated_at')->latest('updated_at');
        }

        $projects = $query->get();
        $viewMode = $request->input('view', 'folder');

        $clients = Client::orderBy('name')->get();
        $projectTypes = ProjectType::orderBy('name')->get();

        return view('modules.project-mgmt.files.index', compact(
            'projects',
            'clients',
            'projectTypes',
            'sort',
            'viewMode'
        ));
    }

    public function create(Request $request): View
    {
        $projects = Project::query()
            ->select('id', 'code', 'name')
            ->orderBy('code')
            ->get();

        $selectedProjectId = $request->filled('project_id') ? (int) $request->input('project_id') : null;
        $selectedProject = $selectedProjectId ? Project::query()->select('id', 'code', 'name')->find($selectedProjectId) : null;

        if ($selectedProject) {
            $this->authorizeProject($selectedProject);
        }

        return view('modules.project-mgmt.files.create', compact('projects', 'selectedProjectId', 'selectedProject'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['required', 'file', 'max:51200'],
            'file_names' => ['nullable', 'array'],
            'file_names.*' => ['nullable', 'string', 'max:255'],
        ], [
            'project_id.required' => 'Please select a project folder.',
            'attachments.required' => 'Please upload at least one file.',
            'attachments.*.max' => 'Each file must not exceed 50MB.',
        ]);

        $project = Project::findOrFail((int) $validated['project_id']);
        $this->authorizeProject($project);

        ProjectFileFolder::firstOrCreate(
            ['project_id' => $project->id],
            ['color' => 'sky']
        );

        foreach ($request->file('attachments', []) as $index => $attachment) {
            $extension = strtolower($attachment->getClientOriginalExtension());
            $storedName = (string) Str::uuid() . ($extension ? '.' . $extension : '');
            $folder = 'project-files/' . $project->id;

            $path = $attachment->storeAs($folder, $storedName, 'public');

            $customName = trim((string) ($validated['file_names'][$index] ?? ''));
            $originalName = $attachment->getClientOriginalName();
            $fallbackName = pathinfo($originalName, PATHINFO_FILENAME);

            $file = ProjectFile::create([
                'project_id' => $project->id,
                'owner_id' => auth()->id(),
                'file_name' => $customName !== '' ? $customName : $fallbackName,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $attachment->getClientMimeType(),
                'extension' => $extension,
                'size' => $attachment->getSize() ?: 0,
            ]);

            $this->recordActivity($project->id, $file->id, 'uploaded', 'Uploaded file "' . $file->file_name . '".', [
                'original_name' => $originalName,
                'size' => $file->formatted_size,
            ]);
        }

        return redirect()
            ->route('project-files.folder', $project->id)
            ->with('success', 'Project files uploaded successfully.');
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorizeProject($project);

        $project->load(['client', 'type', 'projectStatus', 'fileFolder']);

        ProjectFileFolder::firstOrCreate(
            ['project_id' => $project->id],
            ['color' => 'sky']
        );

        $filesQuery = $project->files()
            ->with('owner');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $filesQuery->where(function ($q) use ($search) {
                $q->where('file_name', 'ILIKE', "%{$search}%")
                    ->orWhere('original_name', 'ILIKE', "%{$search}%")
                    ->orWhere('extension', 'ILIKE', "%{$search}%")
                    ->orWhereHas('owner', function ($owner) use ($search) {
                        // The current users table has first_name, middle_name, last_name and email.
                        // Do not query users.name because that column does not exist in PostgreSQL.
                        $owner->where('first_name', 'ILIKE', "%{$search}%")
                            ->orWhere('middle_name', 'ILIKE', "%{$search}%")
                            ->orWhere('last_name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%")
                            ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) ILIKE ?", ["%{$search}%"]);
                    });
            });
        }

        $this->applyModifiedFilterToFiles($filesQuery, (string) $request->input('modified'));

        $sort = $request->input('sort', 'modified_desc');

        if ($sort === 'name') {
            $filesQuery->orderBy('file_name');
        } elseif ($sort === 'size_desc') {
            $filesQuery->orderByDesc('size');
        } elseif ($sort === 'size_asc') {
            $filesQuery->orderBy('size');
        } else {
            $filesQuery->latest('updated_at');
        }

        $files = $filesQuery->paginate(20)->withQueryString();

        $projects = Project::query()
            ->select('id', 'code', 'name')
            ->where('id', '!=', $project->id)
            ->orderBy('code')
            ->get();

        $viewMode = $request->input('view', 'list');

        $activities = ProjectFileActivity::query()
            ->with(['user', 'file'])
            ->where('project_id', $project->id)
            ->latest()
            ->limit(100)
            ->get();

        return view('modules.project-mgmt.files.show', compact('project', 'files', 'projects', 'viewMode', 'sort', 'activities'));
    }

    public function rename(Request $request, ProjectFile $projectFile): RedirectResponse
    {
        $projectFile->loadMissing('project');
        $this->authorizeProject($projectFile->project);

        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
        ]);

        $oldName = $projectFile->file_name;

        $projectFile->update([
            'file_name' => $validated['file_name'],
        ]);

        $this->recordActivity($projectFile->project_id, $projectFile->id, 'renamed', 'Renamed file from "' . $oldName . '" to "' . $projectFile->file_name . '".');

        return back()->with('success', 'File renamed successfully.');
    }

    public function move(Request $request, ProjectFile $projectFile): RedirectResponse
    {
        $projectFile->loadMissing('project');
        $this->authorizeProject($projectFile->project);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
        ], [
            'project_id.required' => 'Please select the target project folder.',
        ]);

        $fromProjectId = $projectFile->project_id;
        $targetProject = Project::findOrFail((int) $validated['project_id']);
        $this->authorizeProject($targetProject);

        ProjectFileFolder::firstOrCreate(
            ['project_id' => $targetProject->id],
            ['color' => 'sky']
        );

        $projectFile->update([
            'project_id' => $targetProject->id,
        ]);

        $this->recordActivity($fromProjectId, $projectFile->id, 'moved_out', 'Moved file "' . $projectFile->file_name . '" to ' . ($targetProject->code ?: 'PROJECT') . ' - ' . $targetProject->name . '.');
        $this->recordActivity($targetProject->id, $projectFile->id, 'moved_in', 'Received moved file "' . $projectFile->file_name . '".');

        return redirect()
            ->route('project-files.folder', $targetProject->id)
            ->with('success', 'File moved successfully.');
    }

    public function folderColor(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'color' => ['required', 'in:sky,blue,green,orange,purple,pink,gray'],
        ]);

        ProjectFileFolder::updateOrCreate(
            ['project_id' => $project->id],
            ['color' => $validated['color']]
        );

        $this->recordActivity($project->id, null, 'folder_color', 'Changed folder color to ' . ucfirst($validated['color']) . '.');

        return back()->with('success', 'Folder color updated successfully.');
    }

    public function preview(ProjectFile $projectFile): BinaryFileResponse
    {
        $projectFile->loadMissing('project');
        $this->authorizeProject($projectFile->project);

        abort_unless(Storage::disk('public')->exists($projectFile->path), 404);

        $previewName = $projectFile->file_name;
        if ($projectFile->extension && ! Str::endsWith(strtolower($previewName), '.' . strtolower($projectFile->extension))) {
            $previewName .= '.' . $projectFile->extension;
        }

        $mimeType = $projectFile->mime_type ?: Storage::disk('public')->mimeType($projectFile->path) ?: 'application/octet-stream';
        $safePreviewName = str_replace(['\\', '"'], ['', ''], $previewName);

        return response()->file(Storage::disk('public')->path($projectFile->path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $safePreviewName . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(ProjectFile $projectFile): StreamedResponse
    {
        $projectFile->loadMissing('project');
        $this->authorizeProject($projectFile->project);

        abort_unless(Storage::disk('public')->exists($projectFile->path), 404);

        $downloadName = $projectFile->file_name;
        if ($projectFile->extension && ! Str::endsWith(strtolower($downloadName), '.' . strtolower($projectFile->extension))) {
            $downloadName .= '.' . $projectFile->extension;
        }

        return Storage::disk('public')->download($projectFile->path, $downloadName);
    }

    public function destroy(ProjectFile $projectFile): RedirectResponse
    {
        $projectFile->loadMissing('project');
        $this->authorizeProject($projectFile->project);

        $projectId = $projectFile->project_id;
        $fileName = $projectFile->file_name;

        $projectFile->deleteStoredFile();
        $projectFile->delete();

        $this->recordActivity($projectId, null, 'deleted', 'Deleted file "' . $fileName . '".');

        return redirect()
            ->route('project-files.folder', $projectId)
            ->with('success', 'File deleted successfully.');
    }

    private function applyModifiedFilterToProjects($query, ?string $modified): void
    {
        if (! $modified) {
            return;
        }

        $date = $this->modifiedStartDate($modified);

        if (! $date) {
            return;
        }

        $query->whereHas('files', function ($files) use ($date) {
            $files->where('updated_at', '>=', $date);
        });
    }

    private function applyModifiedFilterToFiles($query, ?string $modified): void
    {
        if (! $modified) {
            return;
        }

        $date = $this->modifiedStartDate($modified);

        if (! $date) {
            return;
        }

        $query->where('updated_at', '>=', $date);
    }

    private function modifiedStartDate(string $modified): ?Carbon
    {
        if ($modified === 'today') {
            return now()->startOfDay();
        }

        if ($modified === '7days') {
            return now()->subDays(7)->startOfDay();
        }

        if ($modified === '30days') {
            return now()->subDays(30)->startOfDay();
        }

        if ($modified === 'year') {
            return now()->startOfYear();
        }

        return null;
    }

    private function recordActivity(int $projectId, ?int $fileId, string $action, string $description, ?array $meta = null): void
    {
        ProjectFileActivity::create([
            'project_id' => $projectId,
            'project_file_id' => $fileId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta,
        ]);
    }

    private function applyUserScope($query): void
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('super-admin')) {
            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('project_manager_id', $user->id)
                ->orWhereHas('users', function ($users) use ($user) {
                    $users->where('users.id', $user->id);
                });
        });
    }

    private function authorizeProject(Project $project): void
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('super-admin')) {
            return;
        }

        $project->loadMissing('users');

        $isManager = (int) $project->project_manager_id === (int) $user->id;
        $isAssigned = $project->users->contains('id', $user->id);

        abort_unless($isManager || $isAssigned, 403);
    }
}
