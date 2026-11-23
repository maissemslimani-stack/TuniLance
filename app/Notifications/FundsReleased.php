<?php

namespace App\Notifications;

use App\Models\Escrow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FundsReleased extends Notification
{
    use Queueable;

    public function __construct(public readonly Escrow $escrow) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $project   = $this->escrow->milestone?->contract?->project?->title ?? 'a project';
        $milestone = $this->escrow->milestone?->title ?? 'milestone';
        $amount    = number_format((float) $this->escrow->freelancer_payout, 3);

        return [
            'type'    => 'funds_released',
            'title'   => 'Funds Released 💸',
            'message' => "The client released {$amount} TND for \"{$milestone}\" on {$project}.",
            'url'     => '/wallet',
            'icon'    => '💸',
        ];
    }
}
