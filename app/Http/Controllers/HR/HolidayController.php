<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $selectedYear = $request->get('year') ?: now()->format('Y');
        $selectedBranchId = $request->get('branch_id');
        $search = trim((string) $request->get('search'));

        $holidays = Holiday::with('branch')
            ->when($selectedYear, function ($query) use ($selectedYear) {
                $query->whereYear('holiday_date', $selectedYear);
            })
            ->when($selectedBranchId !== null && $selectedBranchId !== '', function ($query) use ($selectedBranchId) {
                if ($selectedBranchId === 'all') {
                    $query->whereNull('branch_id');
                } else {
                    $query->where('branch_id', $selectedBranchId);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%')
                        ->orWhere('remarks', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('holiday_date')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('hr.holidays.index', [
            'holidays' => $holidays,
            'branches' => Branch::orderBy('name')->get(),
            'typeOptions' => Holiday::typeOptions(),
            'selectedYear' => $selectedYear,
            'selectedBranchId' => $selectedBranchId,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'type' => ['required', 'in:' . implode(',', array_keys(Holiday::typeOptions()))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_paid' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['branch_id'] = $validated['branch_id'] ?? null;
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Holiday::updateOrCreate(
            [
                'holiday_date' => $validated['holiday_date'],
                'name' => $validated['name'],
                'branch_id' => $validated['branch_id'],
            ],
            $validated
        );

        return redirect()->route('hr.holidays.index', ['year' => substr($validated['holiday_date'], 0, 4)])
            ->with('success', 'Holiday saved successfully. Attendance will now auto-detect this date.');
    }

    public function edit(Holiday $holiday)
    {
        $this->authorizeAccess();

        return view('hr.holidays.edit', [
            'holiday' => $holiday,
            'branches' => Branch::orderBy('name')->get(),
            'typeOptions' => Holiday::typeOptions(),
        ]);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'type' => ['required', 'in:' . implode(',', array_keys(Holiday::typeOptions()))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_paid' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['branch_id'] = $validated['branch_id'] ?? null;
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['is_active'] = $request->boolean('is_active');

        $holiday->update($validated);

        return redirect()->route('hr.holidays.index', ['year' => $holiday->holiday_date->format('Y')])
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $this->authorizeAccess();

        $holiday->delete();

        return redirect()->route('hr.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            auth()->user()->can('hr.attendance.view')
            || auth()->user()->can('hr.payroll.view')
            || auth()->user()->can('hr.view'),
            403
        );
    }
}
