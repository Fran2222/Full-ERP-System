<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('branches.view') || auth()->user()->can('hr.view'), 403);

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $allowedSorts = [
            'id' => 'id',
            'name' => 'name',
            'code' => 'code',
            'address' => 'address',
        ];

        $sort = (string) $request->input('sort', 'id');

        if (! array_key_exists($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $direction = strtolower((string) $request->input('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $branches = Branch::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%")
                        ->orWhere('status', 'ilike', "%{$search}%")
                        ->orWhere('address', 'ilike', "%{$search}%");
                });
            })
            ->orderBy($allowedSorts[$sort], $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('branches.index', compact('branches', 'search', 'perPage', 'sort', 'direction'));
    }

    public function create()
    {
        $this->abortUnlessCanCreateBranch();

        $data = null;
        $id = null;

        return view('branches.form', compact('data', 'id'));
    }

    public function store(Request $request)
    {
        $this->abortUnlessCanCreateBranch();

        $request->validate([
            'name' => 'required|max:255',
            'code' => 'required|max:50|unique:branches,code',
            'address' => 'nullable|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        Branch::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit($id)
    {
        $this->abortUnlessCanEditBranch();

        $data = Branch::findOrFail($id);

        return view('branches.form', compact('data', 'id'));
    }

    public function update(Request $request, $id)
    {
        $this->abortUnlessCanEditBranch();

        $branch = Branch::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'code' => 'required|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'address' => $request->address,
            'status' => $request->status,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $this->abortUnlessSuperAdmin();

        $branch = Branch::findOrFail($id);
        $branch->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully.',
            ]);
        }

        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }

    private function abortUnlessCanCreateBranch(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->hasAnyRole([
                    'super admin',
                    'super-admin',
                    'superadmin',
                    'hr',
                ])
                || $user->can('branches.create')
                || $user->can('hr.branches.create')
                || $user->can('hr.view')
            ),
            403
        );
    }

    private function abortUnlessCanEditBranch(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->hasAnyRole([
                    'super admin',
                    'super-admin',
                    'superadmin',
                    'hr',
                ])
                || $user->can('branches.edit')
                || $user->can('hr.branches.edit')
            ),
            403
        );
    }

    private function abortUnlessSuperAdmin(): void
    {
        abort_unless(
            auth()->check() &&
            auth()->user()->hasAnyRole([
                'super admin',
                'super-admin',
                'superadmin',
            ]),
            403
        );
    }
}