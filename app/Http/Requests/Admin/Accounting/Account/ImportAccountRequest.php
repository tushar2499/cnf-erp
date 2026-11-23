<?php

namespace App\Http\Requests\Admin\Accounting\Account;

use Illuminate\Foundation\Http\FormRequest;

class ImportAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.coa.import');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to import the chart of accounts.');
    }
}
