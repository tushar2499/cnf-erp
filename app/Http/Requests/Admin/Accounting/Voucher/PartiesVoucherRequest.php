<?php

namespace App\Http\Requests\Admin\Accounting\Voucher;

use App\Services\Accounting\PartyDirectory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartiesVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.voucher.create');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'type'       => ['required', Rule::in(array_keys(PartyDirectory::TYPES))],
            'q'          => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create vouchers.');
    }
}
