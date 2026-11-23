<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClientVerified extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'client_verified',
            'title'   => 'Account Verified ✅',
            'message' => 'Your account has been verified by our team. You can now access all client features.',
            'url'     => '/talents',
            'icon'    => '✅',
        ];
    }
}
