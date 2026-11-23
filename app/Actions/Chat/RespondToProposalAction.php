<?php

namespace App\Actions\Chat;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RespondToProposalAction
{
    public function execute(ChatMessage $proposal, User $responder, bool $accepted): ChatMessage
    {
        if (! $proposal->canBeRespondedToBy($responder)) {
            throw ValidationException::withMessages([
                'proposal' => 'This proposal is no longer active or you are not eligible to respond to it.',
            ]);
        }

        return DB::transaction(function () use ($proposal, $responder, $accepted) {
            $proposal->update([
                'proposal_status' => $accepted ? 'accepted' : 'rejected',
                'proposal_resolved_by' => $responder->id,
                'proposal_resolved_at' => now(),
            ]);

            $confirmation = $accepted
                ? "✅ {$responder->name} accepted the proposal: \"{$proposal->message}\""
                : "❌ {$responder->name} rejected the proposal: \"{$proposal->message}\"";

            return $proposal->chatRoom->messages()->create([
                'sender_id' => $responder->id,
                'message' => $confirmation,
                'is_system_message' => true,
            ]);
        });
    }
}