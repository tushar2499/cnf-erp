<?php

namespace App\Http\Requests\NasFreights\Rfq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.rfq.edit');
    }

    public function rules(): array
    {
        return [
            'rfq_date' => ['required', 'date'],
            'type'     => ['required', 'in:import,export'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit RFQs.');
    }
}
