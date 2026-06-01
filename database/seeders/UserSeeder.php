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
        // Manager account
        User::firstOrCreate(
            ['email' => 'admin@nirvighna.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Manager,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Demo team member
        User::firstOrCreate(
            ['email' => 'team@nirvighna.test'],
            [
                'name' => 'Team Member',
                'password' => Hash::make('password'),
                'role' => UserRole::TeamMember,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
