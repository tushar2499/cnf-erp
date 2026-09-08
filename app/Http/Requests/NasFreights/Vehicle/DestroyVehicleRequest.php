<?php

namespace App\Http\Requests\NasFreights\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class DestroyVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.vehicle.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete vehicles.');
    }
}
