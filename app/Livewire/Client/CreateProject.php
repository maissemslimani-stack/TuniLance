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

class CreateProject extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $skillIds = [];
    public ?string $experienceLevel = null;
    public ?string $duration = null;
    public ?float $budgetMin = null;
    public ?float $budgetMax = null;
    public ?string $deadline = null;
    public array $attachments = [];

    /** Each row: ['title' => '', 'amount' => '', 'due_date' => ''] */
    public array $milestones = [
        ['title' => '', 'amount' => '', 'due_date' => ''],
    ];

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
            'skillIds' => ['nullable', 'array'],
            'budgetMin' => ['nullable', 'numeric', 'min:0'],
            'budgetMax' => ['nullable', 'numeric', 'gte:budgetMin'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.title' => ['required', 'string', 'max:255'],
            'milestones.*.amount' => ['required', 'numeric', 'min:1'],
            'milestones.*.due_date' => ['nullable', 'date'],
        ]);

        $project = DB::transaction(function () {
            $project = Project::create([
                'client_id' => auth()->id(),
                'category_id' => $this->categoryId,
                'title' => $this->title,
                'description' => $this->description,
                'budget_min' => $this->budgetMin,
                'budget_max' => $this->budgetMax,
                'status' => ProjectStatus::Open,
                'deadline' => $this->deadline,
                'experience_level' => $this->experienceLevel,
                'duration' => $this->duration,
                'published_at' => now(),
            ]);

            $project->skills()->sync($this->skillIds);

            foreach ($this->milestones as $index => $row) {
                $project->milestoneTemplates()->create([
                    'title' => $row['title'],
                    'amount' => $row['amount'],
                    'due_date' => $row['due_date'] ?: null,
                    'sort_order' => $index,
                ]);
            }

            foreach ($this->attachments as $file) {
                $project->attachments()->create([
                    'file_path' => $file->store('projects/attachments/'.$project->id, 'public'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);
            }

            return $project;
        });

        session()->flash('success', 'Project posted successfully.');

        $this->redirect(route('projects.client.show', $project), navigate: false);
    }

    public function render()
    {
        return view('livewire.client.create-project', [
            'categories' => Category::whereNull('parent_id')->orderBy('name')->get(),
            'allSkills' => Skill::orderBy('name')->get(),
            'experienceLevels' => ExperienceLevel::cases(),
            'durations' => ProjectDuration::cases(),
        ]);
    }
}