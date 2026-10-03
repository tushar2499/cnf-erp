<?php

namespace App\Http\Requests\NasFreights\FreightExportBookingExpense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreightExportBookingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.expense-manage');
    }

    public function rules(): array
    {
        return [
            'date'                   => ['required', 'date'],
            'rows'                   => ['required', 'array', 'min:1'],
            'rows.*.expense_head_id' => ['required', 'integer', 'exists:nas_freights_expense_heads,id'],
            'rows.*.expense_amount'  => ['required', 'numeric', 'min:0'],
            'rows.*.approved_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage export booking expenses.');
    }
}
