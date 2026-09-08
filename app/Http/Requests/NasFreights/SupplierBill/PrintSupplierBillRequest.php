<?php

namespace App\Http\Requests\NasFreights\SupplierBill;

use Illuminate\Foundation\Http\FormRequest;

class PrintSupplierBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-bill.print');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to print supplier bills.');
    }
}
