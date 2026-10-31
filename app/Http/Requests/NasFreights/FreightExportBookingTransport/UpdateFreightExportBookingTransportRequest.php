<?php

namespace App\Http\Requests\NasFreights\FreightExportBookingTransport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreightExportBookingTransportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.transport-manage');
    }

    public function rules(): array
    {
        return [
            'items'   => ['nullable', 'array'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage transport for freight export booking/jobs.');
    }
}
