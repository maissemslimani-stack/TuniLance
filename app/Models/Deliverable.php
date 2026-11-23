<?php

namespace App\Models;

use App\Enums\DeliverableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliverable extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'freelancer_id',
        'notes',
        'external_link',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliverableStatus::class,
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DeliverableFile::class);
    }
}
