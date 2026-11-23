<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ESCROW FLOW:
        | 1. Client funds a milestone → Escrow status: 'funded', Wallet held_balance++
        | 2. Freelancer submits deliverable → Escrow status stays 'funded'
        | 3a. Client approves → Escrow status: 'released', funds credited to freelancer wallet
        | 3b. Client raises dispute → Escrow status: 'disputed', moderator reviews
        | 3c. Dispute resolved in client's favor → Escrow status: 'refunded'
        |--------------------------------------------------------------------------
        */
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->unique()->constrained()->cascadeOnDelete(); // 1 escrow per milestone
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('freelancer_id')->constrained('users');

            $table->decimal('funded_amount', 12, 2)->default(0.00); // Amount held in TND
            $table->decimal('platform_fee', 12, 2)->default(0.00);  // Platform's commission (TND)
            $table->decimal('freelancer_payout', 12, 2)->default(0.00); // Amount freelancer receives after fee

            $table->string('status')->default('unfunded'); // EscrowStatus enum
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
