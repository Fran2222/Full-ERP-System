<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('employee');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            
            // New added validation rule for position_id to ensure it belongs to the selected department
            'position_id' => ['required', Rule::exists('positions', 'id')->where(fn ($query) => $query->where('department_id', $this->department_id)),],
            // End part of a new added validation rule for position_id to ensure it belongs to the selected department
            
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'employment_type' => ['required', Rule::in(['regular', 'probationary', 'contractual', 'project-based', 'part-time', 'intern'])],
            'employment_status' => ['required', Rule::in(['active', 'inactive', 'probationary', 'resigned', 'terminated'])],
            'hire_date' => ['required', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'tax_id_number' => ['nullable', 'string', 'max:100'],
            'sss_number' => ['nullable', 'string', 'max:100'],
            'philhealth_number' => ['nullable', 'string', 'max:100'],
            'pagibig_number' => ['nullable', 'string', 'max:100'],
            'vacation_leave_credits' => ['nullable', 'numeric', 'min:0'],
            'sick_leave_credits' => ['nullable', 'numeric', 'min:0'],
            'emergency_leave_credits' => ['nullable', 'numeric', 'min:0'],
            'maternity_leave_credits' => ['nullable', 'numeric', 'min:0'],
            'paternity_leave_credits' => ['nullable', 'numeric', 'min:0'],
            'schedule_days' => ['nullable', 'array'],
            'schedule_days.*' => ['string'],
            'schedule_time_in' => ['nullable', 'date_format:H:i'],
            'schedule_time_out' => ['nullable', 'date_format:H:i', 'after:schedule_time_in'],
            'notes' => ['nullable', 'string'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'contract' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'government_id' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'tax_payroll_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
