<?php

namespace App\Livewire\Shared;

use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggleOpen(): void
    {
        $this->open = ! $this->open;
    }

    public function markAllRead(): void
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
        }
    }

    public function markRead(string $notificationId): void
    {
        if (auth()->check()) {
            auth()->user()->notifications()->where('id', $notificationId)->first()?->markAsRead();
        }
    }

    public function render()
    {
        $notifications = auth()->check()
            ? auth()->user()->notifications()->latest()->limit(15)->get()
            : collect();

        $unreadCount = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;

        return view('livewire.shared.notification-bell', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}