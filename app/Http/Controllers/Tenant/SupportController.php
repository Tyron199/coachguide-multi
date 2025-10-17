<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\SupportFormSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SupportController extends Controller
{
    /**
     * Display the support form.
     */
    public function index()
    {
        return Inertia::render('Tenant/Support');
    }

    /**
     * Handle the support form submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'attachments.*' => 'nullable|image|max:10240', // Only images, 10MB max per file
        ]);

        $user = $request->user();
        $tenant = tenant();

        // Get all uploaded files
        $files = $request->hasFile('attachments') 
            ? $request->file('attachments') 
            : [];

        // Get tenant domain from the request host or the first domain
        $tenantDomain = $request->getHost();

        // Send the email
        Mail::to(config('app.support_email'))
            ->send(new SupportFormSubmitted(
                requestSubject: $validated['subject'],
                requestMessage: $validated['message'],
                userName: $user->name,
                userEmail: $user->email,
                tenantId: $tenant->id,
                tenantDomain: $tenantDomain,
                uploadedFiles: $files
            ));

        return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
    }
}
