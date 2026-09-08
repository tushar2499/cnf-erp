<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmSupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.confirm');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to confirm supplier bills.');
    }
}
