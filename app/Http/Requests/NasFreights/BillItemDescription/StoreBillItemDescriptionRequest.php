<?php

namespace App\Http\Requests\NasFreights\BillItemDescription;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillItemDescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.bill-item-description.create');
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255', 'unique:nas_freights_bill_item_descriptions,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Description is required.',
            'name.unique'   => 'This description already exists. If it is not in the list, it may be inactive.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create bill item descriptions.');
    }
}
