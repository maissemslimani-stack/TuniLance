<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use App\Enums\WalletTransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'held_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance'      => 'decimal:2',
            'held_balance' => 'decimal:2',
        ];
    }

    // ─── Computed ────────────────────────────────────────────────────────────

    /** Funds available to spend (not locked in escrow) */
    public function getAvailableBalanceAttribute(): float
    {
        return (float) ($this->balance - $this->held_balance);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->getAvailableBalanceAttribute() >= $amount;
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
