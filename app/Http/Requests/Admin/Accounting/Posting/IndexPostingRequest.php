<?php

namespace App\Http\Requests\Admin\Accounting\Posting;

use Illuminate\Foundation\Http\FormRequest;

class IndexPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.accounting.posting.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view posting.');
    }
}
