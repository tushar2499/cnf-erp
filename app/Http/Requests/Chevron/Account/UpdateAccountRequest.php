<?php

namespace App\Http\Requests\Chevron\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.account.edit');
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'account_no'   => ['required', 'string', 'max:100', 'unique:chevron_accounts,account_no,'.$accountId],
            'account_name' => ['required', 'string', 'max:255'],
            'bank_name'    => ['nullable', 'string', 'max:255'],
            'branch_name'  => ['nullable', 'string', 'max:255'],
            'account_type' => ['required', 'string'],
            'is_active'    => ['sometimes', 'boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit accounts.');
    }
}
