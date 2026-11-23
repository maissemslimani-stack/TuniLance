<?php

namespace App\Notifications;

use App\Models\Deliverable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MilestoneSubmitted extends Notification
{
    use Queueable;

    public function __construct(public readonly Deliverable $deliverable) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $milestone = $this->deliverable->milestone?->title ?? 'a milestone';
        $project   = $this->deliverable->milestone?->contract?->project?->title ?? 'a project';
        $freelancer = $this->deliverable->freelancer?->name ?? 'The freelancer';

        return [
            'type'    => 'milestone_submitted',
            'title'   => 'Deliverable Submitted 📦',
            'message' => "{$freelancer} submitted work for \"{$milestone}\" on {$project}. Please review and release funds or request changes.",
            'url'     => '/projects/mine/client',
            'icon'    => '📦',
        ];
    }
}
