<?php

namespace App\Http\Requests\Chevron\ExpenseHead;

use Illuminate\Foundation\Http\FormRequest;

class ImportPreviewExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.expense-head.create');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import expense heads.');
    }
}
