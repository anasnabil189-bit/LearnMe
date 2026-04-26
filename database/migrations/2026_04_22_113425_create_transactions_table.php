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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10)->index();
            $table->string('plan'); // individual, family, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('payment_method')->nullable(); // card, wallet, fawry
            $table->string('paymob_order_id')->nullable()->unique();
            $table->string('paymob_transaction_id')->nullable()->unique();
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->json('raw_response')->nullable(); // Store the full callback data for debugging
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
