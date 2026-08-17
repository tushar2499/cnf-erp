<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.employees.create');
    }

    public function rules(): array
    {
        return [
            'employee_prefix' => ['required', 'string', 'max:20'],
            'name'            => ['required', 'string', 'max:255'],
            'designation_id'  => ['required', 'exists:chevron_designations,id'],
            'joining_date'    => ['required', 'date'],
            'type'            => ['required', 'in:team_leader,prepare'],
            'team_leader_id'  => ['nullable', 'required_if:type,prepare', 'exists:chevron_employees,id'],
            'customer_ids'    => ['nullable', 'array'],
            'customer_ids.*'  => ['exists:chevron_customers,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create employees.');
    }
}
