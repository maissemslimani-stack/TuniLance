<?php

namespace App\Livewire\Client;

use App\Enums\ProjectStatus;
use App\Models\User;
use Livewire\Component;

class ProfileShow extends Component
{
    public User $profileUser;

    public function mount(?User $user = null): void
    {
        $this->profileUser = ($user ?? auth()->user())->load('categories');
    }

    public function getIsOwnProfileProperty(): bool
    {
        return $this->profileUser->id === auth()->id();
    }

    public function render()
    {
        $projectsPosted = $this->profileUser->projects()->count();

        $totalSpent = $this->profileUser->wallet?->transactions()
            ->where('type', 'escrow_fund')
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        return view('livewire.client.profile-show', [
            'projectsPosted' => $projectsPosted,
            'totalSpent' => $totalSpent,
        ]);
    }
}