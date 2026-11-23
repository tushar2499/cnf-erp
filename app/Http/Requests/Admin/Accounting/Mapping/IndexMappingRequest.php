<?php

namespace App\Http\Requests\Admin\Accounting\Mapping;

use Illuminate\Foundation\Http\FormRequest;

class IndexMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.mapping.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view transaction mappings.');
    }
}
