<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Rules\SubdomainValidation;

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
        ]);
    }

    //Handle registration request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'company_name' => 'required|string|max:255',
            'subdomain' => ['required', new SubdomainValidation()],
        ]);

        //We dont actually want to create a tenant just yet, we want to let them verify email first.
        //So we need to cache this entry and send a link with a token.
        
    }
}