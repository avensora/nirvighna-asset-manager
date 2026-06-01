<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dashboard chart + KPI queries: WHERE type = ? AND date BETWEEN ? AND ?
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['type', 'date'], 'transactions_type_date_idx');
        });

        // Outstanding invoices: WHERE status IN (...) — also covers calendar due-date query
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status', 'invoices_status_idx');
            $table->index(['status', 'due_date'], 'invoices_status_due_date_idx');
        });

        // Dashboard upcoming widget: WHERE status = 'pending' AND due_date <= ?
        Schema::table('scheduled_expenses', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'scheduled_expenses_status_due_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_type_date_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_status_idx');
            $table->dropIndex('invoices_status_due_date_idx');
        });

        Schema::table('scheduled_expenses', function (Blueprint $table) {
            $table->dropIndex('scheduled_expenses_status_due_date_idx');
        });
    }
};
