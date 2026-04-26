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
        Schema::create('user_quiz_summary', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->integer('best_score')->default(0);
            $table->decimal('best_percentage', 5, 2)->default(0);
            $table->integer('best_xp')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'quiz_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quiz_summary');
    }
};
