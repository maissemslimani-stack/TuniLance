<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft      = 'draft';       // Client is still configuring the project
    case Open       = 'open';        // Accepting proposals from freelancers
    case InProgress = 'in_progress'; // A freelancer has been hired, contract active
    case Completed  = 'completed';   // All milestones approved and closed
    case Cancelled  = 'cancelled';   // Project cancelled before completion
    case Disputed   = 'disputed';    // Under moderator review
}
