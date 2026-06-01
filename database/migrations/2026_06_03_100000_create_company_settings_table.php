<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        \DB::table('company_settings')->insert([
            ['key' => 'opening_balance',      'value' => '0',          'created_at' => now(), 'updated_at' => now()],
            ['key' => 'opening_balance_date', 'value' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
