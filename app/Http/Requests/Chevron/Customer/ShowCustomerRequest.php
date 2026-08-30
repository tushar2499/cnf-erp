<?php

namespace App\Http\Requests\Chevron\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ShowCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.customer.edit');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view customers.');
    }
}
