<?php

namespace App\Livewire\Client;

use App\Models\Category;
use Livewire\Component;

class ProfileIndex extends Component
{
    public string $name = '';
    public array $categoryIds = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->categoryIds = $user->categories()->pluck('categories.id')->toArray();
    }

    public function saveProfile(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255']]);

        $user = auth()->user();
        $user->update(['name' => $this->name]);
        $user->categories()->sync($this->categoryIds);

        session()->flash('success', 'Profile updated.');

        $this->redirect(route('client.profile.show'), navigate: false);
    }

    public function render()
    {
        return view('livewire.client.profile-index', [
            'allCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }
}