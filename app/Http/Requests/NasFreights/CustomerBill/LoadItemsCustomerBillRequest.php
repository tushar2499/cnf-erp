<?php

namespace App\Http\Requests\NasFreights\CustomerBill;

use Illuminate\Foundation\Http\FormRequest;

class LoadItemsCustomerBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer-bill.create');
    }

    public function rules(): array
    {
        return [
            'from_date'   => ['required', 'date'],
            'to_date'     => ['required', 'date'],
            'customer_id' => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create customer bills.');
    }
}
