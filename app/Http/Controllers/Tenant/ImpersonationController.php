<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user
     */
    public function start(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        // Prevent if already impersonating
        if (session()->has('impersonator_id')) {
            abort(403, 'Cannot impersonate while already impersonating');
        }

        // Prevent admin-to-admin
        if ($user->hasRole('admin')) {
            abort(403, 'Cannot impersonate administrators');
        }

        // Coach can only impersonate their assigned clients
        if ($currentUser->hasRole('coach') && !$currentUser->hasRole('admin')) {
            if (!$user->hasRole('client') || $user->assigned_coach_id !== $currentUser->id) {
                abort(403, 'You can only impersonate your assigned clients');
            }
        }

        // Admin can impersonate coaches or clients (but not other admins, checked above)
        if ($currentUser->hasRole('admin')) {
            if (!$user->hasRole('coach') && !$user->hasRole('client')) {
                abort(403, 'You can only impersonate coaches or clients');
            }
        }

        // If user is neither admin nor coach, they cannot impersonate anyone
        if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('coach')) {
            abort(403, 'You do not have permission to impersonate users');
        }

        // Store the original user ID in session
        session(['impersonator_id' => $currentUser->id]);

        // Log in as the target user
        Auth::loginUsingId($user->id);

        return redirect()->route('tenant.dashboard')
            ->with('success', "You are now impersonating {$user->name}");
    }

    /**
     * Stop impersonating and return to original user
     */
    public function stop(): RedirectResponse
    {
        // Get the original user ID
        $impersonatorId = session('impersonator_id');

        if (!$impersonatorId) {
            abort(403, 'You are not currently impersonating anyone');
        }

        // Clear the impersonation session
        session()->forget('impersonator_id');

        // Log back in as the original user
        Auth::loginUsingId($impersonatorId);

        return redirect()->back()
            ->with('info', 'You have stopped impersonating and returned to your account');
    }
}

