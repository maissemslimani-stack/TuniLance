<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Deliverable\StoreDeliverableRequest;
use App\Models\Milestone;
use App\Models\Deliverable;

class DeliverableController extends Controller
{
    /** Submit a deliverable for a milestone */
    public function store(StoreDeliverableRequest $request, Milestone $milestone)
    {
        $this->authorize('submitDeliverable', $milestone);

        $deliverable = Deliverable::create([
            'milestone_id'  => $milestone->id,
            'freelancer_id' => auth()->id(),
            'notes'         => $request->validated('notes'),
            'external_link' => $request->validated('external_link'),
        ]);

        // Store each uploaded file
        foreach ($request->file('files', []) as $file) {
            $path = $file->store("deliverables/{$deliverable->id}", 'private');
            $deliverable->files()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        // Update milestone status
        $milestone->update(['status' => 'in_progress']);

        return back()->with('success', 'Deliverable submitted for review.');
    }
}
