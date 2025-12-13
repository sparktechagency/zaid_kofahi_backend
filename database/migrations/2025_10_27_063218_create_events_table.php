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
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->string('sport_type');
            $table->string('sport_name');
            $table->date('starting_date');
            $table->date('ending_date');
            $table->time('time');
            $table->string('location');
            $table->unsignedInteger('number_of_player_required')->default(0);
            $table->unsignedInteger('number_of_team_required')->default(0);
            $table->unsignedInteger('number_of_player_required_in_a_team')->default(0);
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->decimal('prize_amount', 10, 2)->default(0);
            $table->json('prize_distribution');
            $table->longText('rules_guidelines');
            $table->string('image');
            $table->enum('status', ['Pending Payment', 'Upcoming', 'Cancelled', 'Ongoing', 'Event Over', 'Awaiting Confirmation', 'Completed', ])->default('Pending Payment');
            $table->unsignedBigInteger('view')->default(0);
            $table->unsignedBigInteger('share')->default(0);
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
