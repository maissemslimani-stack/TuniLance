<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_item_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function portfolioItem(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class);
    }
}
