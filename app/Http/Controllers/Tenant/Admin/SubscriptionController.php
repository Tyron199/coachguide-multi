<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function manage()
    {

        //If not subscribed, redirect to subscribe
        if (!tenant()->subscribed()) {
            return redirect()->route('tenant.admin.subscription.subscribe');
        }

        return Inertia::render('Tenant/admin/subscription/ManageSubscription');
    }

    public function subscribe()
    {
        return Inertia::render('Tenant/admin/subscription/Subscribe');
    }

    public function receipt(){
        //After payment, we redirect here while we wait for the webhooks from paddle to complete the subscription
        return Inertia::render('Tenant/admin/subscription/Receipt');
    }
}
