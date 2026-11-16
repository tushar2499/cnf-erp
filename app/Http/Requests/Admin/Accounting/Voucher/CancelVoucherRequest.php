<?php

namespace App\Http\Requests\Admin\Accounting\Voucher;

use Illuminate\Foundation\Http\FormRequest;

class CancelVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.voucher.cancel');
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to cancel vouchers.');
    }
}
