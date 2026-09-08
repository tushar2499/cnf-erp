<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class DestroySupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete supplier bills.');
    }
}
