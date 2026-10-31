<?php

namespace App\Http\Requests\NasFreights\SupplierPayment;

use Illuminate\Foundation\Http\FormRequest;

class IndexSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-payment.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view supplier payments.');
    }
}
