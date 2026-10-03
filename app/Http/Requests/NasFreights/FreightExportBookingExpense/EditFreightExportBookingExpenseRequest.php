<?php

namespace App\Http\Requests\NasFreights\FreightExportBookingExpense;

use Illuminate\Foundation\Http\FormRequest;

class EditFreightExportBookingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.export-booking.expense-manage');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to manage export booking expenses.');
    }
}
