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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->unique()->constrained("users")->cascadeOnDelete();
            $table->foreignId("specialization_id")->nullable()->constrained("specializations")->nullOnDelete();
            $table->foreignId("hospital_id")->nullable()->constrained("hospitals")->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable(); // consider doctor belongs to one hospital and will take it's address and latitude , longitude
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text("bio")->nullable();
            $table->string("avatar")->nullable();
            $table->decimal("price", 10, 2)->nullable();
            $table->integer('experience_years')->default(0);
            $table->json('certificates')->nullable();
            $table->string('education')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('language')->default('english');
            $table->boolean("is_active")->default(true);
            $table->timestamps();

            /* certificates example : [
                    {
                        "title": "Laravel Professional",
                        "issuer": "Udemy",
                        "year": 2024
                    },
                    {
                        "title": "PHP Advanced",
                        "issuer": "Coursera",
                        "year": 2023
                    }
                ]
             */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
