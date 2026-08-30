<?php

namespace App\Http\Requests\Chevron\Service;

use Illuminate\Foundation\Http\FormRequest;

class DestroyServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.service.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete services.');
    }
}
