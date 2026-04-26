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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('user_id')->nullable()->after('id')->index();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('user_id')->nullable()->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
