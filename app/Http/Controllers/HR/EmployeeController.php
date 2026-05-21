<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\EmployeeDocument;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\EvaluationTask;
use App\Models\EmployeeTraining;
use App\Models\EmployeeMovement;
use App\Models\EmployeeMemo;
use App\Models\EmployeeExitRecord;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $search = trim((string) $request->input('search', ''));

        $allowedSorts = ['id', 'name', 'email', 'branch', 'department', 'designation'];

        $sort = (string) $request->input('sort', 'id');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $direction = strtolower((string) $request->input('direction', 'asc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $employees = User::query()
            ->select('users.*')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
            ->leftJoin('positions', 'employee_profiles.position_id', '=', 'positions.id')
            ->with(['branch', 'department', 'employeeProfile.position', 'employeeProfile.supervisor'])
            ->whereNotNull('employee_profiles.id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';

                    $q->where('users.first_name', 'ilike', $searchLike)
                        ->orWhere('users.middle_name', 'ilike', $searchLike)
                        ->orWhere('users.last_name', 'ilike', $searchLike)
                        ->orWhereRaw("LOWER(TRIM(COALESCE(users.first_name, '') || ' ' || COALESCE(users.middle_name, '') || ' ' || COALESCE(users.last_name, '') || ' ' || COALESCE(users.suffix, ''))) LIKE ?", [mb_strtolower($searchLike)])
                        ->orWhereRaw("LOWER(TRIM(COALESCE(users.first_name, '') || ' ' || COALESCE(users.last_name, ''))) LIKE ?", [mb_strtolower($searchLike)])
                        ->orWhere('users.username', 'ilike', $searchLike)
                        ->orWhere('users.email', 'ilike', $searchLike)
                        ->orWhere('employee_profiles.employee_id', 'ilike', $searchLike)
                        ->orWhere('employee_profiles.province', 'ilike', $searchLike)
                        ->orWhere('employee_profiles.city', 'ilike', $searchLike)
                        ->orWhere('employee_profiles.barangay', 'ilike', $searchLike)
                        ->orWhere('positions.name', 'ilike', $searchLike)
                        ->orWhere('departments.name', 'ilike', $searchLike)
                        ->orWhere('branches.name', 'ilike', $searchLike);
                });
            });

        if ($sort === 'name') {
            $employees->orderByRaw("LOWER(COALESCE(users.first_name, '') || ' ' || COALESCE(users.last_name, '')) {$direction}");
        } elseif ($sort === 'email') {
            $employees->orderByRaw("LOWER(COALESCE(users.email, '')) {$direction}");
        } elseif ($sort === 'branch') {
            $employees->orderByRaw("LOWER(COALESCE(branches.name, '')) {$direction}");
        } elseif ($sort === 'department') {
            $employees->orderByRaw("LOWER(COALESCE(departments.name, '')) {$direction}");
        } elseif ($sort === 'designation') {
            $employees->orderByRaw("LOWER(COALESCE(positions.name, '')) {$direction}");
        } else {
            $employees->orderBy('users.id', $direction);
        }

        $employees = $employees
            ->orderBy('users.id', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        return view('hr.employees.index', compact('employees', 'search', 'perPage', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('hr.employees.form', $this->formData(new User(), new EmployeeProfile(), false));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => $validated['username'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'branch_id' => $validated['branch_id'],
                'department_id' => $validated['department_id'],
                'status' => $validated['employment_status'],
                'user_type' => 'user',
                'password' => Hash::make('password'),
            ]);

            $user->employeeProfile()->create($this->profilePayload($validated));

            $this->renumberEmployeeCodesByHireDate();
        });

        return redirect()->route('hr.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(User $employee): View
    {
        $employee->load([
            'branch',
            'department',
            'employeeProfile.position',
            'employeeProfile.supervisor',
            'employeeProfile.documents',
            'employeeProfile.leaveBalances.leaveType',
        ]);

        abort_unless($employee->employeeProfile, 404);

        $profile = $employee->employeeProfile;
        $currentYear = (int) now()->year;

        $this->syncEmployeeLeaveBalances($employee, $profile, $currentYear);

        $profile->load('leaveBalances.leaveType');

        $leaveBalances = $profile->leaveBalances
            ->where('year', $currentYear)
            ->sortBy(fn ($balance) => optional($balance->leaveType)->name ?? '');

        $leaveRequests = LeaveRequest::query()
            ->with(['leaveType', 'reviewer'])
            ->where('user_id', $employee->id)
            ->latest('start_datetime')
            ->get();

        $leaveSummary = [
            'allocated' => (float) $leaveBalances->sum('allocated'),
            'used' => (float) $leaveBalances->sum('used'),
            'remaining' => (float) $leaveBalances->sum('remaining'),
            'pending' => $leaveRequests->where('status', 'pending')->count(),
            'approved' => $leaveRequests->where('status', 'approved')->count(),
            'rejected' => $leaveRequests->where('status', 'rejected')->count(),
        ];

        $evaluationTasks = EvaluationTask::query()
            ->with(['form', 'evaluator'])
            ->where('assigned_to_employee_profile_id', $profile->id)
            ->latest('updated_at')
            ->get();

        $scoredEvaluationTasks = $evaluationTasks
            ->whereIn('status', ['submitted', 'completed', 'reviewed'])
            ->filter(fn ($task) => ! is_null($task->performance_score));

        $evaluationSummary = [
            'total' => $evaluationTasks->count(),
            'pending' => $evaluationTasks->where('status', 'pending')->count(),
            'submitted' => $evaluationTasks->whereIn('status', ['submitted', 'completed', 'reviewed'])->count(),
            'average' => $scoredEvaluationTasks->count()
                ? round((float) $scoredEvaluationTasks->avg('performance_score'), 2)
                : null,
        ];

        $trainings = EmployeeTraining::query()
            ->where('employee_profile_id', $profile->id)
            ->latest('completed_at')
            ->latest('created_at')
            ->get();

        $trainingSummary = [
            'total' => $trainings->count(),
            'valid' => $trainings->filter(fn ($training) => $training->status_key === 'valid')->count(),
            'expiring' => $trainings->filter(fn ($training) => $training->status_key === 'expiring_soon')->count(),
            'expired' => $trainings->filter(fn ($training) => $training->status_key === 'expired')->count(),
        ];

        $movements = EmployeeMovement::query()
            ->with('encoder')
            ->where('employee_profile_id', $profile->id)
            ->latest('effective_date')
            ->latest('created_at')
            ->get();

        $movementSummary = [
            'total' => $movements->count(),
            'promotions' => $movements->where('movement_type', 'promotion')->count(),
            'transfers' => $movements->whereIn('movement_type', ['transfer', 'branch_change', 'department_change'])->count(),
            'latest' => $movements->first(),
        ];

        $memos = EmployeeMemo::query()
            ->with('issuer')
            ->where('employee_profile_id', $profile->id)
            ->latest('issue_date')
            ->latest('created_at')
            ->get();

        $memoSummary = [
            'total' => $memos->count(),
            'open' => $memos->whereIn('status', ['open', 'pending'])->count(),
            'closed' => $memos->where('status', 'closed')->count(),
            'latest' => $memos->first(),
        ];

        $exitRecords = EmployeeExitRecord::query()
            ->with('encoder')
            ->where('employee_profile_id', $profile->id)
            ->latest('last_working_day')
            ->latest('created_at')
            ->get();

        $latestExitRecord = $exitRecords->first();

        $exitSummary = [
            'total' => $exitRecords->count(),
            'latest' => $latestExitRecord,
            'exit_status' => $latestExitRecord ? 'Recorded' : 'No Exit Record',
            'clearance_status' => $latestExitRecord?->clearance_status_label ?? 'Not Started',
            'final_pay_status' => $latestExitRecord?->final_pay_status_label ?? 'Not Started',
            'last_working_day' => $latestExitRecord?->last_working_day,
        ];

        return view('hr.employees.show', compact(
            'employee',
            'leaveBalances',
            'leaveRequests',
            'leaveSummary',
            'evaluationTasks',
            'evaluationSummary',
            'trainings',
            'trainingSummary',
            'movements',
            'movementSummary',
            'memos',
            'memoSummary',
            'exitRecords',
            'exitSummary'
        ));
    }

    public function edit(User $employee): View
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        return view('hr.employees.form', $this->formData($employee, $employee->employeeProfile, true));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        $validated = $this->validatedData($request, $employee->id, $employee->employeeProfile->id);

        DB::transaction(function () use ($employee, $validated) {
            $employee->update([
                'username' => $validated['username'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'branch_id' => $validated['branch_id'],
                'department_id' => $validated['department_id'],
                'status' => $validated['employment_status'],
            ]);

            $employee->employeeProfile()->update(
                $this->profilePayload($validated, $employee->employeeProfile)
            );

            $this->renumberEmployeeCodesByHireDate();
        });

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function getCities(Request $request): JsonResponse
    {
        $provinceId = (int) $request->query('province_id');

        if ($provinceId <= 0) {
            return response()->json([]);
        }

        if (Schema::hasTable('city')) {
            $cities = DB::table('city')
                ->select('cityid as id', 'cityname as name')
                ->where('prid', $provinceId)
                ->orderBy('cityname')
                ->get();

            return response()->json($cities);
        }

        return response()->json($this->cityOptions($provinceId));
    }

    public function getBarangays(Request $request): JsonResponse
    {
        $cityId = (int) $request->query('city_id');

        if ($cityId <= 0) {
            return response()->json([]);
        }

        if (Schema::hasTable('brgy')) {
            $barangays = DB::table('brgy')
                ->select('brgyid as id', 'brgyname as name')
                ->where('cityid', $cityId)
                ->orderBy('brgyname')
                ->get();

            return response()->json($barangays);
        }

        return response()->json($this->barangayOptions($cityId));
    }

    public function uploadDocument(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|max:255',
            'expiration_date' => ['nullable', 'date'],
            'file' => 'required|file|max:5120',
        ]);

        $employee = User::findOrFail($employeeId);
        $profile = $employee->employeeProfile;

        abort_unless($profile, 404);

        $file = $request->file('file');
        $path = $file->store('employee_documents', 'public');

        EmployeeDocument::create([
            'employee_profile_id' => $profile->id,
            'document_type' => $validated['document_type'],
            'document_name' => $validated['document_type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'expiration_date' => $validated['expiration_date'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function downloadDocument($id)
    {
        $document = EmployeeDocument::findOrFail($id);

        return $this->downloadFromPublicDisk(
            $document->file_path,
            $document->file_name
        );
    }

    public function deleteDocument($id)
    {
        $document = EmployeeDocument::findOrFail($id);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    public function storeTraining(Request $request, User $employee): RedirectResponse
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        $validated = $request->validate([
            'training_title' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'completed_at' => ['required', 'date'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:completed_at'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'certificate_file' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $certificatePath = null;
        $certificateFileName = null;

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $certificatePath = $file->store('employee_trainings', 'public');
            $certificateFileName = $file->getClientOriginalName();
        }

        EmployeeTraining::create([
            'employee_profile_id' => $employee->employeeProfile->id,
            'training_title' => $validated['training_title'],
            'provider' => $validated['provider'] ?? null,
            'completed_at' => $validated['completed_at'],
            'certificate_number' => $validated['certificate_number'] ?? null,
            'expiration_date' => $validated['expiration_date'] ?? null,
            'certificate_path' => $certificatePath,
            'certificate_file_name' => $certificateFileName,
            'remarks' => $validated['remarks'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('hr.employees.show', ['employee' => $employee->id, 'tab' => 'training'])
            ->with('success', 'Training record added successfully.');
    }

    public function downloadTrainingCertificate(EmployeeTraining $training)
    {
        return $this->downloadFromPublicDisk(
            $training->certificate_path,
            $training->certificate_file_name
        );
    }

    public function deleteTraining(EmployeeTraining $training): RedirectResponse
    {
        if ($training->certificate_path) {
            Storage::disk('public')->delete($training->certificate_path);
        }

        $employeeId = optional($training->employeeProfile)->user_id;
        $training->delete();

        return redirect()
            ->route('hr.employees.show', ['employee' => $employeeId, 'tab' => 'training'])
            ->with('success', 'Training record deleted successfully.');
    }

    public function storeMemo(Request $request, User $employee): RedirectResponse
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        $validated = $request->validate([
            'memo_type' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'status' => ['required', 'in:open,pending,closed'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $attachmentPath = null;
        $attachmentFileName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('employee_memos', 'public');
            $attachmentFileName = $file->getClientOriginalName();
        }

        EmployeeMemo::create([
            'employee_profile_id' => $employee->employeeProfile->id,
            'memo_type' => $validated['memo_type'],
            'subject' => $validated['subject'],
            'issue_date' => $validated['issue_date'],
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_file_name' => $attachmentFileName,
            'issued_by' => auth()->id(),
        ]);

        return redirect()
            ->route('hr.employees.show', ['employee' => $employee->id, 'tab' => 'memos'])
            ->with('success', 'Memo / disciplinary record added successfully.');
    }

    public function downloadMemoAttachment(EmployeeMemo $memo)
    {
        return $this->downloadFromPublicDisk(
            $memo->attachment_path,
            $memo->attachment_file_name
        );
    }

    public function deleteMemo(EmployeeMemo $memo): RedirectResponse
    {
        if ($memo->attachment_path) {
            Storage::disk('public')->delete($memo->attachment_path);
        }

        $employeeId = optional($memo->employeeProfile)->user_id;
        $memo->delete();

        return redirect()
            ->route('hr.employees.show', ['employee' => $employeeId, 'tab' => 'memos'])
            ->with('success', 'Memo / disciplinary record deleted successfully.');
    }

    public function storeExitRecord(Request $request, User $employee): RedirectResponse
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        $validated = $request->validate([
            'resignation_date' => ['nullable', 'date'],
            'last_working_day' => ['required', 'date'],
            'exit_type' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'clearance_status' => ['required', 'in:not_started,in_progress,cleared,hold'],
            'final_pay_status' => ['required', 'in:not_started,processing,released,hold'],
            'rehire_eligibility' => ['nullable', 'in:1,0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $attachmentPath = null;
        $attachmentFileName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('employee_exit_records', 'public');
            $attachmentFileName = $file->getClientOriginalName();
        }

        EmployeeExitRecord::create([
            'employee_profile_id' => $employee->employeeProfile->id,
            'resignation_date' => $validated['resignation_date'] ?? null,
            'last_working_day' => $validated['last_working_day'],
            'exit_type' => $validated['exit_type'],
            'reason' => $validated['reason'] ?? null,
            'clearance_status' => $validated['clearance_status'],
            'final_pay_status' => $validated['final_pay_status'],
            'rehire_eligibility' => array_key_exists('rehire_eligibility', $validated) && $validated['rehire_eligibility'] !== null
                ? (bool) $validated['rehire_eligibility']
                : null,
            'remarks' => $validated['remarks'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_file_name' => $attachmentFileName,
            'encoded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('hr.employees.show', ['employee' => $employee->id, 'tab' => 'exit'])
            ->with('success', 'Exit record added successfully.');
    }

    public function downloadExitRecordAttachment(EmployeeExitRecord $exitRecord)
    {
        return $this->downloadFromPublicDisk(
            $exitRecord->attachment_path,
            $exitRecord->attachment_file_name
        );
    }

    public function deleteExitRecord(EmployeeExitRecord $exitRecord): RedirectResponse
    {
        if ($exitRecord->attachment_path) {
            Storage::disk('public')->delete($exitRecord->attachment_path);
        }

        $employeeId = optional($exitRecord->employeeProfile)->user_id;
        $exitRecord->delete();

        return redirect()
            ->route('hr.employees.show', ['employee' => $employeeId, 'tab' => 'exit'])
            ->with('success', 'Exit record deleted successfully.');
    }

    public function storeMovement(Request $request, User $employee): RedirectResponse
    {
        $employee->load('employeeProfile');
        abort_unless($employee->employeeProfile, 404);

        $validated = $request->validate([
            'movement_type' => ['required', 'string', 'max:100'],
            'previous_value' => ['nullable', 'string', 'max:255'],
            'new_value' => ['nullable', 'string', 'max:255'],
            'effective_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        EmployeeMovement::create([
            'employee_profile_id' => $employee->employeeProfile->id,
            'movement_type' => $validated['movement_type'],
            'previous_value' => $validated['previous_value'] ?? null,
            'new_value' => $validated['new_value'] ?? null,
            'effective_date' => $validated['effective_date'],
            'remarks' => $validated['remarks'] ?? null,
            'encoded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('hr.employees.show', ['employee' => $employee->id, 'tab' => 'movement'])
            ->with('success', 'Movement record added successfully.');
    }

    public function deleteMovement(EmployeeMovement $movement): RedirectResponse
    {
        $employeeId = optional($movement->employeeProfile)->user_id;
        $movement->delete();

        return redirect()
            ->route('hr.employees.show', ['employee' => $employeeId, 'tab' => 'movement'])
            ->with('success', 'Movement record deleted successfully.');
    }

    public function print201File(User $employee)
    {
        $employee->load([
            'branch',
            'department',
            'employeeProfile.position',
            'employeeProfile.supervisor',
            'employeeProfile.documents.uploader',
            'employeeProfile.trainings',
        ]);

        abort_unless($employee->employeeProfile, 404);

        $profile = $employee->employeeProfile;

        $trainings = EmployeeTraining::query()
            ->where('employee_profile_id', $profile->id)
            ->latest('completed_at')
            ->latest('created_at')
            ->get();

        /*
         * Do not generate this page through DomPDF here.
         * The production server currently does not have barryvdh/laravel-dompdf
         * installed, which causes: Class "Barryvdh\DomPDF\Facade\Pdf" not found.
         *
         * The print-201 Blade file is already designed as a printable browser page
         * with a Print / Save as PDF button, so returning the view is more reliable
         * and removes the missing-package server error.
         */
        return view('hr.employees.print-201', [
            'employee' => $employee,
            'profile' => $profile,
            'trainings' => $trainings,
            'printMode' => 'html',
        ]);
    }

    protected function downloadFromPublicDisk(?string $path, ?string $fileName = null)
    {
        abort_unless(is_string($path) && trim($path) !== '', 404);

        $path = ltrim($path, '/\\');
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return response()->download(
            storage_path('app/public/' . $path),
            $fileName ?: basename($path)
        );
    }

    protected function formData(User $employee, EmployeeProfile $profile, bool $isEdit): array
    {
        return [
            'employee' => $employee,
            'profile' => $profile,
            'isEdit' => $isEdit,
            'branches' => Branch::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
            'provinces' => $this->provinceOptions(),
            'supervisors' => User::query()
                ->whereHas('employeeProfile')
                ->when($employee->exists, fn ($query) => $query->where('id', '!=', $employee->id))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'suffixes' => ['N/A', 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI'],
            'maritalStatuses' => ['Single', 'Married', 'Divorced', 'Separated', 'Widowed'],
            'sexes' => ['Male', 'Female'],
            'employmentTypes' => ['Regular', 'Probationary', 'Contractual', 'Project-based', 'Part-time', 'Intern'],
            'employmentStatuses' => ['Active', 'Inactive', 'Probationary', 'Resigned', 'Terminated'],
        ];
    }

    protected function validatedData(Request $request, ?int $employeeId = null, ?int $profileId = null): array
    {
        return $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($employeeId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employeeId)],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('employee_profiles', 'employee_id')->ignore($profileId)],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date'],
            'civil_status' => ['required', 'string', 'max:50'],
            'sex_of_birth' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'hire_date' => ['required', 'date'],
            'employee_rate' => ['required', 'numeric', 'min:0'],
            'sss_number' => ['nullable', 'string', 'max:50'],
            'pagibig_number' => ['nullable', 'string', 'max:50'],
            'philhealth_number' => ['nullable', 'string', 'max:50'],
            'tax_id_number' => ['nullable', 'string', 'max:50'],
            'employment_type' => ['required', 'string', 'max:50'],
            'employment_status' => ['required', 'string', 'max:50'],
        ]);
    }

    protected function profilePayload(array $validated, ?EmployeeProfile $existingProfile = null): array
    {
        return [
            'employee_id' => $validated['employee_id']
                ?? optional($existingProfile)->employee_id
                ?? $this->nextEmployeeCode(),
            'position_id' => $validated['position_id'],
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'employment_type' => $validated['employment_type'],
            'employment_status' => $validated['employment_status'],
            'hire_date' => $validated['hire_date'] ?? null,
            'salary' => $validated['employee_rate'] ?? null,
            'employee_rate' => $validated['employee_rate'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'civil_status' => $validated['civil_status'] ?? null,
            'gender' => $validated['sex_of_birth'] ?? null,
            'sex_of_birth' => $validated['sex_of_birth'] ?? null,
            'province' => $validated['province'] ?? null,
            'city' => $validated['city'] ?? null,
            'barangay' => $validated['barangay'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_number' => $validated['emergency_contact_number'] ?? null,
            'tax_id_number' => $validated['tax_id_number'] ?? null,
            'sss_number' => $validated['sss_number'] ?? null,
            'philhealth_number' => $validated['philhealth_number'] ?? null,
            'pagibig_number' => $validated['pagibig_number'] ?? null,
        ];
    }

    protected function provinceOptions()
    {
        if (Schema::hasTable('province')) {
            return DB::table('province')
                ->select('prid as id', 'prname as name')
                ->orderBy('prname')
                ->get();
        }

        return collect($this->fallbackProvinces())->map(fn ($province) => (object) $province);
    }

    protected function cityOptions(int $provinceId)
    {
        return collect($this->fallbackCities())
            ->where('province_id', $provinceId)
            ->sortBy('name')
            ->values()
            ->map(fn ($city) => (object) [
                'id' => $city['id'],
                'name' => $city['name'],
            ]);
    }

    protected function barangayOptions(int $cityId)
    {
        return collect($this->fallbackBarangays())
            ->where('city_id', $cityId)
            ->sortBy('name')
            ->values()
            ->map(fn ($barangay) => (object) [
                'id' => $barangay['id'],
                'name' => $barangay['name'],
            ]);
    }

    protected function fallbackProvinces(): array
    {
        return [
            ['id' => 1, 'name' => 'Misamis Oriental'],
            ['id' => 2, 'name' => 'Lanao del Norte'],
            ['id' => 3, 'name' => 'Bukidnon'],
            ['id' => 4, 'name' => 'Camiguin'],
            ['id' => 5, 'name' => 'Cebu'],
            ['id' => 6, 'name' => 'Davao del Sur'],
            ['id' => 7, 'name' => 'Metro Manila'],
            ['id' => 8, 'name' => 'Agusan del Norte'],
        ];
    }

    protected function fallbackCities(): array
    {
        return [
            ['id' => 101, 'province_id' => 1, 'name' => 'Cagayan de Oro City'],
            ['id' => 102, 'province_id' => 1, 'name' => 'Gingoog City'],
            ['id' => 103, 'province_id' => 1, 'name' => 'El Salvador City'],
            ['id' => 104, 'province_id' => 1, 'name' => 'Opol'],
            ['id' => 105, 'province_id' => 1, 'name' => 'Tagoloan'],
            ['id' => 106, 'province_id' => 1, 'name' => 'Villanueva'],

            ['id' => 201, 'province_id' => 2, 'name' => 'Iligan City'],
            ['id' => 202, 'province_id' => 2, 'name' => 'Tubod'],
            ['id' => 203, 'province_id' => 2, 'name' => 'Kauswagan'],

            ['id' => 301, 'province_id' => 3, 'name' => 'Malaybalay City'],
            ['id' => 302, 'province_id' => 3, 'name' => 'Valencia City'],
            ['id' => 303, 'province_id' => 3, 'name' => 'Manolo Fortich'],

            ['id' => 401, 'province_id' => 4, 'name' => 'Mambajao'],
            ['id' => 402, 'province_id' => 4, 'name' => 'Catarman'],

            ['id' => 501, 'province_id' => 5, 'name' => 'Cebu City'],
            ['id' => 502, 'province_id' => 5, 'name' => 'Mandaue City'],
            ['id' => 503, 'province_id' => 5, 'name' => 'Lapu-Lapu City'],

            ['id' => 601, 'province_id' => 6, 'name' => 'Davao City'],
            ['id' => 602, 'province_id' => 6, 'name' => 'Digos City'],

            ['id' => 701, 'province_id' => 7, 'name' => 'Manila'],
            ['id' => 702, 'province_id' => 7, 'name' => 'Quezon City'],
            ['id' => 703, 'province_id' => 7, 'name' => 'Makati City'],

            ['id' => 801, 'province_id' => 8, 'name' => 'Butuan City'],
            ['id' => 802, 'province_id' => 8, 'name' => 'Cabadbaran City'],
        ];
    }

    protected function fallbackBarangays(): array
    {
        return [
            ['id' => 10101, 'city_id' => 101, 'name' => 'Agusan'],
            ['id' => 10102, 'city_id' => 101, 'name' => 'Balulang'],
            ['id' => 10103, 'city_id' => 101, 'name' => 'Bugo'],
            ['id' => 10104, 'city_id' => 101, 'name' => 'Bulua'],
            ['id' => 10105, 'city_id' => 101, 'name' => 'Carmen'],
            ['id' => 10106, 'city_id' => 101, 'name' => 'Consolacion'],
            ['id' => 10107, 'city_id' => 101, 'name' => 'Gusa'],
            ['id' => 10108, 'city_id' => 101, 'name' => 'Iponan'],
            ['id' => 10109, 'city_id' => 101, 'name' => 'Kauswagan'],
            ['id' => 10110, 'city_id' => 101, 'name' => 'Lapasan'],
            ['id' => 10111, 'city_id' => 101, 'name' => 'Macabalan'],
            ['id' => 10112, 'city_id' => 101, 'name' => 'Nazareth'],
            ['id' => 10113, 'city_id' => 101, 'name' => 'Patag'],
            ['id' => 10114, 'city_id' => 101, 'name' => 'Puerto'],
            ['id' => 10115, 'city_id' => 101, 'name' => 'Puntod'],
            ['id' => 10116, 'city_id' => 101, 'name' => 'Tablon'],
            ['id' => 10117, 'city_id' => 101, 'name' => 'Upper Carmen'],

            ['id' => 10201, 'city_id' => 102, 'name' => 'Barangay 1'],
            ['id' => 10202, 'city_id' => 102, 'name' => 'Barangay 2'],
            ['id' => 10301, 'city_id' => 103, 'name' => 'Poblacion'],
            ['id' => 10401, 'city_id' => 104, 'name' => 'Poblacion'],
            ['id' => 10501, 'city_id' => 105, 'name' => 'Poblacion'],
            ['id' => 10601, 'city_id' => 106, 'name' => 'Poblacion'],

            ['id' => 20101, 'city_id' => 201, 'name' => 'Poblacion'],
            ['id' => 20102, 'city_id' => 201, 'name' => 'Tibanga'],
            ['id' => 20103, 'city_id' => 201, 'name' => 'Pala-o'],
            ['id' => 20104, 'city_id' => 201, 'name' => 'Hinaplanon'],
            ['id' => 20201, 'city_id' => 202, 'name' => 'Poblacion'],
            ['id' => 20301, 'city_id' => 203, 'name' => 'Poblacion'],

            ['id' => 30101, 'city_id' => 301, 'name' => 'Poblacion'],
            ['id' => 30201, 'city_id' => 302, 'name' => 'Poblacion'],
            ['id' => 30301, 'city_id' => 303, 'name' => 'Alae'],

            ['id' => 40101, 'city_id' => 401, 'name' => 'Poblacion'],
            ['id' => 40201, 'city_id' => 402, 'name' => 'Poblacion'],

            ['id' => 50101, 'city_id' => 501, 'name' => 'Lahug'],
            ['id' => 50102, 'city_id' => 501, 'name' => 'Guadalupe'],
            ['id' => 50201, 'city_id' => 502, 'name' => 'Centro'],
            ['id' => 50301, 'city_id' => 503, 'name' => 'Poblacion'],

            ['id' => 60101, 'city_id' => 601, 'name' => 'Poblacion'],
            ['id' => 60201, 'city_id' => 602, 'name' => 'Poblacion'],

            ['id' => 70101, 'city_id' => 701, 'name' => 'Barangay 1'],
            ['id' => 70201, 'city_id' => 702, 'name' => 'Commonwealth'],
            ['id' => 70301, 'city_id' => 703, 'name' => 'Poblacion'],

            ['id' => 80101, 'city_id' => 801, 'name' => 'Poblacion'],
            ['id' => 80201, 'city_id' => 802, 'name' => 'Poblacion'],
        ];
    }

    protected function syncEmployeeLeaveBalances(User $employee, EmployeeProfile $profile, int $year): void
    {
        $leaveTypes = LeaveType::query()
            ->where('status', 'active')
            ->where('is_paid', true)
            ->orderBy('name')
            ->get();

        foreach ($leaveTypes as $leaveType) {
            $allocated = (float) ($leaveType->default_credits ?? 0);

            $used = (float) LeaveRequest::query()
                ->where('user_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_datetime', $year)
                ->sum('days');

            LeaveBalance::updateOrCreate(
                [
                    'employee_profile_id' => $profile->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'allocated' => $allocated,
                    'used' => $used,
                    'remaining' => max($allocated - $used, 0),
                ]
            );
        }
    }

    protected function nextEmployeeCode(): string
    {
        $nextNumber = EmployeeProfile::query()->count() + 1;

        do {
            $employeeCode = $this->formatEmployeeCode($nextNumber);
            $exists = EmployeeProfile::where('employee_id', $employeeCode)->exists();
            $nextNumber++;
        } while ($exists);

        return $employeeCode;
    }

    protected function renumberEmployeeCodesByHireDate(): void
    {
        $profiles = EmployeeProfile::query()
            ->join('users', 'users.id', '=', 'employee_profiles.user_id')
            ->select('employee_profiles.id', 'employee_profiles.employee_id', 'employee_profiles.hire_date')
            ->orderByRaw('employee_profiles.hire_date ASC NULLS LAST')
            ->orderByRaw("LOWER(COALESCE(users.last_name, '')) ASC")
            ->orderByRaw("LOWER(COALESCE(users.first_name, '')) ASC")
            ->orderBy('employee_profiles.id')
            ->lockForUpdate()
            ->get();

        $timestamp = now()->format('YmdHis');

        foreach ($profiles as $profile) {
            $profile->forceFill([
                'employee_id' => 'TEMP-EMP-' . $profile->id . '-' . $timestamp,
            ])->save();
        }

        foreach ($profiles->values() as $index => $profile) {
            $profile->forceFill([
                'employee_id' => $this->formatEmployeeCode($index + 1),
            ])->save();
        }
    }

    protected function formatEmployeeCode(int $number): string
    {
        return 'EMP-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}