<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use Illuminate\Foundation\Http\FormRequest;

class LedgerReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.ledger');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'account_id' => ['nullable', 'integer', 'exists:acc_accounts,id'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
            'party_type' => ['nullable', 'string', 'max:20'],
            'party_id'   => ['nullable', 'integer'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the ledger report.');
    }
}
