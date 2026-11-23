<?php

namespace App\Http\Requests\Admin\Accounting\Account;

use Illuminate\Foundation\Http\FormRequest;

class DestroyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.coa.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete accounts.');
    }
}
