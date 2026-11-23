<?php

namespace App\Livewire\Client;

use App\Actions\Wallet\DepositFundsAction;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use Livewire\Component;

class WalletIndex extends Component
{
    public string $depositAmount = '';
    public bool $showDepositModal = false;

    public function deposit(): void
    {
        $this->validate(['depositAmount' => ['required', 'numeric', 'min:1', 'max:100000']]);

        app(DepositFundsAction::class)->execute(auth()->user(), (float) $this->depositAmount);

        $this->showDepositModal = false;
        $this->depositAmount = '';
        session()->flash('success', 'Funds deposited.');
    }

    public function render()
    {
        $user = auth()->user();
        $wallet = $user->wallet()->firstOrCreate([]);

        $totalSpent = $wallet->transactions()
            ->where('type', WalletTransactionType::EscrowFund)
            ->where('status', WalletTransactionStatus::Completed)
            ->sum('amount');

        $spendByProject = $user->projects()
            ->whereHas('contract')
            ->with('contract.milestones')
            ->get()
            ->map(fn ($project) => [
                'title' => $project->title,
                'spent' => $project->contract->milestones->where('status', 'approved')->sum('amount'),
            ])
            ->filter(fn ($row) => $row['spent'] > 0)
            ->sortByDesc('spent')
            ->values();

        $history = $wallet->transactions()
            ->whereIn('type', [WalletTransactionType::Deposit, WalletTransactionType::EscrowFund])
            ->latest()
            ->get();

        return view('livewire.client.wallet-index', [
            'wallet' => $wallet,
            'totalSpent' => $totalSpent,
            'spendByProject' => $spendByProject,
            'history' => $history,
        ]);
    }
}