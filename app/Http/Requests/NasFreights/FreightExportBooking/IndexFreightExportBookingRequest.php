<?php

namespace App\Http\Requests\NasFreights\FreightExportBooking;

use Illuminate\Foundation\Http\FormRequest;

class IndexFreightExportBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view freight export bookings.');
    }
}
