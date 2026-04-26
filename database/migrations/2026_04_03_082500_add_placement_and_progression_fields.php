<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_taken_placement_test')->default(false)->after('type');
            $table->integer('placement_score')->nullable()->after('has_taken_placement_test');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('level_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('quiz_type', ['placement', 'lesson', 'comprehensive'])->default('lesson')->after('source_type');
            $table->foreignId('lesson_id')->nullable()->after('level_id')->constrained('lessons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_taken_placement_test', 'placement_score']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn(['quiz_type', 'lesson_id']);
        });
    }
};
