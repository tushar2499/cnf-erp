<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class CreateSupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.create');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create supplier bills.');
    }
}
