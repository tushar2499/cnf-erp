<?php

namespace App\Http\Requests\Admin\Accounting\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class ShowVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.voucher.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view vouchers.');
    }
}
