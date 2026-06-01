<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // group_id 0 = company-wide room (avoids NULL unique-constraint issues in MySQL)
        Schema::create('chat_read_cursors', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id')->default(0);
            $table->unsignedBigInteger('last_read_message_id')->default(0);

            $table->primary(['user_id', 'group_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_read_cursors');
    }
};
