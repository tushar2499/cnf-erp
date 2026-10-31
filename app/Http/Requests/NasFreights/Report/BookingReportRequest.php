<?php

namespace App\Http\Requests\NasFreights\Report;

use Illuminate\Foundation\Http\FormRequest;

class BookingReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('freight.report.booking');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view booking reports.');
    }
}
