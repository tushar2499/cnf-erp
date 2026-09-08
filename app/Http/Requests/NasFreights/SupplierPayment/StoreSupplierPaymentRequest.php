<?php

namespace App\Http\Requests\NasFreights\SupplierPayment;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier-payment.create');
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'bill_id'      => ['required', 'exists:nas_freights_supplier_bills,id'],
            'amount_paid'  => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create supplier payments.');
    }
}
