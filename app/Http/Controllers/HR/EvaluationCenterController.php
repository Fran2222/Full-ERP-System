<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EvaluationTask;
use App\Models\EvaluationTaskAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationCenterController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $search = trim((string) $request->input('search', ''));

        $tasks = $this->evaluationTaskQuery()
            ->when($search !== '', function ($query) use ($search) {
                $this->applyEvaluationTaskSearch($query, $search);
            })
            ->latest()
            ->paginate(10);

        $tasks->appends($request->query());

        return view('hr.evaluation.center.index', compact('tasks', 'search'));
    }

    public function myEvaluations()
    {
        $user = auth()->user();

        abort_unless(
            $user->can('hr.evaluation.view') || $user->can('hr.evaluation.own.result.view'),
            403
        );

        $tasks = $this->evaluationTaskQuery()
            ->where('evaluator_user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('hr.evaluation.my.index', compact('tasks'));
    }

    public function evaluate(EvaluationTask $task)
    {
        $this->authorizeEvaluatorTask($task);

        $task->load([
            'form.sections.questions.scales',
            'answers',
            'branch',
            'assignedEmployee.user.branch',
            'assignedEmployee.user.department',
            'assignedEmployee.position.department',
            'evaluator',
        ]);

        if ($task->status === 'pending') {
            $task->update(['status' => 'in_progress']);
        }

        $answers = $task->answers->keyBy('evaluation_form_question_id');

        return view('hr.evaluation.my.evaluate', compact('task', 'answers'));
    }

    public function submitEvaluation(Request $request, EvaluationTask $task)
    {
        $this->authorizeEvaluatorTask($task);

        $task->load('form.sections.questions');

        $questionIds = $task->form
            ->sections
            ->flatMap(fn ($section) => $section->questions)
            ->pluck('id')
            ->values()
            ->all();

        $rules = [
            'scores' => ['required', 'array'],
            'remarks' => ['nullable', 'array'],
        ];

        foreach ($questionIds as $questionId) {
            $rules["scores.$questionId"] = ['required', 'integer', 'min:1', 'max:10'];
            $rules["remarks.$questionId"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($task, $validated) {
            foreach ($validated['scores'] as $questionId => $score) {
                EvaluationTaskAnswer::updateOrCreate(
                    [
                        'evaluation_task_id' => $task->id,
                        'evaluation_form_question_id' => $questionId,
                    ],
                    [
                        'score' => $score,
                        'remarks' => $validated['remarks'][$questionId] ?? null,
                    ]
                );
            }

            $performanceScore = $this->computePerformanceScore($task->fresh()->load('form.sections.questions', 'answers'));

            $task->update([
                'status' => 'submitted',
                'performance_score' => $performanceScore,
            ]);
        });

        return redirect()
            ->route('hr.evaluation.index')
            ->with('success', 'Evaluation submitted successfully.');
    }

    private function authorizeEvaluatorTask(EvaluationTask $task): void
    {
        $user = auth()->user();

        abort_unless(
            $user->can('hr.evaluation.view') ||
            (
                $user->can('hr.evaluation.own.result.view') &&
                (int) $task->evaluator_user_id === (int) $user->id
            ),
            403
        );
    }

    private function computePerformanceScore(EvaluationTask $task): float
    {
        $answers = $task->answers->keyBy('evaluation_form_question_id');

        $weightedTotal = 0;

        foreach ($task->form->sections as $section) {
            $sectionQuestions = $section->questions;

            if ($sectionQuestions->count() === 0) {
                continue;
            }

            $sectionScoreTotal = 0;

            foreach ($sectionQuestions as $question) {
                $sectionScoreTotal += (int) optional($answers->get($question->id))->score;
            }

            $sectionAverage = $sectionScoreTotal / $sectionQuestions->count();

            $weightedTotal += $sectionAverage * ((float) $section->weight);
        }

        return round($weightedTotal / 10, 2);
    }

    public function myResults()
    {
        $user = auth()->user();

        abort_unless($user->can('hr.evaluation.own.result.view'), 403);

        $tasks = $this->evaluationTaskQuery()
            ->whereHas('assignedEmployee', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['submitted', 'completed', 'reviewed'])
            ->latest()
            ->paginate(10);

        return view('hr.evaluation.my.results', compact('tasks'));
    }

    public function showMyResult(EvaluationTask $task)
    {
        $user = auth()->user();

        abort_unless($user->can('hr.evaluation.own.result.view'), 403);

        abort_unless(
            optional($task->assignedEmployee)->user_id === $user->id,
            403
        );

        abort_unless(
            in_array(strtolower($task->status), ['submitted', 'completed', 'reviewed']),
            403
        );

        $task->load([
            'form.sections.questions.scales',
            'answers.question',
            'branch',
            'assignedEmployee.user.branch',
            'assignedEmployee.user.department',
            'assignedEmployee.position.department',
        ]);

        $answers = $task->answers->keyBy('evaluation_form_question_id');

        return view('hr.evaluation.my.result-show', compact('task', 'answers'));
    }

    private function evaluationTaskQuery()
    {
        return EvaluationTask::query()
            ->with([
                'form',
                'branch',
                'assignedEmployee.user.branch',
                'assignedEmployee.user.department',
                'assignedEmployee.position.department',
                'evaluator',
            ]);
    }

    private function applyEvaluationTaskSearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'ilike', "%{$search}%")
                ->orWhere('status', 'ilike', "%{$search}%")
                ->orWhereHas('form', function ($formQuery) use ($search) {
                    $formQuery->where('title', 'ilike', "%{$search}%")
                        ->orWhere('task_title', 'ilike', "%{$search}%")
                        ->orWhere('instructions', 'ilike', "%{$search}%");
                })
                ->orWhereHas('branch', function ($branchQuery) use ($search) {
                    $branchQuery->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('assignedEmployee.user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('middle_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhereRaw("LOWER(COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(COALESCE(last_name, '') || ', ' || COALESCE(first_name, '')) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                ->orWhereHas('assignedEmployee.user.branch', function ($branchQuery) use ($search) {
                    $branchQuery->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('assignedEmployee.user.department', function ($departmentQuery) use ($search) {
                    $departmentQuery->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('assignedEmployee.position.department', function ($departmentQuery) use ($search) {
                    $departmentQuery->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('evaluator', function ($evaluatorQuery) use ($search) {
                    $evaluatorQuery->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('middle_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhereRaw("LOWER(COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(COALESCE(last_name, '') || ', ' || COALESCE(first_name, '')) LIKE ?", ['%' . strtolower($search) . '%']);
                });
        });
    }
}
