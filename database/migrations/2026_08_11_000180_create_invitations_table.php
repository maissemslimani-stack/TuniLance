<?php
// database/migrations/2026_08_11_000180_create_invitations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending | accepted | declined
            $table->timestamps();

            $table->unique(['project_id', 'freelancer_id']); // one invite per freelancer per project
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};