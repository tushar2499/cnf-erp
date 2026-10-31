<?php

namespace App\Http\Requests\NasFreights\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.supplier.edit');
    }

    public function rules(): array
    {
        return [
            'company_name'   => ['required', 'string', 'max:255'],
            'owner_name'     => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'phone_no'       => ['nullable', 'string', 'max:30'],
            'fax'            => ['nullable', 'string', 'max:30'],
            'url'            => ['nullable', 'string', 'max:255'],
            'mobile_no'      => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'contact'        => ['nullable', 'string', 'max:255'],
            'designation'    => ['nullable', 'string', 'max:255'],
            'supplier_group' => ['nullable', 'string'],
            'taxscope'       => ['required', 'string'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit suppliers.');
    }
}
