<?php

namespace App\Http\Requests\Chevron\MoneyReceipt;

use Illuminate\Foundation\Http\FormRequest;

class IndexMoneyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.money-receipt.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view money receipts.');
    }
}
