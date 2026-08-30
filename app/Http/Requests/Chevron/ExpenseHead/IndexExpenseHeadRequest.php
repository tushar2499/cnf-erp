<?php

namespace App\Http\Requests\Chevron\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class IndexExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-head.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view expense heads.');
    }
}
