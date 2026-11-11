<?php

namespace App\Http\Requests\NasFreights\FreightExportBooking;

use Illuminate\Foundation\Http\FormRequest;

class QuickStoreOverseasAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.overseas-agent.create');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'country'        => ['required', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'designation'    => ['nullable', 'string', 'max:100'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'mobile'         => ['nullable', 'string', 'max:50'],
            'payment_terms'  => ['nullable', 'string', 'max:100'],
            'remarks'        => ['nullable', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Agent Name',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create overseas agents.');
    }
}
