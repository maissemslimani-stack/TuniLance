<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverableFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliverable_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class);
    }
}
