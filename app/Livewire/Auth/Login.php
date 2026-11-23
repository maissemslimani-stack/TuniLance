<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.auth-split-layout', ['title' => 'Sign In · TuniLance'])]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in a moment.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();

        $user = Auth::user();

        $target = match (true) {
            $user->isFreelancer() => route('projects.browse'),
            $user->isClient() => route('talents.browse'),
            $user->isModerator() => route('moderator.disputes.index'),
            default => route('projects.browse'),
        };

        $this->redirect($target, navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}