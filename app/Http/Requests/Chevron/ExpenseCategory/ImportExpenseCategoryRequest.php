<?php

namespace App\Http\Requests\Chevron\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class ImportExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-category.create');
    }

    public function rules(): array
    {
        return [
            'rows' => ['required', 'array', 'min:1'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import expense categories.');
    }
}
