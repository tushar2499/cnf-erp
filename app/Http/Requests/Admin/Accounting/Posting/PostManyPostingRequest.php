<?php

namespace App\Http\Requests\Admin\Accounting\Posting;

use Illuminate\Foundation\Http\FormRequest;

class PostManyPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.posting.post');
    }

    public function rules(): array
    {
        return [
            'company_id'          => ['required', 'integer', 'exists:companies,id'],
            'from'                => ['required', 'date'],
            'to'                  => ['required', 'date', 'after_or_equal:from'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.source_type' => ['required', 'string', 'max:60'],
            'items.*.ref'         => ['required', 'string', 'max:120'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to post vouchers.');
    }
}
