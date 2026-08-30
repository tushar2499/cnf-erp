<?php

namespace App\Http\Requests\Chevron\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.customer.create');
    }

    public function rules(): array
    {
        return [
            'id_prefix'               => ['required', 'string', 'max:20'],
            'name'                    => ['required', 'string', 'max:255'],
            'portal_password'         => ['nullable', 'string', 'min:6'],
            'portal_password_confirm' => ['nullable', 'same:portal_password'],
            'branch_id'               => ['nullable', 'exists:chevron_branches,id'],
            'status'                  => ['nullable', 'string'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create customers.');
    }
}
