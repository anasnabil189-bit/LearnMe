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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('school_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('grade_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_language_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['grade_id', 'school_language_id'], 'grade_language_unique');
        });

        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_id', 10);
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_language_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['teacher_id', 'grade_id', 'school_language_id'], 'teacher_assignment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('grade_languages');
        Schema::dropIfExists('school_languages');
        Schema::dropIfExists('grades');
    }
};
