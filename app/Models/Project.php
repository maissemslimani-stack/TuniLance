<?php
// app/Models/Project.php

namespace App\Models;

use App\Enums\ExperienceLevel;
use App\Enums\ProjectDuration;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'category_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'status',
        'deadline',
        'experience_level',
        'duration',
        'submissions_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => ProjectStatus::class,
            'experience_level' => ExperienceLevel::class,
            'duration'         => ProjectDuration::class,
            'budget_min'       => 'decimal:2',
            'budget_max'       => 'decimal:2',
            'deadline'         => 'date',
            'published_at'     => 'datetime',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', ProjectStatus::Open);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeExperienceLevel($query, array $levels)
    {
        return $query->when(!empty($levels), fn ($q) => $q->whereIn('experience_level', $levels));
    }

    public function scopeDuration($query, array $durations)
    {
        return $query->when(!empty($durations), fn ($q) => $q->whereIn('duration', $durations));
    }

    public function scopeBudgetBetween($query, ?float $min, ?float $max)
    {
        return $query
            ->when($min, fn ($q) => $q->where('budget_max', '>=', $min))
            ->when($max, fn ($q) => $q->where('budget_min', '<=', $max));
    }

    public function scopeVerifiedEscrowOnly($query, bool $enabled)
    {
        return $query->when($enabled, fn ($q) => $q->whereHas('client', fn ($c) => $c->where('is_verified', true)));
    }
    public function savedBy(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'saved_projects', 'project_id', 'freelancer_id')->withTimestamps();
}

public function isSavedBy(?int $userId): bool
{
    return $userId && $this->savedBy()->where('users.id', $userId)->exists();
}

    // ─── Relationships ───────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function acceptedSubmission(): HasOne
    {
        return $this->hasOne(Submission::class)->where('status', 'accepted');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectAttachment::class);
    }
    public function skills(): BelongsToMany
    {
    return $this->belongsToMany(Skill::class, 'project_skill');
    }
    // ─── Accessors ───────────────────────────────────────────────────────────

    /** Badge shown on job cards: derived from client verification, not stored on the project itself */
    public function getHasVerifiedEscrowAttribute(): bool
    {
        return (bool) $this->client?->is_verified;
    }
    /** Excludes projects this freelancer was removed/dropped from — used on Browse Jobs */
    public function scopeVisibleTo($query, ?int $freelancerId)
    {
        return $query->when($freelancerId, fn ($q) => $q->whereDoesntHave('blockedFreelancers', fn ($b) =>
            $b->where('freelancer_id', $freelancerId)
        ));
    }

    public function blockedFreelancers()
    {
        return $this->hasMany(\App\Models\ProjectFreelancerBlock::class);
    }
    public function milestoneTemplates(): HasMany
{
    return $this->hasMany(ProjectMilestoneTemplate::class)->orderBy('sort_order');
}
}