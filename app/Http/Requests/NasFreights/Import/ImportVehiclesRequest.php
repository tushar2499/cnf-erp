<?php

namespace App\Http\Requests\NasFreights\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportVehiclesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.import.vehicles');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import vehicles.');
    }
}
