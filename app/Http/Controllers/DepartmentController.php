<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('departments.view') || auth()->user()->can('hr.view'), 403);

        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $allowedSorts = ['id', 'name', 'designation'];

        $sort = (string) $request->input('sort', 'id');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $direction = strtolower((string) $request->input('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Department::query()
            ->withCount('positions')
            ->with(['positions' => function ($query) {
                $query->select('id', 'name', 'department_id')->orderBy('name');
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('status', 'ilike', "%{$search}%")
                        ->orWhereHas('positions', function ($positionQuery) use ($search) {
                            $positionQuery->where('name', 'ilike', "%{$search}%");
                        });
                });
            });

        if ($sort === 'designation') {
            $query->orderByRaw("(
                SELECT MIN(LOWER(positions.name))
                FROM positions
                WHERE positions.department_id = departments.id
            ) {$direction} NULLS LAST");
        } else {
            $query->orderBy($sort, $direction);
        }

        $departments = $query
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('departments.index', compact('departments', 'search', 'perPage', 'sort', 'direction'));
    }

    public function create()
    {
        $this->abortUnlessCanCreateDepartment();

        $department = new Department();
        $designationText = '';

        return view('departments.form', compact('department', 'designationText'));
    }

    public function store(Request $request)
    {
        $this->abortUnlessCanCreateDepartment();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'designation_names_text' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $department = Department::create([
                'name' => $data['name'],
                'code' => $this->generateUniqueDepartmentCode($data['name']),
                'status' => $data['status'] ?? 'active',
            ]);

            $designationNames = collect(preg_split("/\r\n|\n|\r/", (string) ($data['designation_names_text'] ?? '')))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->unique()
                ->values();

            foreach ($designationNames as $designationName) {
                Position::create([
                    'name' => $designationName,
                    'code' => $this->generateUniquePositionCode($designationName),
                    'department_id' => $department->id,
                    'status' => 'active',
                ]);
            }
        });

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $this->abortUnlessCanManageDepartment();

        $designationText = $department->positions()
            ->orderBy('name')
            ->pluck('name')
            ->implode(PHP_EOL);

        return view('departments.form', compact('department', 'designationText'));
    }

    public function update(Request $request, Department $department)
    {
        $this->abortUnlessCanManageDepartment();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'designation_names_text' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $department) {
            $department->update([
                'name' => $data['name'],
                'code' => $this->generateUniqueDepartmentCode($data['name'], $department->id),
                'status' => $data['status'] ?? $department->status ?? 'active',
            ]);

            $designationNames = collect(preg_split("/\r\n|\n|\r/", (string) ($data['designation_names_text'] ?? '')))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->unique()
                ->values();

            $department->positions()->delete();

            foreach ($designationNames as $designationName) {
                Position::create([
                    'name' => $designationName,
                    'code' => $this->generateUniquePositionCode($designationName),
                    'department_id' => $department->id,
                    'status' => 'active',
                ]);
            }
        });

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request, Department $department)
    {
        $this->abortUnlessCanManageDepartment();

        $department->positions()->delete();
        $department->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
            ]);
        }

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    private function abortUnlessCanCreateDepartment(): void
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
                || $user->can('departments.create')
                || $user->can('hr.departments.create')
                || $user->can('hr.view')
            ),
            403
        );
    }

    private function abortUnlessCanManageDepartment(): void
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
                || $user->can('departments.edit')
                || $user->can('departments.delete')
                || $user->can('hr.departments.edit')
                || $user->can('hr.departments.delete')
            ),
            403
        );
    }

    private function generateUniqueDepartmentCode(string $name, ?int $ignoreDepartmentId = null): string
    {
        $base = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 5));

        if ($base === '') {
            $base = 'DEPT';
        }

        $code = $base;
        $counter = 1;

        while (
            Department::where('code', $code)
                ->when($ignoreDepartmentId, function ($query) use ($ignoreDepartmentId) {
                    $query->where('id', '!=', $ignoreDepartmentId);
                })
                ->exists()
        ) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }

    private function generateUniquePositionCode($name)
    {
        $base = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 5));

        if ($base === '') {
            $base = 'POS';
        }

        $code = $base;
        $counter = 1;

        while (Position::where('code', $code)->exists()) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }
}