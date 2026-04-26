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
        DB::statement("ALTER TABLE quizzes MODIFY COLUMN source_type ENUM('admin', 'teacher', 'user') DEFAULT 'admin'");
        DB::statement("ALTER TABLE lessons MODIFY COLUMN source_type ENUM('admin', 'teacher', 'user') DEFAULT 'admin'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quizzes MODIFY COLUMN source_type ENUM('admin', 'teacher') DEFAULT 'admin'");
        DB::statement("ALTER TABLE lessons MODIFY COLUMN source_type ENUM('admin', 'teacher') DEFAULT 'admin'");
    }
};
