<?php

namespace App\Http\Middleware;

use App\Models\Tenant\PlatformBranding;
use App\Services\Tenant\LogoService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
      protected LogoService $logoService;

    public function __construct(LogoService $logoService)
    {
        $this->logoService = $logoService;
    }
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => tenant() ? (PlatformBranding::current()->company_name ?? config('app.name')) : config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user()?->toInertia(),
                'impersonating' => session()->has('impersonator_id') ? [
                    'original_user_id' => session('impersonator_id'),
                    'current_user_name' => $request->user()?->name,
                ] : null,
            ],
            'tenant' => tenant() ? [
                'id' => tenant('id'),
                'domain' => tenant('domain'),
                'subscribed' => tenant()->subscribed(),
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => session('success') ?: $request->query('success'),
                'error' => session('error') ?: $request->query('error'),
                'info' => session('info') ?: $request->query('info'),
                'warning' => session('warning') ?: $request->query('warning'),
            ],
              'logo' => $this->logoService->getAllLogoUrls()
        ];
    }
}
