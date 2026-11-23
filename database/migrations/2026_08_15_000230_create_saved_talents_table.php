<?php
// database/migrations/2026_08_15_000230_create_saved_talents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'freelancer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_talents');
    }
};