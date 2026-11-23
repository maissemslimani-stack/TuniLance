<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClientPendingVerification extends Notification
{
    use Queueable;

    public function __construct(public readonly User $client) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'client_pending_verification',
            'title'   => 'New Client Needs Verification 🆕',
            'message' => "\"{$this->client->name}\" ({$this->client->email}) just registered and is awaiting identity verification.",
            'url'     => '/moderator/verification',
            'icon'    => '🆕',
        ];
    }
}
