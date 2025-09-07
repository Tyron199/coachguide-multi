<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Enums\Tenant\UserRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Auth\RegisterRequest;
use App\Models\Tenant\User;
use App\Services\Tenant\InvitationTokenService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(Request $request): Response
    {
        $prefilledData = null;
        $isTokenValid = false;
        
        // Check if there's a token in the request
        if ($request->has('token') && !empty($request->token)) {
            $tokenService = new InvitationTokenService();
            $tokenData = $tokenService->getDataFromToken($request->token);
            
            if ($tokenData) {
                $prefilledData = [
                    'name' => $tokenData['name'],
                    'email' => $tokenData['email'],
                ];
                $isTokenValid = true;
            }
        }
        
        return Inertia::render('Tenant/auth/Register', [
            'prefilledData' => $prefilledData,
            'isTokenValid' => $isTokenValid,
            'token' => $request->token,
        ]);
    }

    /**
     * Handle an incoming registration request.
     * 
     * Checks for existing pending invitation and activates the account.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Get normalized and sanitized data from the request
        $normalizedEmail = $request->getNormalizedEmail();
        $sanitizedName = $request->getSanitizedName();
        
        // Find the existing user (validation already confirmed they exist and are pending)
        $existingUser = User::where('email', $normalizedEmail)->first();
        
        // User exists and is pending - activate their account
        $existingUser->update([
            'name' => $sanitizedName, // Let user set their preferred name
            'password' => Hash::make($request->password),
            'status' => UserRegistrationStatus::ACCEPTED,
            'email_verified_at' => now(), // Auto-verify since coach invited them
        ]);

        // If there was a valid token, consume it (remove from cache)
        if ($request->has('token') && !empty($request->token)) {
            $tokenService = new InvitationTokenService();
            if ($tokenService->isValidToken($request->token)) {
                $tokenService->consumeToken($request->token);
            }
        }

        event(new Registered($existingUser));

        Auth::login($existingUser);

        return to_route('tenant.dashboard');
    }
}
