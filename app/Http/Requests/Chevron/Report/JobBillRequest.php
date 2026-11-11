<?php

namespace App\Http\Requests\Chevron\Report;

use Illuminate\Foundation\Http\FormRequest;

class JobBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.report.job-bill');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view the job bill report.');
    }
}
