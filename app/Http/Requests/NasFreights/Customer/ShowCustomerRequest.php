<?php

namespace App\Http\Requests\NasFreights\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ShowCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer.view');
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
