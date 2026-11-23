<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmissionAccepted extends Notification
{
    use Queueable;

    public function __construct(public readonly Contract $contract) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $project = $this->contract->project?->title ?? 'a project';

        return [
            'type'    => 'submission_accepted',
            'title'   => 'Proposal Accepted 🎉',
            'message' => "Your proposal for \"{$project}\" was accepted! A contract has been created.",
            'url'     => '/projects/mine',
            'icon'    => '🎉',
        ];
    }
}
