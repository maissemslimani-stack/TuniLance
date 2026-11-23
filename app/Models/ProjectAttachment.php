<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAttachment extends Model
{
    protected $fillable = ['project_id', 'file_name', 'file_path', 'file_size'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Human-readable size, e.g. "2.4 MB" — used directly in the Blade view */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024).' KB';
    }
}