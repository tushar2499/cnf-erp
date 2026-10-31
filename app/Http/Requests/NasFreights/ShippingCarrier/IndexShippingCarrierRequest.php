<?php

namespace App\Http\Requests\NasFreights\ShippingCarrier;

use Illuminate\Foundation\Http\FormRequest;

class IndexShippingCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.shipping-carrier.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view shipping carriers.');
    }
}
