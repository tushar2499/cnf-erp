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
            'bill_prefix_ref'   => ['nullable', 'string', 'max:100'],
            'bill_prefix_job'   => ['nullable', 'string', 'max:100'],
            'bill_date'         => ['required', 'date'],
            'currency'          => ['required', 'string', 'max:10'],
            'exchange_rate'     => ['required', 'numeric', 'min:0.0001'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.name'      => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'items.*.amount'    => ['required', 'numeric', 'min:0'],
            'vat_title'         => ['nullable', 'string', 'max:255'],
            'vat_amount'        => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'bill_prefix_ref.max'   => 'The Bill Ref prefix may not be longer than :max characters.',
            'bill_prefix_job.max'   => 'The Job No prefix may not be longer than :max characters.',
            'items.*.name.distinct' => 'The bill item description ":input" is used more than once. Each description can be used only once in a bill.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create export booking/job bills.');
    }
}
