<?php

namespace App\Enums;

enum ReimbursementStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Rejected   = 'rejected';
    case Reimbursed = 'reimbursed';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Approved   => 'Approved',
            self::Rejected   => 'Rejected',
            self::Reimbursed => 'Reimbursed',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending    => 'bg-warning text-dark',
            self::Approved   => 'bg-info text-dark',
            self::Rejected   => 'bg-danger',
            self::Reimbursed => 'bg-success',
        };
    }
}
