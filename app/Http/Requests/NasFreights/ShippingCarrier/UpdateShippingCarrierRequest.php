<?php

namespace App\Http\Requests\NasFreights\ShippingCarrier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.shipping-carrier.edit');
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'scac_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit shipping carriers.');
    }
}
