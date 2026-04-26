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
        Schema::table('levels', function (Blueprint $table) {
            $table->foreignId('language_id')->nullable()->constrained('languages')->cascadeOnDelete();
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('language_id')->nullable()->constrained('languages')->cascadeOnDelete();
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('language_id')->nullable()->constrained('languages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_tables', function (Blueprint $table) {
            //
        });
    }
};
