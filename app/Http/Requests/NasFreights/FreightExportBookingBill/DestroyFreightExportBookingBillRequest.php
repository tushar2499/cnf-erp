<?php

namespace App\Http\Requests\NasFreights\FreightExportBookingBill;

use Illuminate\Foundation\Http\FormRequest;

class DestroyFreightExportBookingBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking-bill.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete export booking bills.');
    }
}
