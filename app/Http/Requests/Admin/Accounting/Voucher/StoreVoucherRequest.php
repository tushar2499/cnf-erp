<?php

namespace App\Http\Requests\Admin\Accounting\Voucher;

use App\Models\Accounting\AccVoucher;
use App\Services\Accounting\PartyDirectory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.voucher.create');
    }

    public function rules(): array
    {
        return [
            'company_id'         => ['required', 'integer', 'exists:companies,id'],
            'voucher_type'       => ['required', Rule::in(array_keys(AccVoucher::TYPES))],
            'voucher_date'       => ['required', 'date'],
            'narration'          => ['nullable', 'string', 'max:1000'],
            'lines'              => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['nullable', 'integer'],
            'lines.*.debit'      => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit'     => ['nullable', 'numeric', 'min:0'],
            'lines.*.party_type' => ['nullable', Rule::in(array_keys(PartyDirectory::TYPES))],
            'lines.*.party_id'   => ['nullable', 'integer'],
            'lines.*.party_name' => ['nullable', 'string', 'max:255'],
            'lines.*.narration'  => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create vouchers.');
    }
}
