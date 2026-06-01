<?php

namespace App\Enums;

use Carbon\Carbon;

enum RecurrenceType: string
{
    case None      = 'none';
    case Monthly   = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly    = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::None      => 'One-time',
            self::Monthly   => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly    => 'Yearly',
        };
    }

    public function nextDueDate(Carbon $from): Carbon
    {
        return match($this) {
            self::Monthly   => $from->copy()->addMonth(),
            self::Quarterly => $from->copy()->addMonths(3),
            self::Yearly    => $from->copy()->addYear(),
            self::None      => $from->copy(),
        };
    }
}
