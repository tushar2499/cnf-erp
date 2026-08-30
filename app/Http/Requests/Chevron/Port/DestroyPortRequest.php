<?php

namespace App\Http\Requests\Chevron\Port;

use Illuminate\Foundation\Http\FormRequest;

class DestroyPortRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.port.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete ports.');
    }
}
