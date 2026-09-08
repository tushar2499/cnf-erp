<?php

namespace App\Http\Requests\NasFreights\CustomerBill;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer-bill.edit');
    }

    public function rules(): array
    {
        return [
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit customer bills.');
    }
}
