<?php

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\ClientPendingVerification;
use App\Services\ResumeParserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('components.auth-split-layout-reversed', ['title' => 'Create Account · TuniLance'])]
class Register extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public string $role = 'freelancer';

    // Step 1
    public string $name = '';
    public string $email = '';
    public string $phone_number = '';

    // Step 2
    public array $categoryIds = [];
    public array $skillIds = [];
    public string $bio = '';

    // Step 3
    public string $password = '';
    public string $password_confirmation = '';

    // CV upload
    public $cv = null;
    public ?string $parseMessage = null;

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Fires automatically the moment a PDF is selected (Livewire calls
     * updated{PropertyName} hooks on change). Parses the CV and fills
     * whatever it can into the visible step 1 and step 2 fields.
     */
    public function updatedCv(): void
    {
        $this->validate(['cv' => ['required', 'file', 'mimes:pdf', 'max:5120']]);

        $result = app(ResumeParserService::class)->parse($this->cv->getRealPath());

        $filled = [];

        if ($result['email'] && $this->email === '') {
            $this->email = $result['email'];
            $filled[] = 'email';
        }

        if ($result['phone'] && $this->phone_number === '') {
            $this->phone_number = $result['phone'];
            $filled[] = 'phone';
        }

        if (! empty($result['skill_ids'])) {
            $this->skillIds = array_unique(array_merge($this->skillIds, $result['skill_ids']));
            $filled[] = count($result['skill_ids']).' skills';
        }

        if ($result['bio'] && $this->bio === '') {
            $this->bio = $result['bio'];
            $filled[] = 'bio';
        }

        $this->parseMessage = $filled
            ? 'CV analyzed — filled: '.implode(', ', $filled).'. Review and edit below.'
            : 'CV read successfully, but nothing could be auto-filled. Please fill the form manually.';
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));
        $this->step++;
    }

    public function previousStep(): void
    {
        $this->step--;
    }

    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'phone_number' => ['nullable', 'string', 'max:30'],
            ],
            2 => [
                'categoryIds' => ['nullable', 'array'],
                'skillIds' => ['nullable', 'array'],
                'bio' => ['nullable', 'string', 'max:1000'],
            ],
            default => [],
        };
    }

    public function register()
{
    $this->validate([
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $this->validate($this->rulesForStep(1));

    $user = User::create([
        'name' => $this->name,
        'email' => $this->email,
        'phone_number' => $this->phone_number ?: null,
        'bio' => $this->bio ?: null,
        'password' => Hash::make($this->password),
        'role' => $this->role,
    ]);

    $user->wallet()->create(['balance' => 0.00, 'held_balance' => 0.00]);

    if ($this->skillIds) {
        $user->skills()->sync(Skill::whereIn('id', $this->skillIds)->pluck('id'));
    }

    if ($this->categoryIds) {
        $user->categories()->sync(Category::whereIn('id', $this->categoryIds)->pluck('id'));
    }

    event(new Registered($user));
    Auth::login($user);

    if ($user->isClient()) {
        User::where('role', UserRole::Moderator)->get()
            ->each(fn ($mod) => $mod->notify(new ClientPendingVerification($user)));

        $this->redirect(route('talents.browse'), navigate: false);
        return;
    }

    $this->redirect(route('projects.browse'), navigate: false);
}
    public function render()
    {
        return view('livewire.auth.register', [
            'allSkills' => Skill::orderBy('name')->get(),
            'allCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }
}