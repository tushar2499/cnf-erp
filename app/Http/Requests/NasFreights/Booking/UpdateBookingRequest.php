<?php

namespace App\Http\Requests\NasFreights\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.booking.edit');
    }

    public function rules(): array
    {
        return [
            'booking_prefix'        => ['required'],
            'sales_type'            => ['required'],
            'job_date'              => ['required', 'date'],
            'customer_id'           => ['required'],
            'delivery_date'         => ['required', 'date'],
            'cover_van_no'          => ['required', 'string'],
            'items'                 => ['required', 'array', 'min:1'],
            'products'              => ['nullable', 'array'],
            'products.*.goods_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit bookings.');
    }
}
