<?php

namespace App\Http\Requests\NasFreights\OverseasAgent;

use Illuminate\Foundation\Http\FormRequest;

class StoreOverseasAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.overseas-agent.create');
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'email'   => ['nullable', 'email', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create overseas agents.');
    }
}
