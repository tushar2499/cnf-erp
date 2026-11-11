<?php

namespace App\Http\Requests\NasFreights\FreightExportBooking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFreightExportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.edit');
    }

    public function rules(): array
    {
        return [
            'customer_id'           => ['required', 'exists:nas_freights_customers,id'],
            'booking_date'          => ['required', 'date'],
            'service_type'          => ['required'],
            'currency'              => ['nullable', 'string', 'max:10'],
            'exchange_rate'         => [
                Rule::requiredIf(fn () => ($this->input('currency') ?: 'BDT') !== 'BDT'),
                'nullable', 'numeric', 'decimal:0,3', 'min:0.001', 'max:9999999999.999',
            ],
            'export_booking_no'     => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('nas_freights_freight_export_bookings', 'export_booking_no')
                    ->ignore($this->route('exportBooking')),
            ],
            'party_bill_ref_no'     => ['nullable', 'string', 'max:255'],
            'party_bill_date'       => ['nullable', 'date'],
            'party_invoices'        => ['nullable', 'array', 'max:50'],
            'party_invoices.*.no'   => ['nullable', 'string', 'max:255', 'required_with:party_invoices.*.date'],
            'party_invoices.*.date' => ['nullable', 'date'],
            'bl_date'               => ['nullable', 'date'],
            'hawb_hbl_no'           => ['nullable', 'string', 'max:255'],
            'hawb_hbl_date'         => ['nullable', 'date'],
            'exp_date'              => ['nullable', 'date'],
            'invoice_date'          => ['nullable', 'date'],
            'exp_no'                => ['nullable', 'string', 'max:255'],
            'invoice_no'            => ['nullable', 'string', 'max:255'],
            'lc_no'                 => ['nullable', 'string', 'max:255'],
            'hs_codes'              => ['nullable', 'array', 'max:50'],
            'hs_codes.*'            => ['nullable', 'string', 'max:50'],
            'items.*.package_qty'   => ['nullable', 'integer', 'min:1', 'max:9999999'],
            'items.*.package_unit'  => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'export_booking_no'     => 'Booking/Job No',
            'exchange_rate'         => 'Exchange Rate',
            'party_bill_ref_no'     => 'Party Bill Ref No',
            'party_bill_date'       => 'Party Bill Ref Date',
            'party_invoices.*.no'   => 'Party Invoice No',
            'party_invoices.*.date' => 'Party Invoice Date',
            'bl_date'               => 'Export B/L Date',
            'hawb_hbl_no'           => 'HAWB/HBL No',
            'hawb_hbl_date'         => 'HAWB/HBL Date',
            'invoice_no'            => 'S.B. No',
            'invoice_date'          => 'S.B. Date',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit freight export booking/jobs.');
    }
}
