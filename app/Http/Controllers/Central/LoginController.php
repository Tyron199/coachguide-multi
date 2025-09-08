<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginController extends Controller
{
    //Show registration page
    public function index()
    {
        return Inertia::render('Central/auth/Login');
    }

    //Handle login request
    public function store(Request $request)
    {
        //We need to lookup and figure out what tenant this email might be belong to.
        //TODO

        throw new \Exception('Not implemented');
        
    }
}