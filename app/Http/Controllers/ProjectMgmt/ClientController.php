<?php

namespace App\Http\Controllers\ProjectMgmt;

use App\Helpers\ActionButtonHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectMgmt\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
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
        $clients = Client::latest('id')->paginate(10);

        return view('modules.project-mgmt.clients.index', compact('clients'));
    }

    public function create()
    {
        $code = $this->generateCode();

        return view('modules.project-mgmt.clients.create', compact('code'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:clients,code'],
            'name' => ['required', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:180'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Client name is required.',
            'contact_person.max' => 'Contact person must not exceed 100 characters.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        Client::create($request->only([
            'code',
            'name',
            'contact_person',
            'contact_number',
            'email',
            'address',
            'remarks',
            'status',
        ]));

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        return view('modules.project-mgmt.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('modules.project-mgmt.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:clients,code,' . $client->id],
            'name' => ['required', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:180'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Client name is required.',
            'contact_person.max' => 'Contact person must not exceed 100 characters.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        $client->update($request->only([
            'code',
            'name',
            'contact_person',
            'contact_number',
            'email',
            'address',
            'remarks',
            'status',
        ]));

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client, Request $request)
    {
        if ($client->projects()->exists()) {
            $message = 'Cannot delete this client because it is currently linked to one or more projects.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('clients.index')
                ->with('error', $message);
        }

        $client->delete();

        $message = 'Client deleted successfully.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', $message);
    }

    private function generateCode(): string
    {
        $lastClient = Client::latest('id')->first();
        $nextId = $lastClient ? $lastClient->id + 1 : 1;

        return 'CL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function list(Request $request)
    {
        $query = Client::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('show_url', function ($client) {
                return route('clients.show', $client->id);
            })
            ->addColumn('action', function ($client) {
                $editUrl = auth()->user()->can('projects_mgmt.edit')
                    ? route('clients.edit', $client->id)
                    : null;

                $deleteUrl = auth()->user()->can('projects_mgmt.delete')
                    ? route('clients.destroy', $client->id)
                    : null;

                return ActionButtonHelper::editDelete(
                    $editUrl,
                    $deleteUrl,
                    $client->name,
                    'delete-client',
                    'Edit Client',
                    'Delete Client'
                );
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}