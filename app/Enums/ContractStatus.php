<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Active              = 'active';               // Contract is live and milestones are progressing
    case Paused              = 'paused';                // Temporarily paused (e.g. awaiting client action)
    case Completed            = 'completed';             // All milestones released and contract closed
    case Cancelled            = 'cancelled';             // Generic termination before completion
    case Disputed             = 'disputed';              // Under moderator/dispute review
    case FreelancerChanged    = 'freelancer_changed';    // Client swapped out this freelancer; project reopened
    case CancelledRefunded    = 'cancelled_refunded';    // Client ended the whole project + requested a refund
    case DroppedByFreelancer  = 'dropped_by_freelancer'; // Freelancer chose to leave the project
}