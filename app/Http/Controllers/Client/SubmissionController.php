<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Project;
use App\Models\Contract;

class SubmissionController extends Controller
{
    /** List all submissions for a specific project */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $submissions = $project->submissions()
            ->with('freelancer')
            ->latest()
            ->get();

        return view('client.submissions.index', compact('project', 'submissions'));
    }

    /** Accept a submission → creates a Contract */
    public function accept(Project $project, Submission $submission)
    {
        $this->authorize('update', $project);

        // Delegate to action class to keep controller lean
        app(\App\Actions\Contract\AcceptSubmissionAction::class)->execute($project, $submission);

        return redirect()->route('client.contracts.index')
            ->with('success', 'Submission accepted. Contract is now active.');
    }

    /** Reject a submission */
    public function reject(Project $project, Submission $submission)
    {
        $this->authorize('update', $project);

        $submission->update(['status' => 'rejected']);

        return back()->with('success', 'Submission rejected.');
    }
}
