<?php

namespace App\Livewire\Client;

use App\Enums\ExperienceLevel;
use App\Enums\ProjectDuration;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProject extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $title = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $skillIds = [];
    public ?string $experienceLevel = null;
    public ?string $duration = null;
    public ?float $budgetMin = null;
    public ?float $budgetMax = null;
    public ?string $deadline = null;

    public array $milestones = [];

    public function mount(Project $project): void
    {
        abort_unless($project->client_id === auth()->id(), 403);
        abort_unless($project->status === ProjectStatus::Open, 403, 'This project can no longer be edited — a freelancer has already been hired.');

        $this->project = $project;
        $this->title = $project->title;
        $this->description = $project->description;
        $this->categoryId = $project->category_id;
        $this->skillIds = $project->skills()->pluck('skills.id')->toArray();
        $this->experienceLevel = $project->experience_level?->value;
        $this->duration = $project->duration?->value;
        $this->budgetMin = $project->budget_min;
        $this->budgetMax = $project->budget_max;
        $this->deadline = $project->deadline?->format('Y-m-d');

        $this->milestones = $project->milestoneTemplates->map(fn ($m) => [
            'title' => $m->title,
            'amount' => $m->amount,
            'due_date' => $m->due_date?->format('Y-m-d'),
        ])->toArray();

        if (empty($this->milestones)) {
            $this->milestones = [['title' => '', 'amount' => '', 'due_date' => '']];
        }
    }

    public function addMilestoneRow(): void
    {
        $this->milestones[] = ['title' => '', 'amount' => '', 'due_date' => ''];
    }

    public function removeMilestoneRow(int $index): void
    {
        unset($this->milestones[$index]);
        $this->milestones = array_values($this->milestones);
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'categoryId' => ['required', 'exists:categories,id'],
            'budgetMin' => ['nullable', 'numeric', 'min:0'],
            'budgetMax' => ['nullable', 'numeric', 'gte:budgetMin'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.title' => ['required', 'string', 'max:255'],
            'milestones.*.amount' => ['required', 'numeric', 'min:1'],
            'milestones.*.due_date' => ['nullable', 'date'],
        ]);

        DB::transaction(function () {
            $this->project->update([
                'title' => $this->title,
                'description' => $this->description,
                'category_id' => $this->categoryId,
                'budget_min' => $this->budgetMin,
                'budget_max' => $this->budgetMax,
                'experience_level' => $this->experienceLevel,
                'duration' => $this->duration,
                'deadline' => $this->deadline,
            ]);

            $this->project->skills()->sync($this->skillIds);

            // No contract exists yet at this point (enforced in mount), so it's
            // safe to fully replace the milestone templates rather than diff them.
            $this->project->milestoneTemplates()->delete();
            foreach ($this->milestones as $index => $row) {
                $this->project->milestoneTemplates()->create([
                    'title' => $row['title'],
                    'amount' => $row['amount'],
                    'due_date' => $row['due_date'] ?: null,
                    'sort_order' => $index,
                ]);
            }
        });

        session()->flash('success', 'Project updated.');
        $this->redirect(route('projects.client.show', $this->project), navigate: false);
    }

    public function render()
    {
        return view('livewire.client.edit-project', [
            'categories' => Category::whereNull('parent_id')->orderBy('name')->get(),
            'allSkills' => Skill::orderBy('name')->get(),
            'experienceLevels' => ExperienceLevel::cases(),
            'durations' => ProjectDuration::cases(),
        ]);
    }
}