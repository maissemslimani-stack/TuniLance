<?php

namespace App\Actions\Contract;

use App\Enums\ContractStatus;
use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Enums\SubmissionStatus;
use App\Models\ChatRoom;
use App\Models\Contract;
use App\Models\Escrow;
use App\Models\Project;
use App\Models\Submission;
use App\Notifications\SubmissionAccepted;
use Illuminate\Support\Facades\DB;

class AcceptSubmissionAction
{
    /**
     * Accept a freelancer's submission, generate a Contract with milestones
     * (copied from the project's planned milestone templates if any exist,
     * otherwise a single milestone covering the full bid), and pre-create
     * unfunded Escrow records for each milestone.
     *
     * All operations are wrapped in a DB transaction for atomicity.
     */
    public function execute(Project $project, Submission $submission): Contract
    {
        return DB::transaction(function () use ($project, $submission) {

            // 1. Mark all other submissions as rejected
            $project->submissions()
                ->where('id', '!=', $submission->id)
                ->update(['status' => SubmissionStatus::Rejected->value]);

            // 2. Accept the winning submission
            $submission->update(['status' => SubmissionStatus::Accepted->value]);

            // 3. Close the project to new submissions
            $project->update(['status' => ProjectStatus::InProgress->value]);

            // 4. Create the Contract
            $contract = Contract::create([
                'project_id'    => $project->id,
                'submission_id' => $submission->id,
                'client_id'     => $project->client_id,
                'freelancer_id' => $submission->freelancer_id,
                'status'        => ContractStatus::Active->value,
                'total_amount'  => $submission->bid_amount,
                'started_at'    => now(),
            ]);

            // 5. Milestones — copy from the project's planned templates if any exist,
            //    otherwise fall back to a single milestone covering the full bid.
            $templates = $project->milestoneTemplates;

            if ($templates->isNotEmpty()) {
                foreach ($templates as $index => $template) {
                    $milestone = $contract->milestones()->create([
                        'title'      => $template->title,
                        'amount'     => $template->amount,
                        'due_date'   => $template->due_date,
                        'status'     => MilestoneStatus::Pending->value,
                        'sort_order' => $index,
                    ]);

                    Escrow::create([
                        'milestone_id'  => $milestone->id,
                        'contract_id'   => $contract->id,
                        'client_id'     => $project->client_id,
                        'freelancer_id' => $submission->freelancer_id,
                        'funded_amount' => 0.00,
                        'status'        => EscrowStatus::Unfunded->value,
                    ]);
                }
            } else {
                $milestone = $contract->milestones()->create([
                    'title'      => 'Full Project Delivery',
                    'amount'     => $submission->bid_amount,
                    'status'     => MilestoneStatus::Pending->value,
                    'sort_order' => 1,
                ]);

                Escrow::create([
                    'milestone_id'  => $milestone->id,
                    'contract_id'   => $contract->id,
                    'client_id'     => $project->client_id,
                    'freelancer_id' => $submission->freelancer_id,
                    'funded_amount' => 0.00,
                    'status'        => EscrowStatus::Unfunded->value,
                ]);
            }

            // 6. Chat room — reuse one that may already exist (e.g. client messaged
            //    this freelancer before hiring them) instead of always creating fresh.
            $room = ChatRoom::firstOrCreate(
                ['client_id' => $project->client_id, 'freelancer_id' => $submission->freelancer_id],
                ['contract_id' => $contract->id]
            );

            if (! $room->contract_id) {
                $room->update(['contract_id' => $contract->id]);
            }

            // 7. Notify the freelancer that their submission was accepted
            $contract->load('project');
            $submission->freelancer->notify(new SubmissionAccepted($contract));

            return $contract;
        });
    }
}