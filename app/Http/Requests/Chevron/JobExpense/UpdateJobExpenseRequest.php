<?php

namespace App\Http\Requests\Chevron\JobExpense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job-expense.edit');
    }

    public function rules(): array
    {
        return [
            'job_id'                 => ['required'],
            'employee_id'            => ['required'],
            'date'                   => ['required', 'date'],
            'rows'                   => ['required', 'array', 'min:1'],
            'rows.*.expense_head_id' => ['required'],
            'rows.*.expense_date'    => ['required', 'date'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit job expenses.');
    }
}
