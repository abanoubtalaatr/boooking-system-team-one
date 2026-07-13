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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->string('source')->nullable(); // chat, search,  favorite
            $table->timestamps();

            // Relationships: 
            $table->foreignId('user_id')->constrained('users'); 

            // constraints:
            $table->unique(['user_id', 'query', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
