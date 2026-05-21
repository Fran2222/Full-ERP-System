<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('hr.leave-types.view'), 403);

    $leaveTypes = LeaveType::orderBy('is_paid', 'desc')
        ->orderBy('status', 'asc')
        ->orderBy('name', 'asc')
        ->get();

        return view('hr.leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('hr.leave-types.create'), 403);

        return view('hr.leave-types.form', [
            'leaveType' => new LeaveType(),
            'isEdit' => false,
        ]);
    }

    public function store(LeaveTypeRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('hr.leave-types.create'), 403);

        LeaveType::create($this->payload($request));

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leave_type): View
    {
        abort_unless(auth()->user()->can('hr.leave-types.edit'), 403);

        return view('hr.leave-types.form', [
            'leaveType' => $leave_type,
            'isEdit' => true,
        ]);
    }

    public function update(LeaveTypeRequest $request, LeaveType $leave_type): RedirectResponse
    {
        abort_unless(auth()->user()->can('hr.leave-types.edit'), 403);

        $leave_type->update($this->payload($request, $leave_type));

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leave_type): RedirectResponse
    {
        abort_unless(auth()->user()->can('hr.leave-types.delete'), 403);

        $leave_type->delete();

        return redirect()
            ->route('hr.leave-types.index')
            ->with('success', 'Leave type deleted successfully.');
    }

    protected function payload(LeaveTypeRequest $request, ?LeaveType $leaveType = null): array
    {
        $validated = $request->validated();

        $name = trim($validated['name']);
        $baseCode = Str::upper(Str::slug($name, '_'));
        $code = $baseCode;
        $counter = 1;

        while (LeaveType::where('code', $code)
            ->when($leaveType, fn ($query) => $query->where('id', '!=', $leaveType->id))
            ->exists()) {
            $counter++;
            $code = $baseCode . '_' . $counter;
        }

        return [
            'name' => $name,
            'code' => $code,
            'description' => $validated['description'] ?? $name,
            'default_credits' => $validated['default_credits'],
            'is_paid' => (int) $validated['is_paid'],
            'status' => $validated['status'],
        ];
    }
}