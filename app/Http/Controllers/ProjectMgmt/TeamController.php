<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TeamController extends Controller
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
        return view('modules.project-mgmt.teams.index');
    }

    public function list(Request $request)
    {
        $query = Team::with(['teamLeader', 'members']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('team_leader_name', function ($team) {
                if (! $team->teamLeader) {
                    return '-';
                }

                return trim(($team->teamLeader->first_name ?? '') . ' ' . ($team->teamLeader->last_name ?? '')) ?: $team->teamLeader->email;
            })
            ->addColumn('members', function ($team) {
                return $team->members->map(function ($user) {
                    return [
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email ?? '',
                    ];
                })->values()->toArray();
            })
            ->addColumn('show_url', function ($team) {
                return route('project-teams.show', $team->id);
            })
            ->addColumn('action', function ($team) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('project-teams.edit', $team->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('project-teams.destroy', $team->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $team->name,
                    'delete-team',
                    'Edit Team',
                    'Delete Team'
                );
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $code = $this->generateCode();

        $users = User::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('modules.project-mgmt.teams.create', compact('code', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:teams,code'],
            'name' => ['required', 'string', 'max:100'],
            'team_leader_id' => ['required', 'exists:users,id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $team = Team::create([
            'code' => $request->code,
            'name' => $request->name,
            'team_leader_id' => $request->team_leader_id,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        $team->members()->sync($request->input('members', []));

        return redirect()
            ->route('project-teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        $team->load(['teamLeader', 'members']);

        return view('modules.project-mgmt.teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $team->load('members');

        $users = User::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('modules.project-mgmt.teams.edit', compact('team', 'users'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:teams,code,' . $team->id],
            'name' => ['required', 'string', 'max:100'],
            'team_leader_id' => ['required', 'exists:users,id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $team->update([
            'code' => $request->code,
            'name' => $request->name,
            'team_leader_id' => $request->team_leader_id,
            'remarks' => $request->remarks,
            'status' => $request->status,
        ]);

        $team->members()->sync($request->input('members', []));

        return redirect()
            ->route('project-teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Team deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    private function generateCode(): string
    {
        $lastTeam = Team::latest('id')->first();
        $nextId = $lastTeam ? $lastTeam->id + 1 : 1;

        return 'T-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}