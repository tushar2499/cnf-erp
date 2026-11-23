<?php

namespace App\Http\Requests\Admin\Accounting\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.coa.edit');
    }

    /**
     * Code, parent and type are fixed once created; only descriptive fields change.
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'nature'       => ['required', Rule::in(['dr', 'cr'])],
            'is_cash_bank' => ['boolean'],
            'party_type'   => ['nullable', Rule::in(['customer', 'supplier'])],
            'is_active'    => ['boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit accounts.');
    }
}
