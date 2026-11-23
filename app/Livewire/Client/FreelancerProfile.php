<?php

namespace App\Livewire\Client;

use App\Actions\Chat\FindOrCreateRoomAction;
use App\Enums\ContractStatus;
use App\Enums\ProjectStatus;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Livewire\Component;

class FreelancerProfile extends Component
{
    public User $freelancer;

    public bool $showInviteModal = false;
    public ?int $selectedProjectId = null;
    public string $inviteMessage = '';
    public bool $hideInvite = false;

public function mount(User $user): void
{
    $this->freelancer = $user->load(['skills', 'portfolioItems']);
    $this->hideInvite = request()->query('context') === 'submission';
}

    

    public function contact(): void
    {
        $room = app(FindOrCreateRoomAction::class)->execute(auth()->user(), $this->freelancer);
        $this->redirect(route('messages.show', $room), navigate: false);
    }

    public function openInviteModal(): void
    {
        $this->selectedProjectId = null;
        $this->inviteMessage = '';
        $this->showInviteModal = true;
    }

    public function sendInvitation(): void
    {
        $this->validate(['selectedProjectId' => ['required', 'exists:projects,id']]);

        $project = Project::where('client_id', auth()->id())->findOrFail($this->selectedProjectId);

        Invitation::updateOrCreate(
            ['project_id' => $project->id, 'freelancer_id' => $this->freelancer->id],
            ['client_id' => auth()->id(), 'message' => $this->inviteMessage, 'status' => 'pending']
        );

        $this->showInviteModal = false;
        session()->flash('success', 'Invitation sent.');
    }

    public function render()
    {
        $projectsCompleted = $this->freelancer->freelancerContracts()
            ->whereIn('status', [
                ContractStatus::Completed,
                ContractStatus::FreelancerChanged,
                ContractStatus::CancelledRefunded,
                ContractStatus::DroppedByFreelancer,
            ])
            ->count();

        $totalEarned = $this->freelancer->wallet?->transactions()
            ->where('type', 'escrow_release')
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        $reviews = Review::where('reviewee_id', $this->freelancer->id)->get();

        return view('livewire.client.freelancer-profile', [
            'projectsCompleted' => $projectsCompleted,
            'totalEarned' => $totalEarned,
            'averageRating' => $reviews->avg('rating'),
            'reviewsCount' => $reviews->count(),
            'myOpenProjects' => Project::where('client_id', auth()->id())->where('status', ProjectStatus::Open)->get(),
        ]);
    }
}