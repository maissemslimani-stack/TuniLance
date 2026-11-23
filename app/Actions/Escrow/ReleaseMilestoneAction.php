<?php

namespace App\Actions\Escrow;

use App\Enums\ContractStatus;
use App\Enums\ProjectStatus;
use App\Models\Escrow;
use Illuminate\Support\Facades\DB;

class ReleaseMilestoneAction
{
    public function execute(Escrow $escrow): Escrow
    {
        return DB::transaction(function () use ($escrow) {
            $escrow = app(ReleaseEscrowAction::class)->execute($escrow);

            $contract = $escrow->contract;
            $hasRemainingMilestones = $contract->milestones()
                ->where('sort_order', '>', $escrow->milestone->sort_order)
                ->exists();

            if (! $hasRemainingMilestones) {
                $contract->update(['status' => ContractStatus::Completed, 'completed_at' => now()]);
                $contract->project()->update(['status' => ProjectStatus::Completed]);
            }

            return $escrow;
        });
    }
}