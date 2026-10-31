<?php

namespace App\Http\Requests\NasFreights\ExpenseCategory;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('freight.expense-category.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403);
    }
}
