<?php

namespace App\Http\Requests\Chevron\MoneyReceipt;

class UpdateMoneyReceiptRequest extends StoreMoneyReceiptRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.money-receipt.edit');
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit money receipts.');
    }
}
