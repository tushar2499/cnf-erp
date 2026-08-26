<?php

namespace App\Http\Requests\Chevron\Bill;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.bill.create');
    }

    public function rules(): array
    {
        return [
            'bill_date'                  => ['required', 'date'],
            'rows'                       => ['required', 'array', 'min:1'],
            'rows.*.expense_category_id' => ['required'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create bills.');
    }
}
