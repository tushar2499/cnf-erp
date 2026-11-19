<?php

namespace App\Http\Requests\Chevron\MoneyReceipt;

use App\Models\Chevron\ChevronMoneyReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMoneyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.money-receipt.create');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'receipt_date'                 => ['required', 'date'],
            'receipt_type'                 => ['required', Rule::in(ChevronMoneyReceipt::receiptTypes())],
            'party_id'                     => ['nullable', 'integer'],
            'party_name'                   => ['required', 'string', 'max:255'],
            'pay_type'                     => ['required', 'string'],
            'description'                  => ['nullable', 'string'],
            'advance_mode'                 => ['nullable', Rule::in(['general', 'job'])],
            'job_allocations'              => ['nullable', 'array'],
            'job_allocations.*.job_id'     => ['required', 'integer', 'distinct'],
            'job_allocations.*.amount'     => ['required', 'numeric', 'min:0.01'],
            'allocations'                  => ['required_if:receipt_type,'.ChevronMoneyReceipt::TYPE_BILL, 'nullable', 'array'],
            'allocations.*.bill_id'        => ['required', 'integer', 'distinct'],
            'allocations.*.amount'         => ['required', 'numeric', 'min:0.01'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.payment_type'         => ['required', 'string'],
            'items.*.account_id'           => ['nullable', 'integer'],
            'items.*.account_no'           => ['nullable', 'string', 'max:100'],
            'items.*.cheque_card_holder'   => ['nullable', 'string', 'max:255'],
            'items.*.cheque_card_no'       => ['nullable', 'string', 'max:100'],
            'items.*.amount'               => ['required', 'numeric', 'min:0.01'],
            'items.*.cheque_date'          => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'allocations.required_if' => 'Select at least one bill to collect against.',
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to create money receipts.');
    }
}
