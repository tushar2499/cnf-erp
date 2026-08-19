<?php

namespace App\Http\Requests\Admin\Designation;

use Illuminate\Foundation\Http\FormRequest;

class IndexDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('admin.designations.list');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to view designations.');
    }
}
