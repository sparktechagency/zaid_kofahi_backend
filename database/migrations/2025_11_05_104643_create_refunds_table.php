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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('event_id');
            $table->string('event_name');
            $table->enum('event_type',['single','team']);
            $table->unsignedInteger('participants')->default(0);
            $table->decimal('total_refund_amount',10,2)->default(0);
            $table->enum('status',['Pending','Completed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
