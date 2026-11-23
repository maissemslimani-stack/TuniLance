<?php

namespace App\Livewire\Client;

use App\Models\Project;
use Livewire\Component;

class ProjectShow extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        abort_unless($project->client_id === auth()->id(), 403);
       $this->project = $project->load(['category', 'skills', 'milestoneTemplates', 'attachments', 'submissions']);
    }

    public function render()
    {
        return view('livewire.client.project-show', [
            'submissionsCount' => $this->project->submissions()->count(),
        ]);
    }
}