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
            $table->boolean('is_global')->default(true)->after('id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });
    }
};
