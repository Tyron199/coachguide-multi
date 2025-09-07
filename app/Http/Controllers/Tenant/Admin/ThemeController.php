<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenant\ThemeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index()
    {
        return Inertia::render('Tenant/admin/platform-settings/Theme', [
            'currentTheme' => $this->themeService->getCurrentTheme(),
            'availableThemes' => $this->themeService->getAvailableThemes(),
            'themePaths' => $this->themeService->getThemePaths(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|string',
        ]);

        $themeName = $request->input('theme');
        
        // Validate theme exists
        if (!$this->themeService->themeExists($themeName)) {
            return back()->withErrors(['theme' => 'Selected theme does not exist.']);
        }

        // Update theme via service
        $this->themeService->updateTheme($themeName);

        return back()->with('success', 'Theme updated successfully.');
    }


    public function resetToDefault()
    {
        $this->themeService->resetTheme();

        return back()->with('success', 'Theme reset to default.');
    }
}
