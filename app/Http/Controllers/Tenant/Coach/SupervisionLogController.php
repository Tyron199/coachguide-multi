<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
class SupervisionLogController extends Controller
{
        public function index(Request $request){
        return Inertia::render('Tenant/coach/growth-tracker/SupervisionLog');
    }
}
