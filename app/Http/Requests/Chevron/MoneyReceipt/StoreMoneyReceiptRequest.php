<?php

namespace App\Http\Requests\Chevron\MoneyReceipt;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoneyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.money-receipt.create');
    }

    public function rules(): array
    {
        return [
            'receipt_date'         => ['required', 'date'],
            'party_name'           => ['required', 'string', 'max:255'],
            'pay_type'             => ['required', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.payment_type' => ['required', 'string'],
            'items.*.amount'       => ['required', 'numeric', 'min:0.01'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create money receipts.');
    }
}
