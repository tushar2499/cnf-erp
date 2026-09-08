<?php

namespace App\Http\Requests\NasFreights\PackageType;

use Illuminate\Foundation\Http\FormRequest;

class DestroyPackageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.package-type.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete package types.');
    }
}
