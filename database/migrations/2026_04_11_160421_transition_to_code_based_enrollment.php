<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // School code already exists in schools table migration 2024_01_01_000001_create_schools_table.php

        Schema::table('grades', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('teacher_code')->unique()->nullable()->after('type');
        });

        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 10);
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['student_id', 'grade_id']);
        });

        Schema::create('student_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 10);
            $table->string('teacher_id', 10);
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['student_id', 'teacher_id']);
        });

        Schema::disableForeignKeyConstraints();
        
        // Remove class_id from lessons
        if (Schema::hasColumn('lessons', 'class_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                // Drop foreign key first
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['class_id']);
                }
                $table->dropColumn('class_id');
            });
        }
        
        // Remove class_id from quizzes
        if (Schema::hasColumn('quizzes', 'class_id')) {
            Schema::table('quizzes', function (Blueprint $table) {
                // Drop foreign key first
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['class_id']);
                }
                $table->dropColumn('class_id');
            });
        }

        Schema::dropIfExists('class_user');
        Schema::dropIfExists('classes');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('teacher_code');
        });

        Schema::dropIfExists('student_grades');
        Schema::dropIfExists('student_teachers');
    }
};
