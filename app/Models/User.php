<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'avatar_url',
        'headline',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at'      => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'role'              => UserRole::class,
            'is_verified'       => 'boolean',
        'verified_at'       => 'datetime', 
        ];
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    public function isFreelancer(): bool
    {
        return $this->role === UserRole::Freelancer;
    }

    public function isModerator(): bool
    {
        return $this->role === UserRole::Moderator;
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class);
    }

    /** Projects this user has created as a client */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    /** Submissions this user has submitted as a freelancer */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'freelancer_id');
    }

    /** Contracts where this user is the client */
    public function clientContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id');
    }

    /** Contracts where this user is the freelancer */
    public function freelancerContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'freelancer_id');
    }

    /** Disputes filed by this user */
    public function filedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'reported_by_id');
    }

    /** Disputes assigned to this user as moderator */
    public function assignedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'moderator_id');
    }

    /** Deliverables submitted by this user as a freelancer */
    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'freelancer_id');
    }
    public function savedProjects(): BelongsToMany
{
    return $this->belongsToMany(Project::class, 'saved_projects', 'freelancer_id', 'project_id')->withTimestamps();
}
public function skills(): BelongsToMany
{
    return $this->belongsToMany(Skill::class, 'user_skill');
}

public function categories(): BelongsToMany
{
    return $this->belongsToMany(Category::class, 'freelancer_categories');
}
public function reviewsReceived(): HasMany
{
    return $this->hasMany(Review::class, 'reviewee_id');
}
public function invitations(): HasMany
{
    return $this->hasMany(Invitation::class, 'freelancer_id');
}
public function savedTalents(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'saved_talents', 'client_id', 'freelancer_id')->withTimestamps();
}
public function verifiedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'verified_by');
}
}
