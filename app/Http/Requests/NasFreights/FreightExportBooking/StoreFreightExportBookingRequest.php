<?php

namespace App\Http\Requests\NasFreights\FreightExportBooking;

use Illuminate\Foundation\Http\FormRequest;

class StoreFreightExportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.create');
    }

    public function rules(): array
    {
        return [
            'export_booking_no'     => ['nullable', 'string', 'max:255', 'unique:nas_freights_freight_export_bookings,export_booking_no'],
            'customer_id'           => ['required', 'exists:nas_freights_customers,id'],
            'booking_date'          => ['required', 'date'],
            'service_type'          => ['required'],
            'party_bill_ref_no'     => ['required', 'string', 'max:255'],
            'party_bill_date'       => ['required', 'date'],
            'party_invoices'        => ['nullable', 'array', 'max:50'],
            'party_invoices.*.no'   => ['nullable', 'string', 'max:255', 'required_with:party_invoices.*.date'],
            'party_invoices.*.date' => ['nullable', 'date'],
            'bl_date'               => ['nullable', 'date'],
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
            'party_bill_ref_no'     => 'Party Bill Ref No',
            'party_bill_date'       => 'Party Bill Ref Date',
            'party_invoices.*.no'   => 'Party Invoice No',
            'party_invoices.*.date' => 'Party Invoice Date',
            'invoice_no'            => 'S.B. No',
            'invoice_date'          => 'S.B. Date',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create freight export booking/jobs.');
    }
}
