<?php

namespace App\Models\NasFreights;

use Database\Factories\NasFreights\NasFreightsBankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NasFreightsBank extends Model
{
    /** @use HasFactory<NasFreightsBankFactory> */
    use HasFactory;

    protected $table = 'nas_freights_banks';

    protected $fillable = [
        'account_name', 'account_number', 'account_type',
        'bank_name', 'bank_branch', 'routing_number', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * One-line label for dropdowns, e.g. "Islami Bank Ltd., Motijheel — 1234567890 (NAS Freights Ltd.)".
     */
    public function optionLabel(): string
    {
        $bank = $this->bank_branch ? $this->bank_name.', '.$this->bank_branch : $this->bank_name;

        return $bank.' — '.$this->account_number.' ('.$this->account_name.')';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('bank_name')->orderBy('account_name');
    }
}
