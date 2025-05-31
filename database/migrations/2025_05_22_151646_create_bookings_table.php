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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_id')->nullable();
            $table->string('event_name')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->integer('venue_id')->nullable();
            $table->string('venue_name')->nullable();
            $table->decimal('venue_price',10,2)->nullable();
            $table->string('venue_address')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('booking_date')->nullable();
            $table->integer('number_of_invites')->nullable();
            $table->enum('status', ['accepted', 'rejected', 'waiting'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
