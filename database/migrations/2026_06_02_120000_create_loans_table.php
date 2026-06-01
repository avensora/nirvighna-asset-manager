<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('source_name', 255);
            $table->enum('source_type', ['person', 'bank', 'other'])->default('person');
            $table->decimal('principal_amount', 15, 2);
            $table->date('borrowed_date');
            $table->date('due_date')->nullable();
            $table->string('purpose', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['outstanding', 'partially_repaid', 'repaid'])->default('outstanding');
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
