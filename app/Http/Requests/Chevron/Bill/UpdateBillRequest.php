<?php

namespace App\Http\Requests\Chevron\Bill;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.bill.edit');
    }

    public function rules(): array
    {
        return [
            'bill_date'                  => ['required', 'date'],
            'job_no'                     => ['required', 'string'],
            'rows'                       => ['required', 'array', 'min:1'],
            'rows.*.expense_category_id' => ['required'],
            'rows.*.expense_head_id'     => ['nullable', Rule::exists('chevron_expense_heads', 'id')->where('is_bill', true)],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit bills.');
    }
}
