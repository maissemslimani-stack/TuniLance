<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'message',
        'attachment_url',
        'attachment_name',
        'is_system_message',
        'is_proposal',
        'proposal_status',
        'proposal_resolved_by',
        'proposal_resolved_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_system_message'    => 'boolean',
            'is_proposal'          => 'boolean',
            'proposal_resolved_at' => 'datetime',
            'read_at'              => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function proposalResolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposal_resolved_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /** Is this the room's single live/actionable proposal right now? */
    public function isActiveProposal(): bool
    {
        return $this->is_proposal
            && $this->proposal_status === 'pending'
            && $this->chatRoom->active_proposal_message_id === $this->id;
    }

    /** Can the given user act (accept/reject) on this proposal? Only the non-sender, while pending & active. */
    public function canBeRespondedToBy(User $user): bool
    {
        return $this->isActiveProposal() && $this->sender_id !== $user->id;
    }
}