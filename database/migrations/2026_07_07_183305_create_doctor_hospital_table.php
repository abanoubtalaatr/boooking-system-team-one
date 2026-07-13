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
        Schema::create('doctor_hospital', function (Blueprint $table) {
            //$table->id();
            $table->foreignId("doctor_profile_id")->constrained("doctor_profiles")->cascadeOnDelete();
            $table->foreignId("hospital_id")->constrained("hospitals")->cascadeOnDelete();
            $table->primary(["doctor_profile_id", "hospital_id"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_hospital');
    }
};
