<?php

namespace App\Livewire\Freelancer;

use App\Enums\ContractStatus;
use App\Enums\SubmissionStatus;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectsIndex extends Component
{
    use WithPagination;

    public string $tab = 'active'; // active | pending | past | saved

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }
    public function toggleSave(int $projectId): void
    {
    $user = auth()->user();

    if ($user->savedProjects()->where('project_id', $projectId)->exists()) {
        $user->savedProjects()->detach($projectId);
    } else {
        $user->savedProjects()->attach($projectId);
    }
}
    public function render()
    {
        $freelancer = auth()->user();

        $activeContracts = $freelancer->freelancerContracts()
            ->where('status', ContractStatus::Active)
            ->with(['project', 'client', 'milestones'])
            ->get();

        $pendingSubmissions = $freelancer->submissions()
            ->where('status', SubmissionStatus::Submitted)
            ->with('project')
            ->latest()
            ->paginate(10, pageName: 'pending-page');

        // "Past Contracts" — any terminal state, whatever the reason it ended
        $pastContracts = $freelancer->freelancerContracts()
            ->whereIn('status', [
                ContractStatus::Completed,
                ContractStatus::FreelancerChanged,
                ContractStatus::CancelledRefunded,
                ContractStatus::DroppedByFreelancer,
            ])
            ->with(['project', 'client'])
            ->latest('completed_at')
            ->paginate(10, pageName: 'past-page');

        $savedProjects = $freelancer->savedProjects()
            ->with('client', 'category')
            ->paginate(10, pageName: 'saved-page');
        
        $invitations = $freelancer->invitations()
            ->where('status', 'pending')
            ->with('project', 'client')
            ->latest()
            ->paginate(10, pageName: 'invite-page');

        // Earnings overview (right sidebar) — sums released escrow across active contracts
        // Earnings overview (right sidebar)
    $totalEscrow = $activeContracts->sum('total_amount');
    $pendingRelease = $activeContracts->flatMap->milestones
        ->where('status', \App\Enums\MilestoneStatus::InProgress)->sum('amount'); // freelancer submitted, awaiting client approval
    $upcoming = $activeContracts->flatMap->milestones
        ->whereIn('status', [\App\Enums\MilestoneStatus::Pending, \App\Enums\MilestoneStatus::Funded])->sum('amount'); // not yet started or funded but not worked on

        return view('livewire.freelancer.projects-index', [
            'activeContracts' => $activeContracts,
            'pendingSubmissions' => $pendingSubmissions,
            'pastContracts' => $pastContracts,
            'savedProjects' => $savedProjects,
            'totalEscrow' => $totalEscrow,
            'pendingRelease' => $pendingRelease,
            'upcoming' => $upcoming,
            'invitations' => $invitations,
        ]);
    }
    public function acceptInvitation(int $invitationId): void
{
    $invitation = \App\Models\Invitation::where('freelancer_id', auth()->id())->findOrFail($invitationId);
    app(\App\Actions\Contract\AcceptInvitationAction::class)->execute($invitation);
    session()->flash('success', 'Invitation accepted — contract created.');
}

public function declineInvitation(int $invitationId): void
{
    \App\Models\Invitation::where('freelancer_id', auth()->id())->findOrFail($invitationId)
        ->update(['status' => 'declined']);
}
}