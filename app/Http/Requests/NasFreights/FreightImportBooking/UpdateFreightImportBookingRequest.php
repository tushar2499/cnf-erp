<?php

namespace App\Http\Requests\NasFreights\FreightImportBooking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreightImportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.import-booking.edit');
    }

    public function rules(): array
    {
        return [
            'customer_id'                => ['required', 'exists:nas_freights_customers,id'],
            'customer_invoice_no'        => ['nullable', 'string', 'max:255'],
            'customer_invoice_date'      => ['nullable', 'date'],
            'agent_invoice_no'           => ['nullable', 'string', 'max:255'],
            'flight_no'                  => ['nullable', 'string', 'max:255'],
            'flight_date'                => ['nullable', 'date'],
            'mbl_mawb_no'                => ['nullable', 'string', 'max:255'],
            'mbl_mawb_date'              => ['nullable', 'date'],
            'hbl_hawb_no'                => ['nullable', 'string', 'max:255'],
            'hbl_hawb_date'              => ['nullable', 'date'],
            'lc_no'                      => ['nullable', 'string', 'max:255'],
            'cad_no'                     => ['nullable', 'string', 'max:255'],
            'tt_no'                      => ['nullable', 'string', 'max:255'],
            'rfq_tender_no'              => ['nullable', 'string', 'max:255'],
            'booking_date'               => ['required', 'date'],
            'service_type'               => ['required'],
            'hs_codes'                   => ['nullable', 'array', 'max:50'],
            'hs_codes.*'                 => ['nullable', 'string', 'max:50'],
            'items.*.net_weight'         => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'items.*.gross_weight'       => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'items.*.chargeable_weight'  => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'items.*.package_qty'        => ['nullable', 'integer', 'min:1', 'max:9999999'],
            'items.*.package_unit'       => ['nullable', 'string', 'max:100'],
            'exchange_rate'              => ['nullable', 'numeric', 'min:0', 'max:9999999.999999'],
            'buy_amount'                 => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
            'sell_amount'                => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit freight import bookings.');
    }
}
