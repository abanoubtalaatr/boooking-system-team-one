<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("bookings", function (Blueprint $table) {
            $table->id();
            $table->foreignid("patient_id")->constrained("users")->cascadeOnDelete();
            // ERD's "receives" relation runs DOCTOR_PROFILES -> BOOKINGS (not users),
            // consistent with availability_slots.doctor_id.
            $table->foreignId("doctor_id")->constrained("doctor_profiles")->cascadeOnDelete();
            $table->foreignId("slot_id")->constrained("availability_slots")->cascadeOnDelete();
            $table->enum("status", ["pending", "confirmed", "rejected", "completed", "cancelled"])->default("pending");
            $table->decimal("price", 10, 2);
            $table->enum("payment_status", ["pending", "paid", "failed", "refunded"])->default("pending");
            $table->timestamps();

            $table->index(["doctor_id", "status"]);
            $table->index(["patient_id", "status"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("bookings");
    }
};
