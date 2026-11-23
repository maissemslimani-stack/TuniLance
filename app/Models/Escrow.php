<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Escrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'contract_id',
        'client_id',
        'freelancer_id',
        'funded_amount',
        'platform_fee',
        'freelancer_payout',
        'status',
        'funded_at',
        'released_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'status'             => EscrowStatus::class,
            'funded_amount'      => 'decimal:2',
            'platform_fee'       => 'decimal:2',
            'freelancer_payout'  => 'decimal:2',
            'funded_at'          => 'datetime',
            'released_at'        => 'datetime',
            'refunded_at'        => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /** Wallet transaction records linked to this escrow (polymorphic) */
    public function walletTransactions(): MorphMany
    {
        return $this->morphMany(WalletTransaction::class, 'transactionable');
    }
}
