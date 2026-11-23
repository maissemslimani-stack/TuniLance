<?php

namespace App\Notifications;

use App\Models\Deliverable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChangesRequested extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Deliverable $deliverable,
        public readonly string $feedback,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $milestone = $this->deliverable->milestone?->title ?? 'a milestone';
        $project   = $this->deliverable->milestone?->contract?->project?->title ?? 'a project';

        return [
            'type'    => 'changes_requested',
            'title'   => 'Changes Requested 🔁',
            'message' => "The client requested changes on \"{$milestone}\" for {$project}: {$this->feedback}",
            'url'     => '/projects/mine',
            'icon'    => '🔁',
        ];
    }
}
