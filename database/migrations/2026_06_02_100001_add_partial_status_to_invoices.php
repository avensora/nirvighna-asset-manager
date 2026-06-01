<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','partial','paid') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE invoices SET status = 'sent' WHERE status = 'partial'");
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','paid') NOT NULL DEFAULT 'draft'");
    }
};
