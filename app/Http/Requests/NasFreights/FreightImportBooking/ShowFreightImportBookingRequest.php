<?php

namespace App\Http\Requests\NasFreights\FreightImportBooking;

use Illuminate\Foundation\Http\FormRequest;

class ShowFreightImportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.import-booking.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view freight import bookings.');
    }
}
