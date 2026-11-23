<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use Illuminate\Foundation\Http\FormRequest;

class BalanceSheetReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.balance-sheet');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'as_on'      => ['nullable', 'date'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the balance sheet.');
    }
}
