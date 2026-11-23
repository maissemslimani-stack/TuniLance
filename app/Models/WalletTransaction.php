<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use App\Enums\WalletTransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'reference',
        'description',
        'transactionable_id',
        'transactionable_type',
    ];

    protected function casts(): array
    {
        return [
            'type'           => WalletTransactionType::class,
            'status'         => WalletTransactionStatus::class,
            'amount'         => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after'  => 'decimal:2',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Polymorphic link to the related resource (e.g. Escrow, Proposal) */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
