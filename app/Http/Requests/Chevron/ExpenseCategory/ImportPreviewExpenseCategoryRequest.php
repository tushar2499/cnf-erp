<?php

namespace App\Http\Requests\Chevron\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class ImportPreviewExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-category.create');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import expense categories.');
    }
}
