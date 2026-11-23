<?php

namespace App\Http\Requests\Admin\Accounting\Mapping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.mapping.create');
    }

    public function rules(): array
    {
        return [
            'source_type'       => ['required', 'string', 'max:60'],
            'component'         => ['required', 'string', 'max:60'],
            'match_key'         => ['nullable', 'string', 'max:60'],
            'debit_account_id'  => ['required', 'integer', Rule::exists('acc_accounts', 'id')->where('is_group', 0)],
            'credit_account_id' => ['required', 'integer', 'different:debit_account_id', Rule::exists('acc_accounts', 'id')->where('is_group', 0)],
            'is_active'         => ['boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create transaction mappings.');
    }
}
