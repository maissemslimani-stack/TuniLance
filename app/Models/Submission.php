<?php
// app/Models/Submission.php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'freelancer_id',
        'bid_amount',
        'cover_letter',
        'delivery_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status'     => SubmissionStatus::class,
            'bid_amount' => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /** Contract generated when this submission was accepted */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
}