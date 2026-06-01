<?php

namespace App\Enums;

enum UserRole: string
{
    case Manager = 'manager';
    case TeamMember = 'team_member';

    public function label(): string
    {
        return match($this) {
            UserRole::Manager => 'Manager',
            UserRole::TeamMember => 'Team Member',
        };
    }
}
