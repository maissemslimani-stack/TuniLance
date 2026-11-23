<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Models\Escrow;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FundEscrowAction
{
    /**
     * Debit the client's wallet and move funds into the milestone's escrow.
     * Uses a DB transaction + pessimistic locking to prevent race conditions.
     *
     * Flow: Client Wallet (balance--) → Escrow (funded_amount++)
     */
    public function execute(Escrow $escrow): Escrow
    {
        return DB::transaction(function () use ($escrow) {

            $escrow->loadMissing(['milestone', 'contract.client.wallet']);

            $wallet = $escrow->contract->client->wallet;
            $amount = $escrow->milestone->amount;

            // Lock the wallet row to prevent concurrent modifications
            $wallet = $wallet->lockForUpdate()->find($wallet->id);

            if (!$wallet->hasSufficientBalance($amount)) {
                throw new RuntimeException("Insufficient wallet balance to fund this milestone.");
            }

            $balanceBefore = $wallet->balance;

            // Debit client wallet and increase held amount
            $wallet->decrement('balance', $amount);
            $wallet->increment('held_balance', $amount);
            $wallet->refresh();

            // Record the wallet transaction
            $wallet->transactions()->create([
                'user_id'               => $wallet->user_id,
                'type'                  => WalletTransactionType::EscrowFund->value,
                'amount'                => $amount,
                'balance_before'        => $balanceBefore,
                'balance_after'         => $wallet->balance,
                'status'                => WalletTransactionStatus::Completed->value,
                'description'           => "Funded escrow for milestone: {$escrow->milestone->title}",
                'transactionable_id'    => $escrow->id,
                'transactionable_type'  => Escrow::class,
            ]);

            // Update escrow
            $escrow->update([
                'funded_amount' => $amount,
                'status'        => EscrowStatus::Funded->value,
                'funded_at'     => now(),
            ]);

            // Update milestone status
            $escrow->milestone->update(['status' => MilestoneStatus::Funded->value]);

            return $escrow->fresh();
        });
    }
}
