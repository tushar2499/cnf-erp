<?php

namespace App\Http\Requests\NasFreights\MoneyReceipt;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoneyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.money-receipt.create');
    }

    public function rules(): array
    {
        return [
            'receipt_date'    => ['required', 'date'],
            'bill_id'         => ['required', 'exists:nas_freights_customer_bills,id'],
            'amount_received' => ['required', 'numeric', 'min:0.01'],
            'payment_mode'    => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create money receipts.');
    }
}
