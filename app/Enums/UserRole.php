<?php

namespace App\Enums;

enum UserRole: string
{
    case MasterAdmin = 'master_admin';
    case Manager = 'manager';
    case TeamLead = 'team_lead';

    public function label(): string
    {
        return match($this) {
            UserRole::MasterAdmin => 'Master Admin',
            UserRole::Manager     => 'Manager',
            UserRole::TeamLead    => 'Team Lead',
        };
    }

    public function rank(): int
    {
        return match($this) {
            UserRole::MasterAdmin => 3,
            UserRole::Manager     => 2,
            UserRole::TeamLead    => 1,
        };
    }
}
