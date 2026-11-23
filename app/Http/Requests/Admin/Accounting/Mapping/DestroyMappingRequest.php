<?php

namespace App\Http\Requests\Admin\Accounting\Mapping;

use Illuminate\Foundation\Http\FormRequest;

class DestroyMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.mapping.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete transaction mappings.');
    }
}
