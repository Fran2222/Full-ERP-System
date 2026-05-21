<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    protected array $allowedModules = [
        'hr',
        'inventory',
        'warehouse',
        'purchasing',
        'sales',
        'accounting',
        'payroll',
        'reports',
        'project_management',
    ];

    protected array $allowedAccessLevels = [
        'viewer',
        'staff',
        'manager',
        'admin',
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $method = strtolower($this->method());
        $user_id = $this->route()->user;

        $baseRules = [
            'phone_number' => 'nullable|max:13',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'user_role' => 'required',
            'userProfile.street_addr_1' => 'required|max:500',
            'userProfile.company_name' => 'required|in:Wizmaster Corporation',
            'userProfile.country' => 'required|in:Philippines',
            'userProfile.alt_phone_number' => 'nullable|max:191',
            'userProfile.pin_code' => 'nullable|max:191',

            'module_assignments' => 'required|array',
            'primary_module_assignment' => 'required|string|in:' . implode(',', $this->allowedModules),
            'module_assignments.*.enabled' => 'nullable',
            'module_assignments.*.access_level' => 'nullable|string|in:' . implode(',', $this->allowedAccessLevels),
        ];

        switch ($method) {
            case 'post':
                return array_merge($baseRules, [
                    'username' => 'required|max:20',
                    'password' => 'required|confirmed|min:8',
                    'email' => 'required|max:191|email|unique:users,email',
                ]);

            case 'patch':
                return array_merge($baseRules, [
                    'username' => 'required|max:20',
                    'password' => 'nullable|confirmed|min:8',
                    'email' => 'required|max:191|email|unique:users,email,' . $user_id,
                ]);
        }

        return $baseRules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assignments = $this->input('module_assignments', []);
            $primaryModule = $this->input('primary_module_assignment');

            $enabledModules = [];

            foreach ($assignments as $module => $row) {
                if (!in_array($module, $this->allowedModules, true)) {
                    continue;
                }

                $enabled = !empty($row['enabled']);
                $accessLevel = $row['access_level'] ?? null;

                if ($enabled) {
                    $enabledModules[] = $module;

                    if (!$accessLevel || !in_array($accessLevel, $this->allowedAccessLevels, true)) {
                        $validator->errors()->add(
                            "module_assignments.$module.access_level",
                            ucfirst($module) . ' access level is required.'
                        );
                    }
                }
            }

            if (empty($enabledModules)) {
                $validator->errors()->add('module_assignments', 'Please enable at least one module assignment.');
            }

            if ($primaryModule && !in_array($primaryModule, $enabledModules, true)) {
                $validator->errors()->add(
                    'primary_module_assignment',
                    'Primary module must be one of the enabled module assignments.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'userProfile.street_addr_1.required' => 'Full Address is required.',
            'userProfile.street_addr_1.max' => 'Full Address may not be greater than 500 characters.',
            'userProfile.company_name.required' => 'Company Name is required.',
            'userProfile.company_name.in' => 'Company Name must be Wizmaster Corporation.',
            'userProfile.country.required' => 'Country is required.',
            'userProfile.country.in' => 'Country must be Philippines.',
            'branch_id.required' => 'Branch is required.',
            'department_id.required' => 'Department is required.',
            'user_role.required' => 'User Role is required.',
            'primary_module_assignment.required' => 'Primary module is required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => true,
            'message' => $validator->errors()->first(),
            'all_message' => $validator->errors()
        ];

        if ($this->ajax()) {
            throw new HttpResponseException(response()->json($data, 422));
        } else {
            throw new HttpResponseException(
                redirect()->back()->withInput()->with('errors', $validator->errors())
            );
        }
    }
}