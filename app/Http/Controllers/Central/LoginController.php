<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Central\Tenant;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class LoginController extends Controller
{
    //Show registration page
    public function index()
    {
        if (request()->cookies->has('tenant_id')) {
            $tenant = Tenant::find(request()->cookies->get('tenant_id'));
            if ($tenant) {
                $tenantUrl = tenant_route($tenant->domains[0]->domain, 'tenant.login');
                Log::info('Redirecting to tenant login: ' . $tenantUrl);
                return Inertia::location($tenantUrl);
            }
        }


        return Inertia::render('Central/auth/Login');
    }

    //Handle login request
    public function store(Request $request)
    {
       $request->validate([
            'email' => 'required|email',
        ]);

        $tenants = [];

        Tenant::all()->each(function ($tenant) use ($request, &$tenants) {
            $tenant->run(function () use ($request, $tenant, &$tenants) {
                Log::info('Checking tenant: ' . $tenant->id);
                $user = \App\Models\Tenant\User::where('email', $request->email)->first();
                if ($user) {
                    Log::info('User found: ' . $user->email);
                    Log::info('Tenant: ' . $tenant->registration?->company_name);
                    Log::info('Domain: ' . $tenant->domains[0]->domain);
                    // Use tenant_route helper to generate cross-domain URL
                    $tenantUrl = tenant_route($tenant->domains[0]->domain, 'tenant.login', ['email' => $request->email]);
                    $tenants[] = [
                        'id' => $tenant->id,
                        'name' => $tenant->registration?->company_name,
                        'url' => $tenantUrl
                    ];
                }
            });
        });

        Log::info('Tenants found: ' . json_encode($tenants));

        if (count($tenants) == 0) {
            throw ValidationException::withMessages([
                'email' => 'No account found.',
            ]);
        }


        if (count($tenants) == 1) {
            //Save tenant_id to cookie, so next time they load login page we can auto select the tenant
            cookie()->queue('tenant_id', $tenants[0]['id'], 60 * 24 * 30);
            Log::info('Redirecting to tenant login: ' . $tenants[0]['url']);
            return Inertia::location($tenants[0]['url']);
        }

        // Store the data in the session
        session()->flash('tenants', $tenants);
        session()->flash('email', $request->email);

        return redirect()->route('central.login.tenants.show');
        
    }

     public function showSelectTenant(Request $request)
    {
        // Retrieve the data from the session
        $tenants = session('tenants');
        $email = session('email');

        return Inertia::render('Central/auth/Tenants', [
            'tenants' => $tenants,
            'email' => $email
        ]);
    }

     public function selectTenant(Request $request)
    {
        $request->validate([
            'tenant' => 'required|string',
        ]);

        $email = $request->email;

        $tenant = Tenant::find($request->tenant);
        $tenantUrl = tenant_route($tenant->domains[0]->domain, 'tenant.login', ['email' => $email]);


        //Save tenant_id to cookie, so next time they load login page we can auto select the tenant
        cookie()->queue('tenant_id', $tenant->id, 60 * 24 * 30);

        return Inertia::location($tenantUrl);
    }
}