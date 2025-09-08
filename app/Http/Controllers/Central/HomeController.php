<?php   

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Central/Welcome' ,[
        'plans' => config('subscriptions.plans')
    ]);
    }

    public function terms()
    {
        return Inertia::render('Central/Terms');
    }

    public function privacy()
    {
        return Inertia::render('Central/Privacy');
    }
}   