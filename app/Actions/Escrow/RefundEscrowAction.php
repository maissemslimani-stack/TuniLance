<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Models\Escrow;
use Illuminate\Support\Facades\DB;

class RefundEscrowAction
{
    /**
     * Refund escrow funds back to the client's wallet after a dispute is
     * resolved in their favor. Mirrors ReleaseEscrowAction's structure but
     * moves money the opposite direction.
     */
    public function execute(Escrow $escrow): Escrow
    {
        return DB::transaction(function () use ($escrow) {
            $escrow->loadMissing(['milestone', 'contract.client.wallet']);

            $amount = (float) $escrow->funded_amount;
            $clientWallet = $escrow->contract->client->wallet;
            $clientWallet = $clientWallet->lockForUpdate()->find($clientWallet->id);

            $balanceBefore = $clientWallet->balance;

            $clientWallet->increment('balance', $amount);
            $clientWallet->decrement('held_balance', $amount);
            $clientWallet->refresh();

            $clientWallet->transactions()->create([
                'user_id' => $clientWallet->user_id,
                'type' => WalletTransactionType::EscrowRefund->value,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $clientWallet->balance,
                'status' => WalletTransactionStatus::Completed->value,
                'description' => "Dispute refund: {$escrow->milestone->title}",
                'transactionable_id' => $escrow->id,
                'transactionable_type' => Escrow::class,
            ]);

            $escrow->update([
                'status' => EscrowStatus::Refunded->value,
                'refunded_at' => now(),
            ]);

            $escrow->milestone->update(['status' => MilestoneStatus::Refunded->value]);

            return $escrow->fresh();
        });
    }
}