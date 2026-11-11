<?php

namespace App\Http\Requests\NasFreights\BillItemDescription;

use Illuminate\Foundation\Http\FormRequest;

class DestroyBillItemDescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.bill-item-description.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete bill item descriptions.');
    }
}
