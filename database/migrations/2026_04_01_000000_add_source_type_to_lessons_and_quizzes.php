<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('source_type', ['admin', 'teacher'])->after('video_url')->nullable();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('source_type', ['admin', 'teacher'])->after('title')->nullable();
        });

        // Update existing lessons
        DB::table('lessons')->whereNotNull('level_id')->update(['source_type' => 'admin']);
        DB::table('lessons')->whereNotNull('class_id')->update(['source_type' => 'teacher']);
        DB::table('lessons')->whereNull('source_type')->update(['source_type' => 'admin']);

        // Update existing quizzes
        DB::table('quizzes')->whereNotNull('level_id')->update(['source_type' => 'admin']);
        DB::table('quizzes')->whereNotNull('class_id')->update(['source_type' => 'teacher']);
        DB::table('quizzes')->whereNull('source_type')->update(['source_type' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });
    }
};
