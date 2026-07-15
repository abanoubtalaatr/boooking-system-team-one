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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('creation_idempotency_key', 100)->nullable()->after('payment_status');
            $table->timestamp('hold_expires_at')->nullable()->index()->after('creation_idempotency_key');

            $table->unique(['patient_id', 'creation_idempotency_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['patient_id', 'creation_idempotency_key']);
            $table->dropColumn(['creation_idempotency_key', 'hold_expires_at']);
        });
    }
};
