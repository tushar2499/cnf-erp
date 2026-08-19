<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.users.create');
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'username'          => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'             => ['nullable', 'email', 'unique:users,email'],
            'password'          => ['required', Password::min(6)],
            'is_active'         => ['boolean'],
            'role_id'           => ['nullable', 'exists:roles,id'],
            'employee_id'       => ['required', 'integer', 'exists:employees,id'],
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
        abort(403, 'You do not have permission to create users.');
    }
}
