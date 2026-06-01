<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning   = 'planning';
    case Active     = 'active';
    case OnHold     = 'on_hold';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            ProjectStatus::Planning   => 'Planning',
            ProjectStatus::Active     => 'Active',
            ProjectStatus::OnHold     => 'On Hold',
            ProjectStatus::Completed  => 'Completed',
            ProjectStatus::Cancelled  => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            ProjectStatus::Planning   => 'bg-secondary-subtle text-secondary',
            ProjectStatus::Active     => 'bg-success-subtle text-success',
            ProjectStatus::OnHold     => 'bg-warning-subtle text-warning',
            ProjectStatus::Completed  => 'bg-primary-subtle text-primary',
            ProjectStatus::Cancelled  => 'bg-danger-subtle text-danger',
        };
    }
}
