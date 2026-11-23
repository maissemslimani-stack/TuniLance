<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\StoreSubmissionRequest;
use App\Models\Project;
use App\Models\Submission;

class SubmissionController extends Controller
{
    /** List all submissions submitted by the authenticated freelancer */
    public function index()
    {
        $submissions = Submission::where('freelancer_id', auth()->id())
            ->with('project.client')
            ->latest()
            ->paginate(10);

        return view('freelancer.submissions.index', compact('submissions'));
    }

    /** Submit a new submission for an open project */
    public function store(StoreSubmissionRequest $request, Project $project)
    {
        Submission::create([
            ...$request->validated(),
            'project_id'    => $project->id,
            'freelancer_id' => auth()->id(),
        ]);

        return redirect()->route('freelancer.submissions.index')
            ->with('success', 'Your submission has been submitted.');
    }

    /** Withdraw a pending submission */
    public function withdraw(Submission $submission)
    {
        $this->authorize('update', $submission);

        $submission->update(['status' => 'withdrawn']);

        return back()->with('success', 'Submission withdrawn.');
    }
}
