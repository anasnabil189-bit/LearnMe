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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Get school-related user IDs before deleting them
        $schoolUserIds = DB::table('users')->whereNotNull('school_id')->pluck('id')->toArray();

        // 2. Delete related data for these users
        if (!empty($schoolUserIds)) {
            DB::table('quiz_attempts')->whereIn('user_id', $schoolUserIds)->delete();
            DB::table('user_quiz_summary')->whereIn('user_id', $schoolUserIds)->delete();
            DB::table('daily_user_usages')->whereIn('user_id', $schoolUserIds)->delete();
            DB::table('organization_users')->whereIn('user_id', $schoolUserIds)->delete();
            
            // If there's a student_teachers table (join table)
            if (Schema::hasTable('student_teachers')) {
                DB::table('student_teachers')->whereIn('student_id', $schoolUserIds)->delete();
            }
            
            // Delete the users themselves
            DB::table('users')->whereIn('id', $schoolUserIds)->delete();
        }

        // 3. Delete school-specific content
        DB::table('lessons')->where('is_global', false)->delete();
        DB::table('quizzes')->where('is_global', false)->delete();

        // 4. Wipe school structural tables
        DB::table('teacher_assignments')->truncate();
        DB::table('school_languages')->truncate();
        DB::table('grades')->truncate();
        DB::table('schools')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse a complete wipe.
    }
};
