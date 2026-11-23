<?php

namespace App\Livewire\Client;

use App\Actions\Contract\AcceptSubmissionAction;
use App\Actions\Escrow\FundMilestoneAction;
use App\Actions\Escrow\ReleaseMilestoneAction;
use App\Actions\Escrow\RequestChangesAction;
use App\Enums\ContractStatus;
use App\Enums\MilestoneStatus;
use App\Enums\SubmissionStatus;
use App\Models\Contract;
use App\Models\Submission;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ProjectsIndex extends Component
{
    public string $tab = 'submissions'; // submissions | active | saved

    public ?Contract $activeContract = null;

    public bool $showRequestChangesModal = false;
    public string $changesFeedback = '';
    public string $newDueDate = '';

    public function mount(): void
    {
        $this->activeContract = $this->clientContracts()->first();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function selectContract(int $contractId): void
{
    $this->activeContract = ($this->activeContract?->id === $contractId)
        ? null
        : $this->clientContracts()->firstWhere('id', $contractId);
}

    private function clientContracts()
    {
        return Contract::where('client_id', auth()->id())
            ->where('status', ContractStatus::Active)
            ->with('project')
            ->get();
    }

    private function currentMilestone()
    {
        return $this->activeContract?->milestones()
            ->where('status', '!=', MilestoneStatus::Approved)
            ->orderBy('sort_order')
            ->first();
    }

    // ─── Submissions ─────────────────────────────────────────────────────

    public function acceptSubmission(int $submissionId): void
    {
        $submission = Submission::whereHas('project', fn ($q) => $q->where('client_id', auth()->id()))
            ->findOrFail($submissionId);

        app(AcceptSubmissionAction::class)->execute($submission->project, $submission);

        session()->flash('success', 'Submission accepted — contract created.');
        $this->setTab('active');
    }

    public function rejectSubmission(int $submissionId): void
    {
        Submission::whereHas('project', fn ($q) => $q->where('client_id', auth()->id()))
            ->findOrFail($submissionId)
            ->update(['status' => SubmissionStatus::Rejected]);
    }

    public function unsaveTalent(int $freelancerId): void
    {
        auth()->user()->savedTalents()->detach($freelancerId);
    }

    // ─── Deliverable Locker (Current Projects tab) ─────────────────────────

    public function fundMilestone(): void
    {
        app(FundMilestoneAction::class)->execute($this->currentMilestone());
        session()->flash('success', 'Milestone funded — freelancer can begin work.');
    }

    public function releaseFunds(): void
    {
        $milestone = $this->currentMilestone();
        app(ReleaseMilestoneAction::class)->execute($milestone->escrow);
        session()->flash('success', 'Funds released to freelancer.');
    }

    public function submitRequestChanges(): void
    {
        $this->validate([
            'changesFeedback' => ['required', 'string', 'min:10'],
            'newDueDate' => ['required', 'date', 'after:today'],
        ]);

        $milestone = $this->currentMilestone();

        app(RequestChangesAction::class)->execute(
            $milestone->latestDeliverable,
            $this->changesFeedback,
            Carbon::parse($this->newDueDate)
        );

        $this->showRequestChangesModal = false;
        $this->reset(['changesFeedback', 'newDueDate']);
        session()->flash('success', 'Change request sent to freelancer.');
    }

    public function render()
    {
        $milestone = $this->currentMilestone();

        $submissions = Submission::whereHas('project', fn ($q) => $q->where('client_id', auth()->id()))
            ->where('status', SubmissionStatus::Submitted)
            ->with(['project', 'freelancer'])
            ->latest()
            ->get();
            $contracts = $this->clientContracts();


$contracts = $this->clientContracts();


        return view('livewire.client.projects-index', [
            'submissions' => $submissions,
            'contracts' => $this->clientContracts(),
            'milestone' => $milestone,
            'deliverable' => $milestone?->latestDeliverable,
            'rejectionHistory' => $milestone?->deliverables()->where('status', 'rejected')->get() ?? collect(),
            'savedTalents' => auth()->user()->savedTalents,
        ]);
    }
}