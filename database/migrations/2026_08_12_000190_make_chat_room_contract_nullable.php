<?php
// database/migrations/2026_08_12_000190_make_chat_room_contract_nullable.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropUnique(['contract_id']);
            $table->foreignId('contract_id')->nullable()->change();
            $table->unique(['client_id', 'freelancer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropUnique(['client_id', 'freelancer_id']);
            $table->foreignId('contract_id')->nullable(false)->change();
            $table->unique('contract_id');
        });
    }
};