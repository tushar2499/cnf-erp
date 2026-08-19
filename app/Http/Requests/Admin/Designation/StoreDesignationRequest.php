<?php

namespace App\Http\Requests\Admin\Designation;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.designations.create');
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255', 'unique:designations,name'],
            'is_active' => ['boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create designations.');
    }
}
