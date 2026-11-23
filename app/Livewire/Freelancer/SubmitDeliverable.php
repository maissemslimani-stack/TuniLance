<?php

namespace App\Livewire\Freelancer;

use App\Enums\DeliverableStatus;
use App\Enums\MilestoneStatus;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Notifications\MilestoneSubmitted;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitDeliverable extends Component
{
    use WithFileUploads;

    public Milestone $milestone;
    public string $notes = '';
    public string $externalLink = '';
    public array $files = [];
    public bool $requestRelease = true;

    public function mount(Milestone $milestone): void
    {
        $milestone->load(['contract.project', 'contract.client']);

        abort_unless($milestone->contract->freelancer_id === auth()->id(), 403);

        $this->milestone = $milestone;
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function submit(): void
    {
        $this->validate([
            'notes' => ['required', 'string', 'min:10'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:51200'], // 50MB in KB
        ]);

        $deliverable = Deliverable::create([
            'milestone_id' => $this->milestone->id,
            'freelancer_id' => auth()->id(),
            'notes' => $this->notes,
            'external_link' => $this->externalLink ?: null,
            'status' => DeliverableStatus::Submitted,
        ]);

        foreach ($this->files as $file) {
            $path = $file->store('deliverables/'.$this->milestone->id, 'public');

            $deliverable->files()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        if ($this->requestRelease) {
            $this->milestone->update(['status' => MilestoneStatus::InProgress]);
        }

        // Notify the client that a deliverable was submitted for review
        $deliverable->load(['milestone.contract.project', 'freelancer']);
        $this->milestone->contract->client->notify(new MilestoneSubmitted($deliverable));

        session()->flash('success', 'Your work has been submitted for client review.');

        $this->redirect(route('projects.mine'), navigate: false);
    }

    public function render()
    {
        return view('livewire.freelancer.submit-deliverable');
    }
}