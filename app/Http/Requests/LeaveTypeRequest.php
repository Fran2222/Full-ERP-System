<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'default_credits' => ['required', 'numeric', 'min:0'],
        'is_paid' => ['required', 'boolean'],
        'status' => ['required', 'in:active,inactive'],
        ];
    }
}
