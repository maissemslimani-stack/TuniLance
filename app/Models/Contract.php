<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'submission_id',
        'client_id',
        'freelancer_id',
        'moderator_id',
        'status',
        'total_amount',
        'terms',
        'started_at',
        'completed_at',
        'invitation_id',
    ];

    protected function casts(): array
    {
        return [
            'status'       => ContractStatus::class,
            'total_amount' => 'decimal:2',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', ContractStatus::Active);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('sort_order');
    }

    public function chatRoom(): HasOne
    {
        return $this->hasOne(ChatRoom::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function activeDispute(): HasOne
    {
        return $this->hasOne(Dispute::class)->whereIn('status', ['open', 'under_review']);
    }
    public function invitation(): BelongsTo
{
    return $this->belongsTo(Invitation::class);
}
}
