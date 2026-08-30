<?php

namespace App\Http\Requests\Chevron\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-category.edit');
    }

    public function rules(): array
    {
        $categoryId = $this->route('expenseCategory')?->id;

        return [
            'name'        => ['required', 'string', 'max:255', 'unique:chevron_expense_categories,name,'.$categoryId],
            'is_bill'     => ['sometimes', 'boolean'],
            'is_job'      => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit expense categories.');
    }
}
