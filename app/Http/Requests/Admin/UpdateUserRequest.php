<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'              => ['required', 'string', 'max:255'],
            'username'          => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId), 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'             => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'          => ['nullable', Password::min(6)],
            'is_active'         => ['boolean'],
            'role_id'           => ['nullable', 'exists:roles,id'],
            'employee_link'     => ['nullable', 'string', 'regex:/^\d+:\d+$/'],
            'branch_access'     => ['nullable', 'array'],
            'branch_access.*'   => ['nullable', 'array'],
            'branch_access.*.*' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit users.');
    }
}
