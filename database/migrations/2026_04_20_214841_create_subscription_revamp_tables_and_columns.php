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
        // 1. Remove required_tier from lessons and quizzes
        if (Schema::hasColumn('lessons', 'required_tier')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropColumn('required_tier');
            });
        }

        if (Schema::hasColumn('quizzes', 'required_tier')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('required_tier');
            });
        }

        // 2. Add trial_ends_at to users
        if (!Schema::hasColumn('users', 'trial_ends_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_tier');
            });
        }

        // 3. Create daily_user_usages table
        if (!Schema::hasTable('daily_user_usages')) {
            Schema::create('daily_user_usages', function (Blueprint $table) {
                $table->id();
                $table->string('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('usage_type', ['lesson', 'comprehensive_quiz']);
                $table->unsignedBigInteger('item_id')->comment('lesson_id or quiz_id');
                $table->date('usage_date');
                $table->timestamps();

                $table->unique(['user_id', 'usage_type', 'item_id', 'usage_date'], 'user_daily_usage_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_user_usages');

        if (Schema::hasColumn('users', 'trial_ends_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('trial_ends_at');
            });
        }

        if (!Schema::hasColumn('lessons', 'required_tier')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->string('required_tier')->default('free');
            });
        }

        if (!Schema::hasColumn('quizzes', 'required_tier')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->string('required_tier')->default('free');
            });
        }
    }
};
