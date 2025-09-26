<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ProfessionalCredentialProvider;
use App\Models\Tenant\UserProfessionalCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfessionalCredentialController extends Controller
{
    /**
     * Display a listing of all credential providers and user's credentials
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all active providers with user's credentials
        $providers = ProfessionalCredentialProvider::active()
            ->ordered()
            ->get()
            ->map(function ($provider) use ($user) {
                $userCredential = $provider->userCredential($user->id);
                
                return [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'full_name' => $provider->full_name,
                    'website_url' => $provider->website_url,
                    'logo_url' => $provider->logo_url,
                    'has_credential' => $userCredential ? true : false,
                    'credential' => $userCredential ? [
                        'id' => $userCredential->id,
                        'credential_name' => $userCredential->credential_name,
                        'expiry_date' => $userCredential->expiry_date?->format('Y-m-d'),
                        'is_expired' => $userCredential->is_expired,
                        'is_expiring_soon' => $userCredential->is_expiring_soon,
                        'days_until_expiry' => $userCredential->days_until_expiry,
                        'original_filename' => $userCredential->original_filename,
                        'formatted_file_size' => $userCredential->formatted_file_size,
                        'created_at' => $userCredential->created_at,
                    ] : null,
                ];
            });

        // Get summary statistics
        $stats = [
            'total_providers' => $providers->count(),
            'uploaded_count' => $providers->where('has_credential', true)->count(),
            'expiring_soon_count' => UserProfessionalCredential::where('user_id', $user->id)
                ->expiringSoon()
                ->count(),
            'expired_count' => UserProfessionalCredential::where('user_id', $user->id)
                ->expired()
                ->count(),
        ];

        return Inertia::render('Tenant/coach/growth-tracker/ProfessionalCredentials', [
            'providers' => $providers,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly uploaded credential
     */
    public function store(Request $request)
    {
        $request->validate([
            'professional_credential_provider_id' => 'required|exists:professional_credential_providers,id',
            'credential_name' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'certificate' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $user = auth()->user();
        $provider = ProfessionalCredentialProvider::findOrFail($request->professional_credential_provider_id);

        // Check if user already has a credential for this provider
        $existingCredential = UserProfessionalCredential::where('user_id', $user->id)
            ->where('professional_credential_provider_id', $provider->id)
            ->first();

        if ($existingCredential) {
            return back()->withErrors(['certificate' => 'You already have a credential uploaded for this provider. Please remove it first or use the replace option.']);
        }

        // Store the PDF file in private storage
        $file = $request->file('certificate');
        $filename = sprintf(
            '%s_%s_%s.pdf',
            $user->id,
            $provider->id,
            now()->timestamp
        );
        $path = $file->storeAs('credentials', $filename);

        // Create the credential record
        $credential = UserProfessionalCredential::create([
            'user_id' => $user->id,
            'professional_credential_provider_id' => $provider->id,
            'credential_name' => $request->credential_name,
            'expiry_date' => $request->expiry_date,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Professional credential uploaded successfully.');
    }

    /**
     * Update the specified credential (mainly for expiry date)
     */
    public function update(Request $request, UserProfessionalCredential $credential)
    {
        // Ensure the credential belongs to the authenticated user
        if ($credential->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'credential_name' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        $credential->update([
            'credential_name' => $request->credential_name,
            'expiry_date' => $request->expiry_date,
        ]);

        return back()->with('success', 'Professional credential updated successfully.');
    }

    /**
     * Replace an existing credential with a new file
     */
    public function replace(Request $request, UserProfessionalCredential $credential)
    {
        // Ensure the credential belongs to the authenticated user
        if ($credential->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'certificate' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'credential_name' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        // Delete old file
        if ($credential->file_path && Storage::exists($credential->file_path)) {
            Storage::delete($credential->file_path);
        }

        // Store new file
        $file = $request->file('certificate');
        $filename = sprintf(
            '%s_%s_%s.pdf',
            $credential->user_id,
            $credential->professional_credential_provider_id,
            now()->timestamp
        );
        $path = $file->storeAs('credentials', $filename);

        // Update credential record
        $credential->update([
            'credential_name' => $request->credential_name ?? $credential->credential_name,
            'expiry_date' => $request->expiry_date ?? $credential->expiry_date,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Professional credential replaced successfully.');
    }

    /**
     * Remove the specified credential
     */
    public function destroy(UserProfessionalCredential $credential)
    {
        // Ensure the credential belongs to the authenticated user
        if ($credential->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the file (handled by model observer)
        $credential->delete();

        return back()->with('success', 'Professional credential removed successfully.');
    }

    /**
     * Download the credential certificate
     */
    public function download(UserProfessionalCredential $credential)
    {
        // Ensure the credential belongs to the authenticated user
        if ($credential->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!Storage::exists($credential->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download(
            $credential->file_path,
            $credential->original_filename
        );
    }
}