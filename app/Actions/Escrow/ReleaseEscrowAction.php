<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Models\Escrow;
use App\Notifications\FundsReleased;
use Illuminate\Support\Facades\DB;

class ReleaseEscrowAction
{
    /**
     * Release escrow funds to the freelancer's wallet after client approval.
     * Platform fee is deducted before crediting the freelancer.
     *
     * Flow: Escrow (released) → Freelancer Wallet (balance++) + Platform Fee deducted
     *
     * Note: We use milestone->amount as the authoritative source when funded_amount
     * is still 0 (e.g. when the explicit "Fund" step was skipped in demos/tests).
     */
    public function execute(Escrow $escrow, float $platformFeeRate = 0.05): Escrow
    {
        return DB::transaction(function () use ($escrow, $platformFeeRate) {

            $escrow->loadMissing(['milestone', 'contract.freelancer.wallet', 'contract.client.wallet']);

            // Use milestone->amount as fallback when funded_amount was never set
            $grossAmount = (float) $escrow->funded_amount > 0
                ? (float) $escrow->funded_amount
                : (float) $escrow->milestone->amount;

            $platformFee      = round($grossAmount * $platformFeeRate, 2);
            $freelancerPayout = $grossAmount - $platformFee;

            $freelancerWallet = $escrow->contract->freelancer->wallet->lockForUpdate()->find(
                $escrow->contract->freelancer->wallet->id
            );

            $balanceBefore = $freelancerWallet->balance;

            // Credit freelancer wallet (net of platform fee)
            $freelancerWallet->increment('balance', $freelancerPayout);
            $freelancerWallet->refresh();

            // Credit transaction for freelancer
            $freelancerWallet->transactions()->create([
                'user_id'              => $freelancerWallet->user_id,
                'type'                 => WalletTransactionType::EscrowRelease->value,
                'amount'               => $freelancerPayout,
                'balance_before'       => $balanceBefore,
                'balance_after'        => $freelancerWallet->balance,
                'status'               => WalletTransactionStatus::Completed->value,
                'description'          => "Milestone payment released: {$escrow->milestone->title}",
                'transactionable_id'   => $escrow->id,
                'transactionable_type' => Escrow::class,
            ]);

            // Reduce client's held_balance
            $clientWallet = $escrow->contract->client->wallet;
            $clientWallet->decrement('held_balance', $grossAmount);

            // Update escrow record with actual amounts
            $escrow->update([
                'status'            => EscrowStatus::Released->value,
                'funded_amount'     => $grossAmount,
                'platform_fee'      => $platformFee,
                'freelancer_payout' => $freelancerPayout,
                'released_at'       => now(),
            ]);

            // Mark milestone as approved
            $escrow->milestone->update(['status' => MilestoneStatus::Approved->value]);

            $escrow = $escrow->fresh(['milestone.contract.project']);

            // Notify freelancer that funds have been released
            $escrow->contract->freelancer->notify(new FundsReleased($escrow));

            return $escrow;
        });
    }
}
