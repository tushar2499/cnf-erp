<?php

namespace App\Http\Requests\NasFreights\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.vehicle.edit');
    }

    public function rules(): array
    {
        return [
            'vehicle_number' => ['required', 'string', 'max:50', 'unique:nas_freights_vehicles,vehicle_number,'.$this->route('vehicle')?->id],
            'vehicle_class'  => ['required', 'string'],
            'vehicle_type'   => ['required', 'string'],
            'purchase_unit'  => ['nullable', 'string'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit vehicles.');
    }
}
