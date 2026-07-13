<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();

            $table->morphs('sender'); // sender_id + sender_type + index تلقائي
            $table->enum('type', ['text', 'voice', 'image', 'file'])->default('text');
            $table->text('body')->nullable(); // نص الرسالة، nullable لو النوع attachment بس
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at']); // تسريع جلب الرسائل مرتبة
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};