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

            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('booking_number')->unique();
            $table->decimal('price', 10, 2);

            $table->string('consultation_type');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');

            $table->timestamps();

            // Foreign keys
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('doctor_id')->constrained('users');

            $table->foreignId('availability_slot_id')->constrained();
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
