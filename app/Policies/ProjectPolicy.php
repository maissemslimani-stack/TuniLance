<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Public open projects visible to all; drafts visible only to the owner
        if ($project->status === ProjectStatus::Draft) {
            return $user->id === $project->client_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function update(User $user, Project $project): bool
    {
        // Only owner can edit; cannot edit once a contract is active
        return $user->id === $project->client_id
            && !in_array($project->status->value, ['in_progress', 'completed', 'cancelled']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->client_id
            && $project->status === ProjectStatus::Draft;
    }
}
