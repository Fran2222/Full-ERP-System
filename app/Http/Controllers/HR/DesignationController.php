<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $allowedSorts = ['id', 'name', 'department'];

        $sort = (string) $request->input('sort', 'id');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $direction = strtolower((string) $request->input('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Position::query()
            ->with('department')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.*');

        if ($search !== '') {
            $normalizedSearch = mb_strtolower($search);
            $normalizedSearch = preg_replace('/\s+/', ' ', $normalizedSearch);
            $searchPattern = '%' . str_replace(' ', '%', $normalizedSearch) . '%';

            $query->whereRaw("
                LOWER(
                    COALESCE(positions.name, '') || ' ' ||
                    COALESCE(positions.status, '') || ' ' ||
                    COALESCE(departments.name, '')
                ) LIKE ?
            ", [$searchPattern]);
        }

        if ($sort === 'department') {
            $query->orderByRaw("LOWER(COALESCE(departments.name, '')) {$direction}");
        } elseif ($sort === 'name') {
            $query->orderByRaw("LOWER(COALESCE(positions.name, '')) {$direction}");
        } else {
            $query->orderBy('positions.id', $direction);
        }

        $designations = $query
            ->orderBy('positions.id', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $departments = Department::orderBy('name')->get();

        return view('designations.index', compact(
            'designations',
            'departments',
            'search',
            'perPage',
            'sort',
            'direction'
        ));
    }

    public function create()
    {
        $designation = new Position([
            'status' => 'active',
        ]);

        $departments = Department::orderBy('name')->get();

        return view('designations.form', compact('designation', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        Position::create([
            'name' => $data['name'],
            'code' => $this->generateUniquePositionCode($data['name']),
            'department_id' => $data['department_id'],
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function edit(Position $designation)
    {
        $departments = Department::orderBy('name')->get();

        return view('designations.form', compact('designation', 'departments'));
    }

    public function update(Request $request, Position $designation)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $designation->update([
            'name' => $data['name'],
            'code' => $this->generateUniquePositionCode($data['name'], $designation->id),
            'department_id' => $data['department_id'],
            'status' => $data['status'] ?? $designation->status ?? 'active',
        ]);

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Request $request, Position $designation)
    {
        $designation->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Designation deleted successfully.',
            ]);
        }

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }

    private function generateUniquePositionCode(string $name, ?int $ignorePositionId = null): string
    {
        $base = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 5));

        if ($base === '') {
            $base = 'POS';
        }

        $code = $base;
        $counter = 1;

        while (
            Position::where('code', $code)
                ->when($ignorePositionId, function ($query) use ($ignorePositionId) {
                    $query->where('id', '!=', $ignorePositionId);
                })
                ->exists()
        ) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }
}