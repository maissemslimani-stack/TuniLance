<?php

namespace App\Actions\Escrow;

use App\Models\Escrow;
use App\Models\Milestone;

class FundMilestoneAction
{
    public function execute(Milestone $milestone): Escrow
    {
        $escrow = Escrow::firstOrCreate(
            ['milestone_id' => $milestone->id],
            [
                'contract_id' => $milestone->contract_id,
                'client_id' => $milestone->contract->client_id,
                'freelancer_id' => $milestone->contract->freelancer_id,
                'funded_amount' => 0,
            ]
        );

        return app(FundEscrowAction::class)->execute($escrow);
    }
}