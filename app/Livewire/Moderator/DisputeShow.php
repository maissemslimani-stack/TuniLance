<?php

namespace App\Livewire\Moderator;

use App\Actions\Dispute\ResolveDisputeAction;
use App\Models\Dispute;
use Livewire\Component;

class DisputeShow extends Component
{
    public Dispute $dispute;
    public string $outcome = 'release_to_freelancer';
    public int $freelancerPercent = 50;
    public string $notes = '';

    public function mount(Dispute $dispute): void
    {
        abort_unless($dispute->moderator_id === auth()->id(), 403, 'Assign this case to yourself first.');
        $this->dispute = $dispute->load(['contract.project', 'contract.client', 'contract.freelancer', 'milestone']);
    }

    public function resolve(): void
    {
        $this->validate([
            'outcome' => ['required', 'in:release_to_freelancer,refund_to_client,split'],
            'notes' => ['required', 'string', 'min:10'],
            'freelancerPercent' => ['required_if:outcome,split', 'integer', 'min:0', 'max:100'],
        ]);

        app(ResolveDisputeAction::class)->execute(
            $this->dispute, auth()->user(), $this->outcome, $this->notes,
            $this->outcome === 'split' ? $this->freelancerPercent : null
        );

        session()->flash('success', 'Dispute resolved.');
        $this->redirect(route('moderator.disputes.index'), navigate: false);
    }

    public function render()
    {
        $room = \App\Models\ChatRoom::where('contract_id', $this->dispute->contract_id)->first();
        $messages = $room?->messages()->with('sender')->get() ?? collect();

        return view('livewire.moderator.dispute-show', [
            'messages' => $messages,
        ]);
    }
}