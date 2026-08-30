<?php

namespace App\Http\Requests\Chevron\Bill;

use Illuminate\Foundation\Http\FormRequest;

class PrintBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.bill.print');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to print bills.');
    }
}
