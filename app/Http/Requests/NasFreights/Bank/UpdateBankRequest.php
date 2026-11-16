<?php

namespace App\Http\Requests\NasFreights\Bank;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.bank.edit');
    }

    public function rules(): array
    {
        return [
            'account_name'   => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255', 'unique:nas_freights_banks,account_number,'.$this->route('bank')?->id],
            'account_type'   => ['required', 'string', 'max:50'],
            'bank_name'      => ['required', 'string', 'max:255'],
            'bank_branch'    => ['nullable', 'string', 'max:255'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_name.required'   => 'Account name is required.',
            'account_number.required' => 'Account number is required.',
            'account_number.unique'   => 'This account number already exists.',
            'account_type.required'   => 'Account type is required.',
            'bank_name.required'      => 'Bank name is required.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit banks.');
    }
}
