<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use Illuminate\Foundation\Http\FormRequest;

class CashBookReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.cash-book');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
            'account_id' => ['nullable', 'integer', 'exists:acc_accounts,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the cash / bank book.');
    }
}
