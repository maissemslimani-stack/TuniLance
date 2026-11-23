<?php

namespace App\Livewire\Moderator;

use App\Models\Dispute;
use Livewire\Component;

class DisputeInbox extends Component
{
    public function assignToMe(int $disputeId): void
    {
        Dispute::findOrFail($disputeId)->update([
            'moderator_id' => auth()->id(),
            'status' => 'under_review',
        ]);
    }

    public function render()
    {
        $open = Dispute::whereIn('status', ['open'])
            ->with(['contract.project', 'reportedBy'])
            ->latest()
            ->get();

        $mine = Dispute::where('moderator_id', auth()->id())
            ->where('status', 'under_review')
            ->with(['contract.project', 'reportedBy'])
            ->latest()
            ->get();

        return view('livewire.moderator.dispute-inbox', compact('open', 'mine'));
    }
}