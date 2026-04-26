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
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('school_language_id')->nullable()->constrained('school_languages')->onDelete('set null');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('school_language_id')->nullable()->constrained('school_languages')->onDelete('set null');
            $table->enum('academic_type', ['lesson', 'general'])->default('lesson');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['school_language_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['school_language_id', 'grade_id', 'academic_type']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['school_language_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['school_language_id', 'grade_id']);
        });
    }
};
