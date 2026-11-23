<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use Illuminate\Foundation\Http\FormRequest;

class ProfitLossReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.profit-loss');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the profit & loss report.');
    }
}
