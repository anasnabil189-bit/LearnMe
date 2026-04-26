<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('xp_points')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp');
    }
};
