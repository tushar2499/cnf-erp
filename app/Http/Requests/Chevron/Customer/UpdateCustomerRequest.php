<?php

namespace App\Http\Requests\Chevron\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.customer.edit');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'portal_password'         => ['nullable', 'string', 'min:6'],
            'portal_password_confirm' => ['nullable', 'same:portal_password'],
            'branch_id'               => ['nullable', 'exists:chevron_branches,id'],
            'status'                  => ['nullable', 'string'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit customers.');
    }
}
