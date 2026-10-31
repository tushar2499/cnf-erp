<?php

namespace App\Http\Requests\NasFreights\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class DestroySupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete suppliers.');
    }
}
