<?php

namespace App\Http\Requests\NasFreights\ContainerType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContainerTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.container-type.edit');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:50', 'unique:nas_freights_container_types,name,'.$this->route('containerType')?->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit container types.');
    }
}
