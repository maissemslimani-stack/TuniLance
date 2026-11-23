<?php
// app/Livewire/Freelancer/BrowseJobs.php
//
// What this file does: the Livewire component powering the Browse Jobs page.
// It queries open Projects, applies whatever filters the freelancer has set
// (checkboxes/radio/budget/toggle), and re-renders the results live as those
// filters change — no page reload, no separate "Search" button needed.

namespace App\Livewire\Freelancer;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseJobs extends Component
{
    use WithPagination;

    // Checkboxes → multiple values possible → array
    public array $experienceLevels = [];

    // Radio group → only one value possible at a time → nullable string, NOT array
    public ?string $duration = null;

    public ?float $budgetMin = null;
    public ?float $budgetMax = null;
    public bool $verifiedEscrowOnly = false;
    public string $sortBy = 'newest';

    protected $queryString = ['experienceLevels', 'duration', 'budgetMin', 'budgetMax', 'verifiedEscrowOnly'];

    public function updating($property): void
    {
        if (in_array($property, ['experienceLevels', 'duration', 'budgetMin', 'budgetMax', 'verifiedEscrowOnly'])) {
            $this->resetPage();
        }
    }

    /** Lets the "Remove" / "Clear filters" UI reset the radio selection back to null */
    public function clearDuration(): void
    {
        $this->duration = null;
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
        $projects = Project::query()
            ->open()
            ->visibleTo(auth()->id())
            ->with(['client', 'category'])
            ->experienceLevel($this->experienceLevels)
            ->duration($this->duration ? [$this->duration] : [])
            ->budgetBetween($this->budgetMin, $this->budgetMax)
            ->verifiedEscrowOnly($this->verifiedEscrowOnly)
            ->when($this->sortBy === 'newest', fn ($q) => $q->latest('published_at'))
            ->paginate(10);

        return view('livewire.freelancer.browse-jobs', [
            'projects' => $projects,
            'activeSubmissionsCount' => auth()->user()?->submissions()->count() ?? 0,
        ]);
    }
}