<?php

namespace App\Http\Requests\NasFreights\OverseasAgent;

use Illuminate\Foundation\Http\FormRequest;

class IndexOverseasAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.overseas-agent.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view overseas agents.');
    }
}
