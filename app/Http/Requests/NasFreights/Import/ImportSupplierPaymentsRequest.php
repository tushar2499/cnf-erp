<?php

namespace App\Http\Requests\NasFreights\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportSupplierPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.import.supplier-payments');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import supplier-payments.');
    }
}
