<?php

namespace App\Http\Requests\NasFreights\CustomerBill;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer-bill.create');
    }

    public function rules(): array
    {
        return [
            'from_date'          => ['required', 'date'],
            'to_date'            => ['required', 'date'],
            'bill_date'          => ['required', 'date'],
            'delivery_type'      => ['required'],
            'bill_type'          => ['required'],
            'invoice_no'         => ['nullable', 'string', 'max:100'],
            'payment_date'       => ['nullable', 'date'],
            'money_receipt_no'   => ['nullable', 'string', 'max:100'],
            'money_receipt_date' => ['nullable', 'date'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create customer bills.');
    }
}
