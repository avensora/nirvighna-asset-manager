<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft   = 'draft';
    case Sent    = 'sent';
    case Partial = 'partial';
    case Paid    = 'paid';

    public function label(): string
    {
        return match($this) {
            self::Draft   => 'Draft',
            self::Sent    => 'Sent',
            self::Partial => 'Partial',
            self::Paid    => 'Paid',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft   => 'bg-secondary',
            self::Sent    => 'bg-info text-dark',
            self::Partial => 'bg-warning text-dark',
            self::Paid    => 'bg-success',
        };
    }
}
