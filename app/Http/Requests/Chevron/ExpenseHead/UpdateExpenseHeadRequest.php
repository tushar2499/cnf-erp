<?php

namespace App\Http\Requests\Chevron\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-head.edit');
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:chevron_expense_categories,id'],
            'type'                => ['required', 'in:External,Internal'],
            'amount'              => ['nullable', 'numeric', 'min:0'],
            'employee_ids'        => ['array'],
            'employee_ids.*'      => ['exists:chevron_employees,id'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit expense heads.');
    }
}
