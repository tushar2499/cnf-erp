<?php

namespace App\Http\Requests\NasFreights\CustomerBill;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmCustomerBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer-bill.confirm');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to confirm customer bills.');
    }
}
