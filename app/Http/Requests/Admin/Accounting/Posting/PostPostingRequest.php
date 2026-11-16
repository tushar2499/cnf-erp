<?php

namespace App\Http\Requests\Admin\Accounting\Posting;

use Illuminate\Foundation\Http\FormRequest;

class PostPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.posting.post');
    }

    public function rules(): array
    {
        return [
            'company_id'         => ['required', 'integer', 'exists:companies,id'],
            'from'               => ['required', 'date'],
            'to'                 => ['required', 'date', 'after_or_equal:from'],
            'source_type'        => ['required', 'string', 'max:60'],
            'ref'                => ['required', 'string', 'max:120'],
            'narration'          => ['nullable', 'string', 'max:1000'],
            'lines'              => ['nullable', 'array'],
            'lines.*.account_id' => ['nullable', 'integer'],
            'lines.*.debit'      => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit'     => ['nullable', 'numeric', 'min:0'],
            'lines.*.party_type' => ['nullable', 'string', 'max:20'],
            'lines.*.party_id'   => ['nullable', 'integer'],
            'lines.*.party_name' => ['nullable', 'string', 'max:255'],
            'lines.*.narration'  => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to post vouchers.');
    }
}
