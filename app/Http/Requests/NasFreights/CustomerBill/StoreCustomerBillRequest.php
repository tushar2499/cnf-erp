<?php

namespace App\Http\Requests\NasFreights\CustomerBill;

use App\Models\NasFreights\NasFreightsCustomerBill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.customer-bill.create');
    }

    public function rules(): array
    {
        return [
            'from_date'     => ['required', 'date'],
            'to_date'       => ['required', 'date'],
            'bill_date'     => ['required', 'date'],
            'delivery_type' => ['required'],
            'bill_type'     => ['required'],
            'print_format'  => ['nullable', Rule::in(array_keys(NasFreightsCustomerBill::printFormats()))],
            'tds_percent'   => ['nullable', 'numeric', 'min:0'],
            'ait_percent'   => ['nullable', 'numeric', 'min:0'],
            'vat_percent'   => ['nullable', 'numeric', 'min:0'],
            'vat_on_ait'    => ['nullable', 'boolean'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create customer bills.');
    }
}
