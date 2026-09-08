<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.edit');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date'],
            'bill_date' => ['required', 'date'],
            'items'     => ['required', 'array', 'min:1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit supplier bills.');
    }
}
