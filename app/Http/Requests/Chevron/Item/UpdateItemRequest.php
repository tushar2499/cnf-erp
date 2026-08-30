<?php

namespace App\Http\Requests\Chevron\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.item.edit');
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'item_code'           => ['required', 'string', 'max:100', 'unique:chevron_items,item_code,'.$itemId],
            'purchase_unit'       => ['required', 'string'],
            'item_price'          => ['required', 'numeric', 'min:0'],
            'item_name'           => ['nullable', 'string', 'max:255'],
            'supplier'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'remarks'             => ['nullable', 'string'],
            'image'               => ['nullable', 'image'],
            'availability_in_po'  => ['sometimes', 'boolean'],
            'availability_in_so'  => ['sometimes', 'boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit items.');
    }
}
