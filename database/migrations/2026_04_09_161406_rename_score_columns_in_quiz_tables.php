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
        Schema::table('user_quiz_summary', function (Blueprint $table) {
            $table->renameColumn('best_percentage', 'best_total_points');
        });

        Schema::table('user_quiz_summary', function (Blueprint $table) {
            $table->integer('best_total_points')->default(0)->change();
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->renameColumn('total_questions', 'total_points');
            $table->renameColumn('percentage', 'unused_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('user_quiz_summary', function (Blueprint $table) {
            $table->renameColumn('best_total_points', 'best_percentage');
        });

        Schema::table('user_quiz_summary', function (Blueprint $table) {
            $table->decimal('best_percentage', 5, 2)->default(0)->change();
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->renameColumn('total_points', 'total_questions');
            $table->renameColumn('unused_percentage', 'percentage');
        });
    }
};
