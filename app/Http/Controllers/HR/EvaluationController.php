<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()->can('hr.evaluation.view') || auth()->user()->can('hr.evaluation.own.result.view'),
            403
        );

        $canManageEvaluations = auth()->user()->can('hr.evaluation.view');

        if ($canManageEvaluations) {
            // HR/Admin = all evaluations.
            $evaluations = Evaluation::with(['employeeProfile.user', 'items'])
                ->latest()
                ->paginate(10);

            $employees = EmployeeProfile::with('user')->get();
        } else {
            // Employee/User = own evaluation result only.
            $employeeProfileId = $this->currentEmployeeProfileId();

            $evaluations = Evaluation::with(['employeeProfile.user', 'items'])
                ->where('employee_profile_id', $employeeProfileId)
                ->latest()
                ->paginate(10);

            $employees = collect();
        }

        return view('hr.evaluation.index', compact(
            'evaluations',
            'employees',
            'canManageEvaluations'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $validated = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'evaluation_date' => ['required', 'date'],
            'period' => ['nullable', 'string'],
            'overall_remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $evaluation = Evaluation::create($validated);

            $criteriaList = [
                'Attendance',
                'Work Quality',
                'Productivity',
                'Communication',
                'Teamwork',
            ];

            foreach ($criteriaList as $criteria) {
                $fieldKey = str_replace(' ', '_', $criteria);

                EvaluationItem::create([
                    'evaluation_id' => $evaluation->id,
                    'criteria' => $criteria,
                    'score' => (int) $request->input('score_' . $fieldKey, 0),
                    'remarks' => $request->input('remarks_' . $fieldKey),
                ]);
            }
        });

        return redirect()->route('hr.evaluation.index')
            ->with('success', 'Evaluation saved successfully.');
    }

    public function show(Evaluation $evaluation)
    {
        abort_unless(
            auth()->user()->can('hr.evaluation.view')
            || auth()->user()->can('hr.evaluation.result.view')
            || auth()->user()->can('hr.evaluation.own.result.view'),
            403
        );

        $this->authorizeOwnEvaluation($evaluation);

        $evaluation->load([
            'employeeProfile.user.department',
            'employeeProfile.position',
            'items',
        ]);

        return view('hr.evaluation.show', compact('evaluation'));
    }

    public function downloadPdf(Evaluation $evaluation)
    {
        abort_unless(
            auth()->user()->can('hr.evaluation.pdf') || auth()->user()->can('hr.evaluation.own.result.view'),
            403
        );

        $this->authorizeOwnEvaluation($evaluation);

        $evaluation->load([
            'employeeProfile.user.department',
            'employeeProfile.position',
            'items',
        ]);

        $pdf = Pdf::loadView('hr.evaluation.pdf', compact('evaluation'))
            ->setPaper('a4', 'portrait');

        $employeeName = str_replace(' ', '_', trim(
            ($evaluation->employeeProfile->user->first_name ?? '') . '_' .
            ($evaluation->employeeProfile->user->last_name ?? '')
        ));

        return $pdf->download('evaluation_' . $employeeName . '.pdf');
    }

    private function authorizeOwnEvaluation(Evaluation $evaluation): void
    {
        // HR/Admin can access all.
        if (
            auth()->user()->can('hr.evaluation.view')
            || auth()->user()->can('hr.evaluation.result.view')
            || auth()->user()->can('hr.evaluation.pdf')
        ) {
            return;
        }

        // Employee/User can access own evaluation only.
        $employeeProfileId = $this->currentEmployeeProfileId();

        abort_unless((int) $evaluation->employee_profile_id === $employeeProfileId, 403);
    }

    private function currentEmployeeProfileId(): int
    {
        $employeeProfile = auth()->user()->employeeProfile;

        abort_unless($employeeProfile, 403);

        return (int) $employeeProfile->id;
    }
}