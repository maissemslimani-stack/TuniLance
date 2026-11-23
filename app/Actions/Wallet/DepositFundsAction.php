<?php

namespace App\Actions\Wallet;

use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepositFundsAction
{
    public function execute(User $user, float $amount): \App\Models\Wallet
    {
        return DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallet()->firstOrCreate([]);
            $wallet = $wallet->lockForUpdate()->find($wallet->id);

            $balanceBefore = $wallet->balance;
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => WalletTransactionType::Deposit,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => WalletTransactionStatus::Completed,
                'description' => 'Wallet deposit',
            ]);

            return $wallet;
        });
    }
}