<?php

namespace App\Http\Requests\NasFreights\Report;

use Illuminate\Foundation\Http\FormRequest;

class PartyBillSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.report.party-bill-summary');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view party bill summary reports.');
    }
}
