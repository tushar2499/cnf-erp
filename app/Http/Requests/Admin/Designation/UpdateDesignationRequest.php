<?php

namespace App\Http\Requests\Admin\Designation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.designations.edit');
    }

    public function rules(): array
    {
        $designation = $this->route('designation');

        return [
            'name'      => ['required', 'string', 'max:255', Rule::unique('designations', 'name')->ignore($designation)],
            'is_active' => ['boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit designations.');
    }
}
