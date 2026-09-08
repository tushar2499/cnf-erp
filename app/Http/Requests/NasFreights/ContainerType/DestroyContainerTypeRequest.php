<?php

namespace App\Http\Requests\NasFreights\ContainerType;

use Illuminate\Foundation\Http\FormRequest;

class DestroyContainerTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.container-type.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete container types.');
    }
}
