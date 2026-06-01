<?php

namespace App\Enums;

enum LoanSourceType: string
{
    case Person = 'person';
    case Bank   = 'bank';
    case Other  = 'other';

    public function label(): string
    {
        return match($this) {
            self::Person => 'Person',
            self::Bank   => 'Bank / Financial Institution',
            self::Other  => 'Other',
        };
    }
}
