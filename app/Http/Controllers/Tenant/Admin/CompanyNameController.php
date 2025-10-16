<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PlatformBranding;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyNameController extends Controller
{
    /**
     * Show the company name management page
     */
    public function index()
    {
        return Inertia::render('Tenant/admin/platform-settings/CompanyName', [
            'currentCompanyName' => PlatformBranding::current()->company_name ?? '',
        ]);
    }

    /**
     * Update the company name
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:30',
        ]);

        PlatformBranding::current()->updateBranding([
            'company_name' => $validated['company_name']
        ]);

        return back()->with('success', 'Company name updated successfully.');
    }
}

