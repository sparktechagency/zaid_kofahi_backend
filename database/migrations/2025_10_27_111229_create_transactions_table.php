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
            $table->foreignId('user_id')->constrained('users');
            $table->integer('event_id')->nullable();
            $table->enum('type',['Deposit','Entry Free','Platform Free','Withdraw','Payout','Refund','Earning','Winning']);
            $table->decimal('amount',10,2)->default(0);
            $table->timestamp('date');
            $table->enum('status',['Completed'])->default('Completed');
            $table->timestamps();
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
