<?php

namespace App\Actions\Chat;

use App\Events\ChatMessageSent;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendMessageAction
{
    public function execute(ChatRoom $chatRoom, User $sender, string $text): \App\Models\ChatMessage
    {
        return DB::transaction(function () use ($chatRoom, $sender, $text) {
            $isProposal = Str::contains($text, '@proposal');

            $message = $chatRoom->messages()->create([
                'sender_id' => $sender->id,
                'message' => $text,
                'is_proposal' => $isProposal,
                'proposal_status' => $isProposal ? 'pending' : null,
            ]);

            if ($isProposal) {
                $chatRoom->update(['active_proposal_message_id' => $message->id]);
            }

            $chatRoom->update(['last_message_at' => now()]);

            // @accept / @refuse shortcuts — only meaningful if there's an active proposal
            // this sender didn't create themselves (matches the "one-time, other party only" rule)
            $activeProposal = $chatRoom->activeProposal;
            if ($activeProposal && $activeProposal->canBeRespondedToBy($sender)) {
                if (Str::contains($text, '@accept')) {
                    app(RespondToProposalAction::class)->execute($activeProposal, $sender, true);
                } elseif (Str::contains($text, '@refuse')) {
                    app(RespondToProposalAction::class)->execute($activeProposal, $sender, false);
                }
            }

            broadcast(new ChatMessageSent($message))->toOthers();

            return $message;
        });
    }
}