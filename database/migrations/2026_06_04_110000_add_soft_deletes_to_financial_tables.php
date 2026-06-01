<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('void_reason', 255)->nullable()->after('notes');
            $table->timestamp('voided_at')->nullable()->after('void_reason');
            $table->foreignId('voided_by')->nullable()->after('voided_at')
                  ->constrained('users')->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('void_reason', 255)->nullable()->after('rejection_reason');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['void_reason', 'voided_at', 'voided_by']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('void_reason');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
