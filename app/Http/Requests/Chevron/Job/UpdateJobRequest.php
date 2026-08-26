<?php

namespace App\Http\Requests\Chevron\Job;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasPermission('cnf.job.edit');
    }

    public function rules(): array
    {
        $jobId = $this->route('job')?->id;

        return [
            'job_type_id'      => ['required'],
            'port_id'          => ['required'],
            'party_name'       => ['required', 'string', 'max:255'],
            'goods_name'       => ['required', 'string', 'max:255'],
            'job_date'         => ['required', 'date'],
            'hbi_hawb_no'      => ['required', 'string', 'max:255', 'unique:chevron_jobs,hbi_hawb_no,'.$jobId],
            'received_amount'  => ['nullable', 'numeric', 'min:0'],
            'assessable_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to edit jobs.');
    }
}
