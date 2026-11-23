<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case Deposit         = 'deposit';          // Funds added to wallet from external source
    case Withdrawal      = 'withdrawal';       // Funds withdrawn from wallet to external source
    case EscrowFund      = 'escrow_fund';      // Funds moved from wallet into escrow
    case EscrowRelease   = 'escrow_release';   // Funds released from escrow to freelancer wallet
    case EscrowRefund    = 'escrow_refund';    // Funds refunded from escrow back to client wallet
    case PlatformFee     = 'platform_fee';     // Platform commission deducted

    public function label(): string
    {
        return match($this) {
            self::Deposit       => 'Deposit',
            self::Withdrawal    => 'Withdrawal',
            self::EscrowFund    => 'Escrow Funded',
            self::EscrowRelease => 'Escrow Released',
            self::EscrowRefund  => 'Escrow Refunded',
            self::PlatformFee   => 'Platform Fee',
        };
    }
}
