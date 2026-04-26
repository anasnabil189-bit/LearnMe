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
        $languageId = \Illuminate\Support\Facades\DB::table('languages')->insertGetId([
            'name' => 'English',
            'code' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('levels')->update(['language_id' => $languageId]);
        \Illuminate\Support\Facades\DB::table('lessons')->update(['language_id' => $languageId]);
        \Illuminate\Support\Facades\DB::table('quizzes')->update(['language_id' => $languageId]);

        $users = \Illuminate\Support\Facades\DB::table('users')->get(['id', 'learning_xp']);
        $userLanguages = [];
        $now = now();
        foreach ($users as $user) {
            $userLanguages[] = [
                'user_id' => $user->id,
                'language_id' => $languageId,
                'learning_xp' => $user->learning_xp ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($userLanguages, 500) as $chunk) {
            \Illuminate\Support\Facades\DB::table('user_languages')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('user_languages')->truncate();
        \Illuminate\Support\Facades\DB::table('languages')->truncate();
    }
};
