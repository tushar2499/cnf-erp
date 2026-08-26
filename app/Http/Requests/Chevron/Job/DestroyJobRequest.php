<?php

namespace App\Http\Requests\Chevron\Job;

use Illuminate\Foundation\Http\FormRequest;

class DestroyJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job.delete');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to delete jobs.');
    }
}
