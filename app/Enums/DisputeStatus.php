<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open       = 'open';        // Dispute filed, awaiting moderator assignment
    case UnderReview = 'under_review'; // Moderator actively reviewing
    case Resolved   = 'resolved';    // Moderator has issued a resolution
    case Closed     = 'closed';      // Dispute fully closed (funds distributed)
}
