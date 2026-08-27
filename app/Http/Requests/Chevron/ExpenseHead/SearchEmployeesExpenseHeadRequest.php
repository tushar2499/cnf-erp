<?php

namespace App\Http\Requests\Chevron\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class SearchEmployeesExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-head.edit');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage expense heads.');
    }
}
