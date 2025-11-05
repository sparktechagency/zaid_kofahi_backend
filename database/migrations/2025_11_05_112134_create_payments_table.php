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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->enum('event_type',['single','team']);
            $table->string('winner')->nullable();
            $table->string('organizer')->nullable();
            $table->decimal('amount',10,2)->default(0);
            $table->date('date');
            $table->enum('status',['Pending','Completed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
