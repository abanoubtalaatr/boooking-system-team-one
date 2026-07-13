<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            // Matches availability_slots.doctor_id (users.id)
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('availability_slot_id')->constrained('availability_slots')->cascadeOnDelete();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('consultation_type');
            $table->string('status')->default('pending');
            $table->decimal('price', 10, 2);
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
