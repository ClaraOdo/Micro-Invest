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
            $table->string('reference_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('current_value', 12, 2);
            $table->decimal('return_rate', 8, 4);
            $table->integer('lock_period');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('withdrawn_at')->nullable();
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
