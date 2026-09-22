<?php

namespace App\Http\Requests\NasFreights\FreightBookingExpense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreightBookingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = str_contains($this->path(), 'export-expenses') ? 'export' : 'import';

        return $this->user() && $this->user()->hasPermission("freight.{$type}-expense.edit");
    }

    public function rules(): array
    {
        return [
            'date'                      => 'required|date',
            'employee_id'               => 'nullable|exists:nas_freights_employees,id',
            'rows'                      => 'required|array|min:1',
            'rows.*.expense_head_id'    => 'required|exists:nas_freights_expense_heads,id',
            'rows.*.expense_amount'     => 'required|numeric|min:0',
            'rows.*.approved_amount'    => 'nullable|numeric|min:0',
            'rows.*.receiptable'        => 'nullable|in:Yes,No',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403);
    }
}
