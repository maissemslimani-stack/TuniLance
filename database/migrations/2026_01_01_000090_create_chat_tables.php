<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One chat room per contract — the private conversation channel
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->unique()->constrained()->cascadeOnDelete(); // 1:1 with contract
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('freelancer_id')->constrained('users');
            $table->timestamp('last_message_at')->nullable(); // Used for sorting inbox
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();          // Text body (nullable if attachment-only)
            $table->string('attachment_url')->nullable(); // Optional single file attachment
            $table->string('attachment_name')->nullable();
            $table->boolean('is_system_message')->default(false); // Auto-generated messages (e.g. "Milestone funded")
            $table->timestamp('read_at')->nullable();     // When the other party read the message
            $table->timestamps();

            $table->index(['chat_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
    }
};
