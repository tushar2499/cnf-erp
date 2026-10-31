<?php

namespace App\Http\Requests\NasFreights\FreightExportBookingBill;

use Illuminate\Foundation\Http\FormRequest;

class StoreFreightExportBookingBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking-bill.create');
    }

    public function rules(): array
    {
        return [
            'export_booking_id' => ['required', 'exists:nas_freights_freight_export_bookings,id'],
            'bill_type'         => ['required', 'in:Customer,Overseas Agent'],
            'bill_date'         => ['required', 'date'],
            'currency'          => ['required', 'string', 'max:10'],
            'exchange_rate'     => ['required', 'numeric', 'min:0.0001'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.name'      => ['required', 'string', 'max:255'],
            'items.*.amount'    => ['required', 'numeric', 'min:0'],
            'vat_title'         => ['nullable', 'string', 'max:255'],
            'vat_amount'        => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create export booking/job bills.');
    }
}
