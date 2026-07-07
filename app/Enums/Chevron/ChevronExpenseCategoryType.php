<?php

namespace App\Enums\Chevron;

enum ChevronExpenseCategoryType: string
{
    case Bill = 'bill';
    case Job = 'job';

    public function label(): string
    {
        return match ($this) {
            self::Bill => 'Bill',
            self::Job  => 'Job',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Bill => '<span class="badge text-white" style="background-color:#0891b2">'.$this->label().'</span>',
            self::Job  => '<span class="badge bg-primary">'.$this->label().'</span>',
        };
    }
}
