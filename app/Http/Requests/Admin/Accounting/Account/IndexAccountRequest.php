<?php

namespace App\Http\Requests\Admin\Accounting\Account;

use Illuminate\Foundation\Http\FormRequest;

class IndexAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.coa.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the chart of accounts.');
    }
}
