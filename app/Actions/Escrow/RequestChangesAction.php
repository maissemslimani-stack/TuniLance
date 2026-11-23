<?php

namespace App\Actions\Escrow;

use App\Enums\DeliverableStatus;
use App\Enums\MilestoneStatus;
use App\Events\ChatMessageSent;
use App\Models\Deliverable;
use App\Notifications\ChangesRequested;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RequestChangesAction
{
    /**
     * Client rejects the current deliverable, writes feedback, and sets a new
     * due date. If that date is later than the original, the delay cascades
     * forward onto the project's overall deadline and every later milestone,
     * keeping their relative spacing intact.
     */
    public function execute(Deliverable $deliverable, string $feedback, Carbon $newDueDate): Deliverable
    {
        return DB::transaction(function () use ($deliverable, $feedback, $newDueDate) {
            $milestone = $deliverable->milestone;
            $oldDueDate = $milestone->due_date;

            $deliverable->update([
                'status' => DeliverableStatus::Rejected,
                'rejection_reason' => $feedback,
            ]);

            $milestone->update([
                'status' => MilestoneStatus::Rejected,
                'due_date' => $newDueDate,
            ]);

            // Cascade the delay forward if the new date is later than the original
            if ($oldDueDate && $newDueDate->gt($oldDueDate)) {
                $delayDays = $oldDueDate->diffInDays($newDueDate);

                $milestone->contract->project()->update([
                    'deadline' => $milestone->contract->project->deadline?->addDays($delayDays),
                ]);

                $milestone->contract->milestones()
                    ->where('sort_order', '>', $milestone->sort_order)
                    ->whereNotNull('due_date')
                    ->get()
                    ->each(fn ($m) => $m->update(['due_date' => $m->due_date->addDays($delayDays)]));
            }

            // Notify the freelancer via the real, working chat system
            $room = \App\Models\ChatRoom::firstOrCreate([
                'client_id' => $milestone->contract->client_id,
                'freelancer_id' => $milestone->contract->freelancer_id,
            ], ['contract_id' => $milestone->contract_id]);

            $message = $room->messages()->create([
                'sender_id' => $milestone->contract->client_id,
                'message' => "🔁 Changes requested on \"{$milestone->title}\": {$feedback} (new due date: {$newDueDate->format('M d, Y')})",
                'is_system_message' => true,
            ]);

            broadcast(new ChatMessageSent($message))->toOthers();

            // Also send an in-app notification to the freelancer
            $milestone->contract->freelancer->notify(
                new ChangesRequested($deliverable->fresh(['milestone.contract.project']), $feedback)
            );

            return $deliverable->fresh();
        });
    }
}