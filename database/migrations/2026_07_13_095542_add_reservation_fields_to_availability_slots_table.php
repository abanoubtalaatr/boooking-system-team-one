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
        Schema::table('availability_slots', function (Blueprint $table) {
            $table->string('reservation_status')->default('available')->index()->after('is_booked');
            $table->foreignId('reserved_booking_id')->nullable()->unique()->after('reservation_status')->constrained('bookings')->nullOnDelete();
            $table->timestamp('reserved_until')->nullable()->index()->after('reserved_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availability_slots', function (Blueprint $table) {
            $table->dropForeign(['reserved_booking_id']);
            $table->dropColumn(['reservation_status', 'reserved_booking_id', 'reserved_until']);
        });
    }
};
