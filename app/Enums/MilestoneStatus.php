<?php

namespace App\Enums;

enum MilestoneStatus: string
{
    case Pending    = 'pending';     // Not yet started, waiting for escrow funding
    case Funded     = 'funded';      // Escrow funded — freelancer can begin work
    case InProgress = 'in_progress'; // Freelancer has submitted a deliverable under review
    case Approved   = 'approved';    // Client approved the deliverable — funds released
    case Rejected   = 'rejected';    // Client rejected deliverable — freelancer must resubmit
    case Disputed   = 'disputed';    // Sent to dispute resolution
    case Refunded   = 'refunded';    // Escrow refunded to client
}
