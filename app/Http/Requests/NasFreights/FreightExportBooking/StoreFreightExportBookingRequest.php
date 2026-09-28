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
            'customer_id'  => ['required', 'exists:nas_freights_customers,id'],
            'booking_date' => ['required', 'date'],
            'service_type' => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create freight export bookings.');
    }
}
