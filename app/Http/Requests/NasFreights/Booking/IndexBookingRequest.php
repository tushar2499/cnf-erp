<?php

namespace App\Http\Requests\NasFreights\Booking;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.booking.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view bookings.');
    }
}
