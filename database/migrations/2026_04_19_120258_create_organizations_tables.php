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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['school', 'company', 'university', 'center'])->default('company');
            $table->decimal('discount_percentage', 5, 2)->default(0.00); // e.g., 50.00 for 50%
            $table->integer('max_users')->nullable();
            $table->string('subscription_plan')->default('freemium'); // Or custom tier logic for orgs
            $table->timestamps();
        });

        Schema::create('organization_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Using string because users.id is string
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->enum('role', ['student', 'instructor', 'admin'])->default('student');
            $table->timestamp('joined_at')->useCurrent();
            
            // Assuming users table has 'id' as type string based on documentation
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['user_id', 'organization_id']);
        });

        Schema::create('organization_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_codes');
        Schema::dropIfExists('organization_users');
        Schema::dropIfExists('organizations');
    }
};
