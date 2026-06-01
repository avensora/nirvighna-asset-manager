<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Expand ENUM to include new roles, keeping old values temporarily
        DB::statement("ALTER TABLE users MODIFY role ENUM('master_admin','manager','team_lead','team_member') NOT NULL DEFAULT 'team_lead'");

        // Migrate existing data
        DB::statement("UPDATE users SET role = 'team_lead' WHERE role = 'team_member'");

        // Remove old team_member value from ENUM now that no rows reference it
        DB::statement("ALTER TABLE users MODIFY role ENUM('master_admin','manager','team_lead') NOT NULL DEFAULT 'team_lead'");
    }

    public function down(): void
    {
        // Re-expand to include both before migrating back
        DB::statement("ALTER TABLE users MODIFY role ENUM('master_admin','manager','team_lead','team_member') NOT NULL DEFAULT 'team_member'");

        DB::statement("UPDATE users SET role = 'manager' WHERE role = 'master_admin'");
        DB::statement("UPDATE users SET role = 'team_member' WHERE role = 'team_lead'");

        DB::statement("ALTER TABLE users MODIFY role ENUM('manager','team_member') NOT NULL DEFAULT 'team_member'");
    }
};
