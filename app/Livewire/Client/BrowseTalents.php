<?php

namespace App\Livewire\Client;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseTalents extends Component
{
    use WithPagination;

    public array $categoryIds = [];
    public array $skillIds = [];
    public string $sortBy = 'top_rated'; // top_rated | most_experienced

    public bool $showInviteModal = false;
    public ?int $invitingFreelancerId = null;
    public ?int $selectedProjectId = null;
    public string $inviteMessage = '';
    public ?int $projectId = null;


    public function updating($property): void
    {
        if (in_array($property, ['categoryIds', 'skillIds', 'sortBy'])) {
            $this->resetPage();
        }
    }

    public function openInviteModal(int $freelancerId): void
    {
        $this->invitingFreelancerId = $freelancerId;
        $this->selectedProjectId = null;
        $this->inviteMessage = '';
        $this->showInviteModal = true;
    }

    public function sendInvitation(): void
    {
        $this->validate(['selectedProjectId' => ['required', 'exists:projects,id']]);

        $project = Project::where('client_id', auth()->id())->findOrFail($this->selectedProjectId);

        Invitation::updateOrCreate(
            ['project_id' => $project->id, 'freelancer_id' => $this->invitingFreelancerId],
            ['client_id' => auth()->id(), 'message' => $this->inviteMessage, 'status' => 'pending']
        );

        $this->showInviteModal = false;
        session()->flash('success', 'Invitation sent.');
    }

    public function render()
    {
        $freelancers = User::query()
            ->where('role', UserRole::Freelancer)
            ->with(['skills', 'categories'])
            ->withAvg('reviewsReceived', 'rating')
            ->withCount('reviewsReceived')
            ->when($this->categoryIds, fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $this->categoryIds)))
            ->when($this->skillIds, fn ($q) => $q->whereHas('skills', fn ($s) => $s->whereIn('skills.id', $this->skillIds)))
           ->when($this->sortBy === 'top_rated', fn ($q) => $q->orderByDesc('reviews_received_avg_rating'))
->when($this->sortBy === 'most_experienced', fn ($q) => $q->orderByDesc('years_experience'))
->when($this->sortBy === 'recommended' && $this->projectId, function ($q) {
    $project = \App\Models\Project::with('skills')->find($this->projectId);
    $skillIds = $project?->skills->pluck('id')->toArray() ?? [];
    $categoryId = $project?->category_id;

    $q->withCount(['skills as matching_skills_count' => fn ($s) => $s->whereIn('skills.id', $skillIds)])
      ->withCount(['categories as matches_category' => fn ($c) => $c->where('categories.id', $categoryId)])
      ->orderByDesc('matching_skills_count')
      ->orderByDesc('matches_category');
})
            ->paginate(10);

        return view('livewire.client.browse-talents', [
            'freelancers' => $freelancers,
            'allCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
            'allSkills' => Skill::orderBy('name')->get(),
            'myOpenProjects' => Project::where('client_id', auth()->id())->where('status', ProjectStatus::Open)->get(),
            'projectId' => $this->projectId,
        ]);
    }
    public function toggleSaveTalent(int $freelancerId): void
{
    $client = auth()->user();

    if ($client->savedTalents()->where('freelancer_id', $freelancerId)->exists()) {
        $client->savedTalents()->detach($freelancerId);
    } else {
        $client->savedTalents()->attach($freelancerId);
    }
}

public function mount(): void
{
    $this->projectId = request()->integer('project') ?: null;
    if ($this->projectId) {
        $this->sortBy = 'recommended';
    }
}
}