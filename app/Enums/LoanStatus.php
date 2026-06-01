<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Outstanding     = 'outstanding';
    case PartiallyRepaid = 'partially_repaid';
    case Repaid          = 'repaid';

    public function label(): string
    {
        return match($this) {
            self::Outstanding     => 'Outstanding',
            self::PartiallyRepaid => 'Partially Repaid',
            self::Repaid          => 'Repaid',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Outstanding     => 'bg-danger',
            self::PartiallyRepaid => 'bg-warning text-dark',
            self::Repaid          => 'bg-success',
        };
    }
}
