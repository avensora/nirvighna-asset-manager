<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Master Admin — full platform control
        User::updateOrCreate(
            ['email' => 'admin@nirvighna.test'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password'),
                'role'              => UserRole::MasterAdmin,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // Manager — daily operations
        User::firstOrCreate(
            ['email' => 'manager@nirvighna.test'],
            [
                'name'              => 'Manager',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Manager,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // Team Lead — project execution
        User::updateOrCreate(
            ['email' => 'team@nirvighna.test'],
            [
                'name'              => 'Team Lead',
                'password'          => Hash::make('password'),
                'role'              => UserRole::TeamLead,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
