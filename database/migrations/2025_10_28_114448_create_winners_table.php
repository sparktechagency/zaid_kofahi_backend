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
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('place');
            $table->unsignedInteger('player_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
            $table->decimal('amount',10,2)->default(0);
            $table->string('additional_prize')->nullable();
            $table->boolean('admin_approval')->default(false);
            $table->enum('status',['Pending','Accepted','Decline'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};



