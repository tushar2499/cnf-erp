<?php

namespace App\Http\Requests\Chevron\Item;

use Illuminate\Foundation\Http\FormRequest;

class QuickStoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null;
    }

    public function rules(): array
    {
        return [
            'item_name'     => ['required', 'string', 'max:255'],
            'purchase_unit' => ['nullable', 'string'],
            'item_price'    => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create items.');
    }
}
