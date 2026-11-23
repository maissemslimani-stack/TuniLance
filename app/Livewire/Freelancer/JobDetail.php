<?php

namespace App\Livewire\Freelancer;

use App\Models\Project;
use App\Models\Submission;
use Livewire\Component;

class JobDetail extends Component
{
    public Project $project;
    public bool $showApplyModal = false;

    // Apply form fields
    public $bid_amount = '';
    public $cover_letter = '';
    public $delivery_days = '';

    public function mount(Project $project): void
    {
        $this->project = $project->load(['client', 'category', 'attachments', 'skills']);
    }

    public function getHasAppliedProperty(): bool
    {
        return $this->project->submissions()
            ->where('freelancer_id', auth()->id())
            ->exists();
    }

    public function getSubmissionsCountProperty(): int
    {
        return $this->project->submissions()->count();
    }

    public function getAverageBidProperty(): ?float
    {
        return $this->project->submissions()->avg('bid_amount');
    }

    public function openApplyModal(): void
    {
        abort_if($this->hasApplied, 422, 'You already applied to this project.');
        $this->showApplyModal = true;
    }

    public function submitApplication(): void
    {
        $this->validate([
            'bid_amount' => ['required', 'numeric', 'min:1'],
            'cover_letter' => ['required', 'string', 'min:20'],
            'delivery_days' => ['required', 'integer', 'min:1'],
        ]);

        Submission::create([
            'project_id' => $this->project->id,
            'freelancer_id' => auth()->id(),
            'bid_amount' => $this->bid_amount,
            'cover_letter' => $this->cover_letter,
            'delivery_days' => $this->delivery_days,
            'status' => \App\Enums\SubmissionStatus::Submitted,
        ]);

        $this->showApplyModal = false;
        $this->reset(['bid_amount', 'cover_letter', 'delivery_days']);

        session()->flash('success', 'Your application has been submitted.');
    }

    public function render()
    {
        return view('livewire.freelancer.job-detail');
    }
}