<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Rules\SubdomainValidation;
use App\Rules\RecaptchaValidation;
use App\Models\Central\Registration;
use App\Models\Central\Tenant;
use App\Enums\Central\RegistrationStatus;
use App\Notifications\Central\RegistrationConfirm;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    //Show registration page
    public function index()
    {
          $appUrl = config('app.url');
        $domain = parse_url($appUrl, PHP_URL_HOST);

        return Inertia::render('Central/auth/Register', [
            'domain_suffix' => $domain,
            'reserved_subdomains' => SubdomainValidation::getReservedSubdomains(),
            'recaptcha_site_key' => config('services.recaptcha.key'),
        ]);
    }

    //Handle registration request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:registrations,email,NULL,id,status,' . RegistrationStatus::COMPLETED->value
            ],
            'company_name' => 'required|string|max:255',
            'g-recaptcha-response' => ['required', new RecaptchaValidation()],
        ]);

        // Create the registration entry
        $registration = Registration::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_name' => $validated['company_name'],
            'confirmation_token' => Registration::generateConfirmationToken(),
            'token_expires_at' => now()->addDays(7), // 7 day expiry - generous for leads
            'status' => RegistrationStatus::PENDING,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
        ]);

        // Send confirmation email
        Notification::route('mail', $registration->email)
            ->notify(new RegistrationConfirm($registration));

        //Add email to session to share with 'verify' page
        session(['email' => $registration->email]);

        return redirect()->route('central.registration.verify');
    }

    public function verify(Request $request)
    {
        //Get email from session
        $email = session('email');

        //Remove email from session
        session()->forget('email');

        //Page to show after they submit, telling them to check their email
        return Inertia::render('Central/auth/Verify', [
            'email' => $email,
        ]);
    }

    /**
     * Show the confirmation page where user enters password and subdomain
     */
    public function showConfirm(Request $request, string $token)
    {
        $registration = Registration::where('confirmation_token', $token)->first();
        
        if (!$registration) {
            abort(404, 'Invalid confirmation link.');
        }

        // Check if token is expired
        if ($registration->isTokenExpired()) {
            return redirect()->route('central.register')->with('error', 'This confirmation link has expired. Please register again.');
        }

        // Check if already completed
        if ($registration->status === RegistrationStatus::COMPLETED) {
            return redirect()->route('central.login')->with('info', 'Your account has already been set up. You can log in to your tenant.');
        }

        // Check if registration failed
        if ($registration->status === RegistrationStatus::FAILED) {
            return redirect()->route('central.register')->with('error', 'This registration has failed. Please register again.');
        }

        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
        
        return Inertia::render('Central/auth/Confirm', [
            'registration' => [
                'name' => $registration->name,
                'email' => $registration->email,
                'company_name' => $registration->company_name,
            ],
            'token' => $token,
            'domain_suffix' => $appDomain,
            'reserved_subdomains' => SubdomainValidation::getReservedSubdomains(),
        ]);
    }

    /**
     * Handle the confirmation form submission
     */
    public function submitConfirm(Request $request, string $token)
    {
        $registration = Registration::where('confirmation_token', $token)->first();
        
        if (!$registration) {
            abort(404, 'Invalid confirmation link.');
        }

        // Check if token is expired
        if ($registration->isTokenExpired()) {
            return redirect()->route('central.register')->with('error', 'This confirmation link has expired. Please register again.');
        }

        // Check if this email already has a completed registration (prevents multiple tenants per email)
        $existingCompleted = Registration::where('email', $registration->email)
            ->where('status', RegistrationStatus::COMPLETED)
            ->exists();
            
        if ($existingCompleted) {
            return redirect()->route('central.login')->with('info', 'You already have an account with this email address. Please log in to your existing tenant.');
        }

        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($registration) {
                    if ($value !== $registration->email) {
                        $fail('The email address cannot be changed during confirmation.');
                    }
                },
            ],
            'company_name' => 'required|string|max:255',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                // Add your password rules here
            ],
            'subdomain' => ['required', new SubdomainValidation()],
        ]);

        try {
            // Update registration with any changed values (except email which is validated to be unchanged)
            $registration->update([
                'name' => $validated['name'],
                'company_name' => $validated['company_name'],
            ]);

            // Create the tenant and domain atomically
            $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $fullDomain = $validated['subdomain'] . '.' . $appDomain;

            // Generate a unique tenant ID
            $tenantId = \Illuminate\Support\Str::uuid();

            // Create tenant
            $tenant = Tenant::create(['id' => $tenantId]);
            $tenant->domains()->create(['domain' => $fullDomain]);

            // Create the admin user in the tenant context
            $tenant->run(function () use ($registration, $validated) {
                $user = \App\Models\Tenant\User::create([
                    'name' => $registration->name,
                    'email' => $registration->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                    'email_verified_at' => now(),
                    'status' => \App\Enums\Tenant\UserRegistrationStatus::ACCEPTED,
                ]);

                // Assign admin and coach roles
                $user->assignRole([\App\Enums\Tenant\UserRole::ADMIN->value, \App\Enums\Tenant\UserRole::COACH->value]);

                // Create platform branding
                $platformBranding = \App\Models\Tenant\PlatformBranding::create([
                    'company_name' => $registration->company_name,
                ]);
            });

            // Update registration
            $registration->update([
                'status' => RegistrationStatus::COMPLETED,
                'tenant_id' => $tenant->id,
                'completed_at' => now(),
            ]);

            // Invalidate any other pending registrations for this email to prevent multiple tenants
            Registration::where('email', $registration->email)
                ->where('id', '!=', $registration->id)
                ->whereIn('status', [RegistrationStatus::PENDING])
                ->update(['status' => RegistrationStatus::FAILED]);

            // Redirect to the new tenant domain
            return Inertia::location('https://' . $fullDomain . '/login');

        } catch (\Exception $e) {
            Log::error('Failed to create platform: ' . $e->getMessage());
            // Mark registration as failed
            $registration->update(['status' => RegistrationStatus::FAILED]);
            
            return back()->with('error', 'Failed to create your platform. Please try again or contact support.')->withInput();
        }
    }
}