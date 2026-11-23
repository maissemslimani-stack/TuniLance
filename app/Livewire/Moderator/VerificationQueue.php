<?php

namespace App\Livewire\Moderator;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\ClientVerified;
use Livewire\Component;

class VerificationQueue extends Component
{
    public function approve(int $userId): void
    {
        $client = User::where('role', UserRole::Client)->findOrFail($userId);
        $client->update(['is_verified' => true, 'verified_at' => now(), 'verified_by' => auth()->id()]);

        // Notify the client their account is now verified
        $client->notify(new ClientVerified());

        session()->flash('success', 'Client verified.');
    }

    public function reject(int $userId): void
    {
        $client = User::where('role', UserRole::Client)->findOrFail($userId);
        $client->update(['is_verified' => false, 'verified_at' => null, 'verified_by' => null]);
        session()->flash('success', 'Verification rejected.');
    }

    public function render()
    {
        $pending = User::where('role', UserRole::Client)
            ->where('is_verified', false)
            ->latest()
            ->get();

        $verified = User::where('role', UserRole::Client)
            ->where('is_verified', true)
            ->with('verifiedBy')
            ->latest('verified_at')
            ->get();

        return view('livewire.moderator.verification-queue', compact('pending', 'verified'));
    }
}