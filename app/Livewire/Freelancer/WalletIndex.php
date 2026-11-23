<?php

namespace App\Livewire\Freelancer;

use App\Enums\EscrowStatus;
use App\Enums\WalletTransactionStatus;
use App\Enums\WalletTransactionType;
use App\Models\Escrow;
use Illuminate\Support\Carbon;
use Livewire\Component;

class WalletIndex extends Component
{
    public string $chartRange = '6M'; // '6M' | '1Y'

    public function setChartRange(string $range): void
    {
        $this->chartRange = $range;
    }

    public function render()
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        $totalEarnings = $user->wallet->transactions()
            ->where('type', WalletTransactionType::EscrowRelease)
            ->where('status', WalletTransactionStatus::Completed)
            ->sum('amount');

        $securedProjectsCount = Escrow::where('freelancer_id', $user->id)
            ->where('status', EscrowStatus::Funded)
            ->distinct('contract_id')
            ->count('contract_id');

        // ─── Monthly earnings chart data ───────────────────────────────────
        $months = $this->chartRange === '1Y' ? 12 : 6;
        $chartData = collect(range($months - 1, 0))->map(function ($monthsAgo) use ($user) {
            $month = now()->subMonths($monthsAgo);
            $total = $user->wallet->transactions()
                ->where('type', WalletTransactionType::EscrowRelease)
                ->where('status', WalletTransactionStatus::Completed)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            return ['label' => $month->format('M'), 'total' => (float) $total];
        });
        $maxMonth = max($chartData->pluck('total')->max(), 1); // avoid divide-by-zero

        // ─── Transaction history: real transactions + pending (funded, unreleased) escrows ───
        $completedTransactions = $user->wallet->transactions()
            ->whereIn('type', [WalletTransactionType::EscrowRelease, WalletTransactionType::Deposit, WalletTransactionType::Withdrawal])
            ->with('transactionable.milestone.contract.project')
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'project' => $t->transactionable?->milestone?->contract?->project?->title ?? $t->description,
                'detail' => $t->description,
                'date' => $t->created_at,
                'amount' => $t->amount,
                'is_credit' => $t->type === WalletTransactionType::EscrowRelease,
                'status' => 'Released',
            ]);

        $pendingEscrows = Escrow::where('freelancer_id', $user->id)
            ->where('status', EscrowStatus::Funded) // funded but not yet released = still "in review"
            ->with('milestone.contract.project')
            ->get()
            ->map(fn ($e) => [
                'project' => $e->milestone->contract->project->title,
                'detail' => $e->milestone->title,
                'date' => $e->funded_at,
                'amount' => $e->freelancer_payout,
                'is_credit' => false,
                'status' => 'In Review',
            ]);

        $history = $completedTransactions->concat($pendingEscrows)->sortByDesc('date')->values();

        return view('livewire.freelancer.wallet-index', [
            'wallet' => $wallet,
            'totalEarnings' => $totalEarnings,
            'securedProjectsCount' => $securedProjectsCount,
            'chartData' => $chartData,
            'maxMonth' => $maxMonth,
            'history' => $history,
        ]);
    }
}