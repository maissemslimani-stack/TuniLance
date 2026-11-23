<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'amount',
        'status',
        'due_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status'   => MilestoneStatus::class,
            'amount'   => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /** Escrow account holding the funds for this milestone */
    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }

    /** All deliverable submissions for this milestone */
    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class)->latest();
    }

    /** The most recent deliverable submission */
    public function latestDeliverable(): HasOne
    {
        return $this->hasOne(Deliverable::class)->latestOfMany();
    }

    /** Dispute linked to this specific milestone */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }
}
