<?php

namespace App\Enums;

enum DeliverableStatus: string
{
    case Submitted = 'submitted'; // Freelancer submitted, awaiting client review
    case Approved  = 'approved';  // Client approved — triggers escrow release
    case Rejected  = 'rejected';  // Client rejected — freelancer must revise and resubmit
    case Revised   = 'revised';   // Freelancer submitted a revised version
}
