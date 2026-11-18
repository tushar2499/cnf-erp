<?php

namespace App\Http\Requests\Chevron\Job;

use App\Models\Chevron\ChevronJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job.edit');
    }

    public function rules(): array
    {
        $job = $this->route('job');
        $rootId = $job?->parent_job_id ?? $job?->id;
        $familyIds = ChevronJob::where('id', $rootId)
            ->orWhere('parent_job_id', $rootId)
            ->pluck('id');

        return [
            'job_type_id'      => ['required'],
            'port_id'          => ['required'],
            'party_name'       => ['required', 'string', 'max:255'],
            'goods_name'       => ['required', 'string', 'max:255'],
            'job_date'         => ['required', 'date'],
            'hbi_hawb_no'      => [
                'required', 'string', 'max:255',
                Rule::unique('chevron_jobs', 'hbi_hawb_no')->where(fn ($q) => $q->whereNotIn('id', $familyIds)),
            ],
            'received_amount'  => ['nullable', 'numeric', 'min:0'],
            'assessable_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit jobs.');
    }
}
