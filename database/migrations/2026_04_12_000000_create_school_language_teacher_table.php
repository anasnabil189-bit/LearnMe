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
        Schema::create('school_language_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_language_id')->constrained()->onDelete('cascade');
            $table->string('teacher_id'); // User ID is a string (e.g. AB1234)
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_language_teacher');
    }
};
