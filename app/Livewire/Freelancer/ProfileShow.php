<?php

namespace App\Livewire\Freelancer;

use App\Enums\ContractStatus;
use App\Models\User;
use Livewire\Component;

class ProfileShow extends Component
{
    public User $profileUser;

    public function mount(?User $user = null): void
    {
        $this->profileUser = $user ?? auth()->user();
    }

    public function getIsOwnProfileProperty(): bool
    {
        return $this->profileUser->id === auth()->id();
    }

    public function render()
    {
        $projectsWorkedCount = $this->profileUser->freelancerContracts()
            ->whereIn('status', [
                ContractStatus::Completed,
                ContractStatus::FreelancerChanged,
                ContractStatus::CancelledRefunded,
                ContractStatus::DroppedByFreelancer,
            ])
            ->count();

        $reviews = \App\Models\Review::where('reviewee_id', $this->profileUser->id)
            ->with('reviewer')
            ->latest()
            ->get();

        $averageRating = $reviews->avg('rating');

        return view('livewire.freelancer.profile-show', [
            'portfolioItems' => $this->profileUser->portfolioItems()->orderBy('sort_order')->get(),
            'projectsWorkedCount' => $projectsWorkedCount,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
        ]);
    }
}