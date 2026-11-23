<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use Illuminate\Foundation\Http\FormRequest;

class TrialBalanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.trial-balance');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
            'level'      => ['nullable', 'integer', 'between:1,4'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the trial balance.');
    }
}
