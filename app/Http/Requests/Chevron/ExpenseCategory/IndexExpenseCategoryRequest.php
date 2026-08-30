<?php

namespace App\Http\Requests\Chevron\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class IndexExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-category.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view expense categories.');
    }
}
