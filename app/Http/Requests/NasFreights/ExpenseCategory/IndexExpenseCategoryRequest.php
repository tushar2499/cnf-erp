<?php

namespace App\Http\Requests\NasFreights\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class IndexExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('freight.expense-category.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403);
    }
}
