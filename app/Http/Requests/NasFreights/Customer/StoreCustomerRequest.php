<?php

namespace App\Http\Requests\NasFreights\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer.create');
    }

    public function rules(): array
    {
        return [
            'id_prefix' => ['required', 'string', 'max:20'],
            'name'      => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create customers.');
    }
}
