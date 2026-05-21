<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $proofRequired = false;

        if ($this->filled('leave_type_id')) {
            $leaveType = LeaveType::find($this->input('leave_type_id'));

            if ($leaveType) {
                $proofRequired = $this->requiresProof($leaveType->name);
            }
        }

        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'proxy_user_id' => ['nullable', 'exists:users,id'],
            'from_date' => ['required', 'date'],
            'from_time' => ['required', 'in:morning,afternoon'],
            'to_date' => ['required', 'date'],
            'to_time' => ['required', 'in:morning,afternoon'],
            'reason' => ['required', 'string', 'max:2000'],

            'proof_file' => [
                $proofRequired ? 'required' : 'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from_time.required' => 'Please select the starting half-day schedule.',
            'from_time.in' => 'Starting schedule must be Morning or Afternoon.',
            'to_time.required' => 'Please select the ending half-day schedule.',
            'to_time.in' => 'Ending schedule must be Morning or Afternoon.',
            'proof_file.required' => 'Proof picture is required for this leave type.',
            'proof_file.image' => 'The proof must be an image file.',
            'proof_file.mimes' => 'The proof must be a JPG, JPEG, PNG, or WEBP image.',
            'proof_file.max' => 'The proof picture must not be greater than 5MB.',
        ];
    }

    private function requiresProof(?string $leaveTypeName): bool
    {
        $leaveTypeName = trim((string) $leaveTypeName);

        $leaveTypesWithoutProof = [
            'Service Incentive Leave',
        ];

        return !in_array($leaveTypeName, $leaveTypesWithoutProof, true);
    }
}