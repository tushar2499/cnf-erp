<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.roles.edit');
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name'             => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId)],
            'permission_ids'   => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit roles.');
    }
}
