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
        Schema::dropIfExists('results');
        Schema::dropIfExists('xp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Results table schema (partial/best effort)
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Match User ID type
            $table->foreignId('quiz_id');
            $table->decimal('score', 8, 2);
            $table->json('details')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        Schema::create('xp', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->integer('xp_points')->default(0);
            $table->timestamps();
        });
    }
};
