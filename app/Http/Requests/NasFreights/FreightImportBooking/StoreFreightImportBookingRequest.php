<?php

namespace App\Http\Requests\NasFreights\FreightImportBooking;

use Illuminate\Foundation\Http\FormRequest;

class StoreFreightImportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.import-booking.create');
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
        abort(403, 'You do not have permission to create freight import bookings.');
    }
}
