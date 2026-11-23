<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */


  
    /**
     * Handle an incoming authentication request.
     */

   
    //
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Where to send a user right after logging in — differs by role since each
     * side of the marketplace has a different home screen.
     */
    private function redirectTarget(): string
{
    $user = auth()->user();

    return match (true) {
        $user->isFreelancer() => route('projects.browse'),
        $user->isClient() => route('talents.browse'),
        $user->isModerator() => route('moderator.disputes.index'),
        default => route('projects.browse'),
    };
}
}