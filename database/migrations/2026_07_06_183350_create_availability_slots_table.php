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
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId("doctor_id")->constrained("users")->cascadeOnDelete();
            $table->date("day"); 
            $table->time("start_time");
            $table->time("end_time");
            $table->boolean("is_booked")->default(false);
            $table->timestamps();

            $table->unique(["doctor_id" , "day" , "start_time" , "end_time"]);
            $table->index(["doctor_id" , "day"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};
