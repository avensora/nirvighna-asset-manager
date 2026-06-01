<?php

namespace App\Enums;

enum ProjectPriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match($this) {
            ProjectPriority::Low    => 'Low',
            ProjectPriority::Medium => 'Medium',
            ProjectPriority::High   => 'High',
            ProjectPriority::Urgent => 'Urgent',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            ProjectPriority::Low    => 'bg-secondary-subtle text-secondary',
            ProjectPriority::Medium => 'bg-info-subtle text-info',
            ProjectPriority::High   => 'bg-warning-subtle text-warning',
            ProjectPriority::Urgent => 'bg-danger-subtle text-danger',
        };
    }
}
