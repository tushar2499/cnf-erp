<?php

namespace App\Http\Requests\Chevron\JobType;

use Illuminate\Foundation\Http\FormRequest;

class IndexJobTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job-type.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view job types.');
    }
}
