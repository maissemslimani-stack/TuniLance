<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One wallet per user — balance stored in TND (Tunisian Dinar)
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0.00);     // Available balance (TND)
            $table->decimal('held_balance', 12, 2)->default(0.00); // Amount currently locked in active escrows (TND)
            $table->timestamps();
        });

        // Immutable audit ledger — every balance change creates a record here
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');   // WalletTransactionType enum value
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2); // Snapshot of balance before this transaction
            $table->decimal('balance_after', 12, 2);  // Snapshot of balance after this transaction
            $table->string('status')->default('completed'); // WalletTransactionStatus enum value
            $table->string('reference')->nullable(); // External payment gateway reference or internal ID
            $table->text('description')->nullable(); // Human-readable reason
            $table->nullableMorphs('transactionable'); // Polymorphic: links to escrow, proposal, etc.
            $table->timestamps();

            $table->index(['wallet_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
