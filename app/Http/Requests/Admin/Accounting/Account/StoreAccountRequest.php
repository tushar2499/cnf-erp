<?php

namespace App\Http\Requests\Admin\Accounting\Account;

use App\Models\Accounting\AccAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.coa.create');
    }

    public function rules(): array
    {
        return [
            'parent_id'    => ['nullable', 'integer', Rule::exists('acc_accounts', 'id')->where('is_group', 1)],
            'code'         => ['required', 'string', 'regex:/^\d+$/', 'unique:acc_accounts,code'],
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required_without:parent_id', 'nullable', Rule::in(AccAccount::TYPES)],
            'nature'       => ['required', Rule::in(['dr', 'cr'])],
            'is_cash_bank' => ['boolean'],
            'party_type'   => ['nullable', Rule::in(['customer', 'supplier'])],
            'is_active'    => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $parent = $this->parentAccount();
                $level = $parent ? $parent->level + 1 : AccAccount::LEVEL_MASTER;

                if ($level > AccAccount::LEVEL_LEDGER) {
                    $validator->errors()->add('parent_id', 'A ledger cannot have child accounts.');

                    return;
                }

                $expected = AccAccount::CODE_LENGTHS[$level];
                if (strlen($this->code) !== $expected) {
                    $validator->errors()->add('code', AccAccount::LEVEL_NAMES[$level]." code must be {$expected} digits.");
                }
                if ($parent && ! str_starts_with($this->code, $parent->code)) {
                    $validator->errors()->add('code', "Code must start with parent code {$parent->code}.");
                }
            },
        ];
    }

    public function parentAccount(): ?AccAccount
    {
        return $this->parent_id ? AccAccount::find($this->parent_id) : null;
    }

    public function messages(): array
    {
        return [
            'parent_id.exists'      => 'Parent must be a group account.',
            'code.regex'            => 'Code must contain digits only.',
            'type.required_without' => 'Type is required for a master account.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create accounts.');
    }
}
