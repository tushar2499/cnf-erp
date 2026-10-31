<?php

namespace App\Http\Requests\NasFreights\Branch;

use Illuminate\Foundation\Http\FormRequest;

class IndexBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.branch.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view branches.');
    }
}
