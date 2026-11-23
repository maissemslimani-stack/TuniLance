<?php

namespace App\Enums;

enum EscrowStatus: string
{
    case Unfunded = 'unfunded'; // Escrow created but not yet funded from client wallet
    case Funded   = 'funded';   // Client wallet debited, funds held in escrow
    case Released = 'released'; // Funds transferred to freelancer wallet
    case Refunded = 'refunded'; // Funds returned to client wallet
    case Disputed = 'disputed'; // Frozen pending dispute resolution
}
