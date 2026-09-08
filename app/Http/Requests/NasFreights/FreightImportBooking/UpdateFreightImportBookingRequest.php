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
            'booking_date' => ['required', 'date'],
            'service_type' => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit freight import bookings.');
    }
}
