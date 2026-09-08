<?php

namespace App\Http\Requests\NasFreights\Report;

use Illuminate\Foundation\Http\FormRequest;

class BillDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.report.bill-details');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view bill details reports.');
    }
}
