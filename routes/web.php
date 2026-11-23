<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');



Route::middleware('auth')->group(function () {
    // Freelancer — job browsing & applications
    Route::get('/jobs', \App\Livewire\Freelancer\BrowseJobs::class)->name('projects.browse');
    Route::get('/jobs/{project}', \App\Livewire\Freelancer\JobDetail::class)->name('projects.show');
    Route::get('/projects/mine', \App\Livewire\Freelancer\ProjectsIndex::class)->name('projects.mine');
    Route::get('/milestones/{milestone}/submit', \App\Livewire\Freelancer\SubmitDeliverable::class)
        ->name('milestones.submit');
    Route::get('/messages', \App\Livewire\Chat\Show::class)->name('messages.index');
    Route::get('/wallet', \App\Livewire\Freelancer\WalletIndex::class)->name('wallet.index');
    Route::get('/profile', \App\Livewire\Freelancer\ProfileShow::class)->name('profile.show');
    Route::get('/profile/edit', \App\Livewire\Freelancer\ProfileIndex::class)->name('profile.edit');
    Route::get('/talents', \App\Livewire\Client\BrowseTalents::class)->name('talents.browse');
    Route::get('/freelancers/{user}', \App\Livewire\Client\FreelancerProfile::class)->name('freelancers.show');
    Route::get('/wallet/client', \App\Livewire\Client\WalletIndex::class)->name('wallet.client.index');
    Route::get('/client/profile', \App\Livewire\Client\ProfileShow::class)->name('client.profile.show');
    Route::get('/client/profile/edit', \App\Livewire\Client\ProfileIndex::class)->name('client.profile.edit');
    Route::get('/projects/create', \App\Livewire\Client\CreateProject::class)->name('projects.create');
    Route::get('/projects/mine/client', \App\Livewire\Client\ProjectsIndex::class)->name('projects.mine.client');
    Route::get('/projects/{project}/client', \App\Livewire\Client\ProjectShow::class)->name('projects.client.show');
    Route::get('/messages/{chatRoom}', \App\Livewire\Chat\Show::class)->name('messages.show');

    Route::prefix('moderator')->name('moderator.')->group(function () {
    Route::get('/verification', \App\Livewire\Moderator\VerificationQueue::class)->name('verification.index');
    Route::get('/disputes', \App\Livewire\Moderator\DisputeInbox::class)->name('disputes.index');
    Route::get('/disputes/{dispute}', \App\Livewire\Moderator\DisputeShow::class)->name('disputes.show');
});
});

require __DIR__.'/auth.php';