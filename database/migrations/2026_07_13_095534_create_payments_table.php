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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('method')->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3);
            $table->unsignedSmallInteger('commission_bps')->default(0);
            $table->unsignedBigInteger('commission_amount_cents')->default(0);
            $table->unsignedBigInteger('doctor_amount_cents');
            $table->string('idempotency_key', 100);
            $table->string('provider')->nullable();
            $table->string('provider_intention_id')->nullable()->index();
            $table->string('provider_order_id')->nullable()->index();
            $table->string('provider_transaction_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->text('provider_client_secret')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'idempotency_key']);
            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
