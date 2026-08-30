<?php

namespace App\Http\Requests\Chevron\Customer;

use Illuminate\Foundation\Http\FormRequest;

class DestroyCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.customer.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete customers.');
    }
}
