<?php

namespace App\Http\Requests\NasFreights\Rfq;

use Illuminate\Foundation\Http\FormRequest;

class ShowRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.rfq.view');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view RFQs.');
    }
}
