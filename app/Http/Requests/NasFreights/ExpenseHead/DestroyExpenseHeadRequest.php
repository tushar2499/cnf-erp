<?php

namespace App\Http\Requests\NasFreights\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class DestroyExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('freight.expense-head.delete');
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
