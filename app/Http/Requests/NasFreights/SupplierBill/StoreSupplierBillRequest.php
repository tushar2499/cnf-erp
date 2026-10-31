<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.create');
    }

    public function rules(): array
    {
        return [
            'from_date'          => ['required', 'date'],
            'to_date'            => ['required', 'date'],
            'bill_date'          => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'invoice_no'         => ['nullable', 'string', 'max:100'],
            'payment_date'       => ['nullable', 'date'],
            'money_receipt_no'   => ['nullable', 'string', 'max:100'],
            'money_receipt_date' => ['nullable', 'date'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create supplier bills.');
    }
}
