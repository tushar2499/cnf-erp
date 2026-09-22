<?php

namespace App\Http\Requests\NasFreights\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('freight.expense-head.edit');
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'type'                => 'required|string|max:100',
            'expense_category_id' => 'nullable|exists:nas_freights_expense_categories,id',
            'amount'              => 'nullable|numeric|min:0',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403);
    }
}
