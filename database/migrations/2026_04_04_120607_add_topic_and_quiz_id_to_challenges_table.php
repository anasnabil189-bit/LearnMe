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
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('description');
            $table->integer('questions_count')->default(10)->after('topic');
            $table->string('question_type')->default('multiple_choice')->after('questions_count');
            $table->unsignedBigInteger('quiz_id')->nullable()->after('question_type');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn(['topic', 'questions_count', 'question_type', 'quiz_id']);
        });
    }
};
