<?php

namespace App\Http\Requests\NasFreights\PackageType;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.package-type.create');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100', 'unique:nas_freights_package_types,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create package types.');
    }
}
