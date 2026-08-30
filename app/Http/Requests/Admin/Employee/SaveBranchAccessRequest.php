<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class SaveBranchAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.employees.branch-access');
    }

    public function rules(): array
    {
        return [
            'access'                => ['nullable', 'array'],
            'access.*.company_id'   => ['required', 'exists:companies,id'],
            'access.*.branch_ids'   => ['nullable', 'array'],
            'access.*.branch_ids.*' => ['integer'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage employee branch access.');
    }
}
