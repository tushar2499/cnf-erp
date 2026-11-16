<?php

namespace App\Http\Requests\Admin\Accounting\Report;

use App\Models\Accounting\AccVoucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DayBookReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.report.day-book');
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
            'type'       => ['nullable', Rule::in(array_keys(AccVoucher::TYPES))],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the day book.');
    }
}
