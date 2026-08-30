<?php

namespace App\Http\Requests\Chevron\Item;

use Illuminate\Foundation\Http\FormRequest;

class DestroyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.item.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete items.');
    }
}
