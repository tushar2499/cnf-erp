<?php

namespace App\Http\Requests\NasFreights\DueList;

use Illuminate\Foundation\Http\FormRequest;

class ViewDueListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.due-list.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view due lists.');
    }
}
