<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.roles.create');
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permission_ids'   => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create roles.');
    }
}
