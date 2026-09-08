<?php

namespace App\Http\Requests\NasFreights\Rfq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.rfq.edit');
    }

    public function rules(): array
    {
        return [
            'status'      => ['required', 'in:Draft,Pending,Win,Lose'],
            'lost_reason' => ['required_if:status,Lose'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit RFQs.');
    }
}
