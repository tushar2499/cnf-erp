<?php

namespace App\Http\Requests\Admin\Accounting\Posting;

use Illuminate\Foundation\Http\FormRequest;

class EntriesPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.posting.list');
    }

    public function rules(): array
    {
        return [
            'company_id'  => ['required', 'integer', 'exists:companies,id'],
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
            'source_type' => ['nullable', 'string', 'max:60'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view posting.');
    }
}
