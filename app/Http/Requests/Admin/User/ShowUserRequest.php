<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class ShowUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.users.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view users.');
    }
}
