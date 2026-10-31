<?php

namespace App\Http\Requests\NasFreights\FreightBookingExpense;

use Illuminate\Foundation\Http\FormRequest;

class IndexFreightBookingExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = str_contains($this->path(), 'export-expenses') ? 'export' : 'import';

        return $this->user() && $this->user()->hasPermission("freight.{$type}-expense.list");
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
