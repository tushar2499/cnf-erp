<?php

namespace App\Http\Requests\NasFreights\FreightBookingExpense;

use Illuminate\Foundation\Http\FormRequest;

class EditFreightBookingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = str_contains($this->path(), 'export-expenses') ? 'export' : 'import';

        return $this->user() && $this->user()->hasPermission("freight.{$type}-expense.edit");
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
