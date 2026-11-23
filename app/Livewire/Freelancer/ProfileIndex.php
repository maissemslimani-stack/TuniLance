<?php

namespace App\Livewire\Freelancer;

use App\Models\Category;
use App\Models\PortfolioItem;
use App\Models\Skill;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileIndex extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $headline = '';
    public string $bio = '';
    public string $phoneNumber = '';
    public ?int $yearsExperience = null;
    public array $skillIds = [];
    public array $categoryIds = [];

    public bool $showPortfolioModal = false;
    public ?int $editingItemId = null;
    public string $itemTitle = '';
    public string $itemDescription = '';
    public string $itemProjectLink = '';
    public $thumbnail = null;
    public array $newAttachments = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->headline = $user->headline ?? '';
        $this->bio = $user->bio ?? '';
        $this->phoneNumber = $user->phone_number ?? '';
        $this->yearsExperience = $user->years_experience;
        $this->skillIds = $user->skills()->pluck('skills.id')->toArray();
        $this->categoryIds = $user->categories()->pluck('categories.id')->toArray();
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phoneNumber' => ['nullable', 'string', 'max:30'],
            'yearsExperience' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $user = auth()->user();

        $user->update([
            'name' => $this->name,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'phone_number' => $this->phoneNumber ?: null,
            'years_experience' => $this->yearsExperience,
        ]);

        $user->skills()->sync($this->skillIds);
        $user->categories()->sync($this->categoryIds);

        session()->flash('success', 'Profile updated.');

        $this->redirect(route('profile.show'), navigate: false);
    }

    public function openPortfolioModal(?int $itemId = null): void
    {
        $this->resetErrorBag();
        $this->reset(['itemTitle', 'itemDescription', 'itemProjectLink', 'thumbnail', 'newAttachments']);

        $this->editingItemId = $itemId;

        if ($itemId) {
            $item = PortfolioItem::where('user_id', auth()->id())->findOrFail($itemId);
            $this->itemTitle = $item->title;
            $this->itemDescription = $item->description ?? '';
            $this->itemProjectLink = $item->project_link ?? '';
        }

        $this->showPortfolioModal = true;
    }

    public function savePortfolioItem(): void
    {
        $this->validate([
            'itemTitle' => ['required', 'string', 'max:255'],
            'itemDescription' => ['nullable', 'string'],
            'itemProjectLink' => ['nullable', 'url'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'newAttachments.*' => ['file', 'max:10240'],
        ]);

        $item = $this->editingItemId
            ? PortfolioItem::where('user_id', auth()->id())->findOrFail($this->editingItemId)
            : new PortfolioItem(['user_id' => auth()->id()]);

        $item->title = $this->itemTitle;
        $item->description = $this->itemDescription;
        $item->project_link = $this->itemProjectLink ?: null;

        if ($this->thumbnail) {
            $item->thumbnail_url = $this->thumbnail->store('portfolio/thumbnails', 'public');
        }

        $item->save();

        foreach ($this->newAttachments as $file) {
            $item->attachments()->create([
                'file_path' => $file->store('portfolio/attachments/'.$item->id, 'public'),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $this->showPortfolioModal = false;
        session()->flash('success', $this->editingItemId ? 'Portfolio item updated.' : 'Portfolio item added.');
    }

    public function deletePortfolioItem(int $itemId): void
    {
        PortfolioItem::where('user_id', auth()->id())->findOrFail($itemId)->delete();
        session()->flash('success', 'Portfolio item removed.');
    }

    public function render()
    {
        return view('livewire.freelancer.profile-index', [
            'portfolioItems' => PortfolioItem::where('user_id', auth()->id())
                ->with('attachments')
                ->orderBy('sort_order')
                ->get(),
            'allSkills' => Skill::orderBy('name')->get(),
            'allCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }
}