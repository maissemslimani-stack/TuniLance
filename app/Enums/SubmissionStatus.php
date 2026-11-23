<?php
// app/Enums/SubmissionStatus.php
// Delete the old app/Enums/ProposalStatus.php file once this replaces it everywhere

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitted = 'submitted';
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Withdrawn = 'withdrawn';
}