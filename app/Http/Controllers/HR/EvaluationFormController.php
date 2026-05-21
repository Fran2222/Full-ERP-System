<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\EmployeeProfile;
use App\Models\EvaluationForm;
use App\Models\EvaluationTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluationFormController extends Controller
{
    private array $defaultScales = [
        ['label' => 'Unsatisfactory', 'min_score' => 1, 'max_score' => 2],
        ['label' => 'Needs Improvement', 'min_score' => 3, 'max_score' => 4],
        ['label' => 'Satisfactory', 'min_score' => 5, 'max_score' => 6],
        ['label' => 'Good', 'min_score' => 7, 'max_score' => 8],
        ['label' => 'Excellent', 'min_score' => 9, 'max_score' => 10],
    ];

    public function index()
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $forms = EvaluationForm::withCount(['sections', 'questions', 'tasks'])
            ->latest()
            ->paginate(10);

        return view('hr.evaluation.forms.index', compact('forms'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $form = new EvaluationForm(['status' => 'draft']);
        $defaultScales = $this->defaultScales;

        return view('hr.evaluation.forms.form', compact('form', 'defaultScales'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $validated = $this->validateForm($request);

        DB::transaction(function () use ($validated) {
            $form = EvaluationForm::create([
            'title' => $validated['title'],
            'task_title' => $validated['task_title'],
            'instructions' => $validated['instructions'] ?? null,
            'status' => $validated['status'],
        ]);

            $this->saveSections($form, $validated['sections']);
        });

        return redirect()->route('hr.evaluation.forms.index')
            ->with('success', 'Evaluation form created successfully.');
    }

    public function show(EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.view'), 403);

        $form->load('sections.questions.scales');

        return view('hr.evaluation.forms.show', compact('form'));
    }

    public function edit(EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.edit'), 403);

        $form->load('sections.questions.scales');
        $defaultScales = $this->defaultScales;

        return view('hr.evaluation.forms.form', compact('form', 'defaultScales'));
    }

    public function update(Request $request, EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.edit'), 403);

        $validated = $this->validateForm($request);

        DB::transaction(function () use ($form, $validated) {
            $form->update([
            'title' => $validated['title'],
            'task_title' => $validated['task_title'],
            'instructions' => $validated['instructions'] ?? null,
            'status' => $validated['status'],
        ]);

            $form->sections()->delete();
            $this->saveSections($form, $validated['sections']);
        });

        return redirect()->route('hr.evaluation.forms.index')
            ->with('success', 'Evaluation form updated successfully.');
    }

    public function duplicate(Request $request, EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $form->load('sections.questions.scales');

        $copy = DB::transaction(function () use ($form) {
            $copy = EvaluationForm::create([
                'title' => $form->title . ' Copy',
                'task_title' => $form->task_title ? $form->task_title . ' Copy' : $form->title . ' Copy',
                'instructions' => $form->instructions,
                'status' => 'draft',
            ]);

            foreach ($form->sections as $section) {
                $newSection = $copy->sections()->create([
                    'title' => $section->title,
                    'weight' => $section->weight,
                    'sort_order' => $section->sort_order,
                ]);

                foreach ($section->questions as $question) {
                    $newQuestion = $newSection->questions()->create([
                        'title' => $question->title,
                        'question' => $question->question,
                        'sort_order' => $question->sort_order,
                    ]);

                    foreach ($question->scales as $scale) {
                        $newQuestion->scales()->create($scale->only(['label', 'min_score', 'max_score', 'description']));
                    }
                }
            }

            return $copy;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluation form duplicated successfully.',
                'form_id' => $copy->id,
            ]);
        }

        return redirect()->route('hr.evaluation.forms.index')
            ->with('success', 'Evaluation form duplicated successfully.');
    }

    public function assign(EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $form->load('sections.questions');

        $superAdminRoleNames = [
            'super-admin',
            'super admin',
            'superadmin',
            'super_admin',
        ];

        $branches = Branch::where('status', 'active')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Exclude Super Admin Users
        |--------------------------------------------------------------------------
        | The assign.blade.php uses $employees for BOTH:
        | 1. Assigned To / Employees to Evaluate
        | 2. Evaluator(s)
        |
        | So filtering $employees here removes Super Admin from both dropdowns.
        */
        $employees = EmployeeProfile::with(['user.branch', 'user.department', 'user.roles', 'position'])
            ->whereHas('user')
            ->whereDoesntHave('user.roles', function ($roleQuery) use ($superAdminRoleNames) {
                $roleQuery->whereIn(DB::raw('LOWER(name)'), $superAdminRoleNames);
            })
            ->orderBy('employee_id')
            ->get();

        $evaluators = User::where('status', 'active')
            ->whereDoesntHave('roles', function ($roleQuery) use ($superAdminRoleNames) {
                $roleQuery->whereIn(DB::raw('LOWER(name)'), $superAdminRoleNames);
            })
            ->orderBy('first_name')
            ->get();

        return view('hr.evaluation.forms.assign', compact('form', 'branches', 'employees', 'evaluators'));
    }

    public function storeAssignment(Request $request, EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.create'), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_to_employee_profile_ids' => ['required', 'array', 'min:1'],
            'assigned_to_employee_profile_ids.*' => ['required', 'exists:employee_profiles,id'],
            'evaluator_user_ids' => ['required', 'array', 'min:1'],
            'evaluator_user_ids.*' => ['required', 'exists:users,id'],
            'description' => ['nullable', 'string'],
        ]);

        $superAdminRoleNames = [
            'super-admin',
            'super admin',
            'superadmin',
            'super_admin',
        ];

        /*
        |--------------------------------------------------------------------------
        | Backend Protection
        |--------------------------------------------------------------------------
        | Even if someone manipulates the HTML value manually, Super Admin users
        | still cannot be assigned or selected as evaluators.
        */
        $validEmployeeProfileCount = EmployeeProfile::query()
            ->whereIn('id', $validated['assigned_to_employee_profile_ids'])
            ->whereHas('user')
            ->whereDoesntHave('user.roles', function ($roleQuery) use ($superAdminRoleNames) {
                $roleQuery->whereIn(DB::raw('LOWER(name)'), $superAdminRoleNames);
            })
            ->count();

        if ($validEmployeeProfileCount !== count(array_unique($validated['assigned_to_employee_profile_ids']))) {
            throw ValidationException::withMessages([
                'assigned_to_employee_profile_ids' => 'Super Admin users cannot be assigned for evaluation.',
            ]);
        }

        $validEvaluatorCount = User::query()
            ->whereIn('id', $validated['evaluator_user_ids'])
            ->whereDoesntHave('roles', function ($roleQuery) use ($superAdminRoleNames) {
                $roleQuery->whereIn(DB::raw('LOWER(name)'), $superAdminRoleNames);
            })
            ->count();

        if ($validEvaluatorCount !== count(array_unique($validated['evaluator_user_ids']))) {
            throw ValidationException::withMessages([
                'evaluator_user_ids' => 'Super Admin users cannot be selected as evaluators.',
            ]);
        }

        $createdCount = 0;

        DB::transaction(function () use ($validated, $form, &$createdCount) {
            foreach ($validated['assigned_to_employee_profile_ids'] as $employeeProfileId) {
                foreach ($validated['evaluator_user_ids'] as $evaluatorUserId) {
                    EvaluationTask::create([
                        'evaluation_form_id' => $form->id,
                        'title' => $validated['title'],
                        'due_date' => $validated['due_date'] ?? null,
                        'branch_id' => $validated['branch_id'] ?? null,
                        'assigned_to_employee_profile_id' => $employeeProfileId,
                        'evaluator_user_id' => $evaluatorUserId,
                        'description' => $validated['description'] ?? null,
                        'status' => 'pending',
                    ]);

                    $createdCount++;
                }
            }
        });

        return redirect()->route('hr.evaluation.center.index')
            ->with('success', $createdCount . ' evaluation task(s) assigned successfully.');
    }

    public function destroy(Request $request, EvaluationForm $form)
    {
        abort_unless(auth()->user()->can('hr.evaluation.delete'), 403);

        if ($form->tasks()->exists()) {
            $form->update(['status' => 'archived']);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Form has tasks already, so it was archived instead of deleted.',
                    'action' => 'archived',
                    'form_id' => $form->id,
                ]);
            }

            return redirect()->route('hr.evaluation.forms.index')
                ->with('success', 'Form has tasks already, so it was archived instead of deleted.');
        }

        $deletedFormId = $form->id;
        $form->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluation form deleted successfully.',
                'action' => 'deleted',
                'form_id' => $deletedFormId,
            ]);
        }

        return redirect()->route('hr.evaluation.forms.index')
            ->with('success', 'Evaluation form deleted successfully.');
    }

    private function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'task_title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,archived'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'sections.*.questions' => ['required', 'array', 'min:1'],
            'sections.*.questions.*.title' => ['required', 'string', 'max:255'],
            'sections.*.questions.*.question' => ['nullable', 'string'],
            'sections.*.questions.*.scales' => ['required', 'array', 'min:5'],
            'sections.*.questions.*.scales.*.description' => ['nullable', 'string'],
        ]);

        $totalWeight = collect($validated['sections'])->sum(fn ($section) => (float) $section['weight']);

        if (round($totalWeight, 2) !== 100.00) {
            throw ValidationException::withMessages([
                'sections' => 'Total section weight must be exactly 100%. Current total is ' . number_format($totalWeight, 2) . '%.',
            ]);
        }

        return $validated;
    }

    private function saveSections(EvaluationForm $form, array $sections): void
    {
        foreach ($sections as $sectionIndex => $sectionData) {
            $section = $form->sections()->create([
                'title' => $sectionData['title'],
                'weight' => $sectionData['weight'],
                'sort_order' => $sectionIndex + 1,
            ]);

            foreach ($sectionData['questions'] as $questionIndex => $questionData) {
                $question = $section->questions()->create([
                    'title' => $questionData['title'],
                    'question' => $questionData['question'] ?? null,
                    'sort_order' => $questionIndex + 1,
                ]);

                foreach ($this->defaultScales as $scaleIndex => $scale) {
                    $question->scales()->create([
                        'label' => $scale['label'],
                        'min_score' => $scale['min_score'],
                        'max_score' => $scale['max_score'],
                        'description' => $questionData['scales'][$scaleIndex]['description'] ?? null,
                    ]);
                }
            }
        }
    }
}
