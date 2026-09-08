<?php

namespace App\Http\Requests\NasFreights\FreightExportBooking;

use Illuminate\Foundation\Http\FormRequest;

class CreateFreightExportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.create');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create freight export bookings.');
    }
}
