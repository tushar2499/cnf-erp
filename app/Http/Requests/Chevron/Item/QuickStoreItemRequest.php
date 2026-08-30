<?php

namespace App\Http\Requests\Chevron\Item;

use Illuminate\Foundation\Http\FormRequest;

class QuickStoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.item.create');
    }

    public function rules(): array
    {
        return [
            'item_name'     => ['required', 'string', 'max:255'],
            'purchase_unit' => ['required', 'string'],
            'item_price'    => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create items.');
    }
}
