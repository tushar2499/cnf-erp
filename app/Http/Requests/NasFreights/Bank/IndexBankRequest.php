<?php

namespace App\Http\Requests\NasFreights\Bank;

use Illuminate\Foundation\Http\FormRequest;

class IndexBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.bank.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view banks.');
    }
}
