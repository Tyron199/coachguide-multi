<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingLogController extends Controller
{
        public function index(Request $request){
        return Inertia::render('Tenant/coach/growth-tracker/TrainingDevelopment');
    }
}
