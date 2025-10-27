<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('sport_type');
            $table->string('entry_type');
            $table->date('starting_date');
            $table->date('ending_date');
            $table->time('time');
            $table->string('location');
            $table->integer('number_of_player_required')->default(0);
            $table->decimal('entry_free', 10, 2)->default(0);
            $table->decimal('prize_amount', 10, 2)->default(0);
            $table->json('prize_distribution');
            $table->string('rules_guidelines');
            $table->string('image');
            $table->enum('status', ['Pending Payment', 'Upcoming', 'Completed'])->default('Pending Payment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
