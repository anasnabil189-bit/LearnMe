<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('level_user')) {
            Schema::create('level_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('level_id')->constrained()->onDelete('cascade');
                $table->string('user_id', 10);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['level_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('lesson_user')) {
            Schema::create('lesson_user', function (Blueprint $table) {
                $table->id();
                $table->string('user_id');
                $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
                $table->boolean('passed')->default(false);
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_user');
        Schema::dropIfExists('lesson_user');
    }
};
