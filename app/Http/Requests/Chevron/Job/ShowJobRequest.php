<?php

namespace App\Http\Requests\Chevron\Job;

use Illuminate\Foundation\Http\FormRequest;

class ShowJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view this job.');
    }
}
