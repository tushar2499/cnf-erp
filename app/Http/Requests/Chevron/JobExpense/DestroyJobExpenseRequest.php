<?php

namespace App\Http\Requests\Chevron\JobExpense;

use Illuminate\Foundation\Http\FormRequest;

class DestroyJobExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job-expense.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete job expenses.');
    }
}
