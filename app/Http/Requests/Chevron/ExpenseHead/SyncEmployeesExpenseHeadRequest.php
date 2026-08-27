<?php

namespace App\Http\Requests\Chevron\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class SyncEmployeesExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-head.edit');
    }

    public function rules(): array
    {
        return [
            'employee_ids'   => ['array'],
            'employee_ids.*' => ['exists:chevron_employees,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage expense heads.');
    }
}
