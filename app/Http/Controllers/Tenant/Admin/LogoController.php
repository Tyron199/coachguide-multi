<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenant\LogoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogoController extends Controller
{
    protected LogoService $logoService;

    public function __construct(LogoService $logoService)
    {
        $this->logoService = $logoService;
    }
    /**
     * Show the logo management page
     */
    public function index()
    {
        return Inertia::render('Tenant/admin/platform-settings/Logo', [
            'hasCustomLogo' => $this->logoService->hasCustomLogo('logo'),
            'hasCustomIcon' => $this->logoService->hasCustomLogo('icon'),
        ]);
    }

    /**
     * Upload a custom logo
     */
    public function upload(Request $request)
    {
        $request->validate([
            'logo' => 'required|file|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048', // 2MB max
            'type' => 'required|in:logo,icon',
        ]);

        $type = $request->input('type');
        $file = $request->file('logo');
        
        // Store the logo in tenant-scoped storage
        $this->logoService->storeCustomLogo($type, $file);

        return back()->with('success', ucfirst($type) . ' uploaded successfully.');
    }


    /**
     * Reset to default logo
     */
    public function reset(Request $request)
    {
        $request->validate([
            'type' => 'required|in:logo,icon',
        ]);

        $type = $request->input('type');
        
        // Delete the custom logo file from tenant storage
        $this->logoService->deleteCustomLogo($type);

        return back()->with('success', ucfirst($type) . ' reset to default.');
    }

}
