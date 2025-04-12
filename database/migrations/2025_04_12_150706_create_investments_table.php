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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('investment_plan_id')->constrained();
            $table->string('reference_id')->unique(); // Unique reference for tracking
            $table->decimal('amount', 12, 2);         // Initial investment amount
            $table->decimal('current_value', 12, 2);  // Current value with accrued interest
            $table->decimal('return_rate', 8, 4);     // Locked-in return rate at time of investment
            $table->integer('lock_period');           // Locked-in lock period at time of investment
            $table->date('start_date');               // Investment start date
            $table->date('end_date');                 // Date when investment becomes eligible for withdrawal
            $table->date('withdrawn_at')->nullable(); // Date when investment was withdrawn
            $table->enum('status', ['active', 'pending', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
