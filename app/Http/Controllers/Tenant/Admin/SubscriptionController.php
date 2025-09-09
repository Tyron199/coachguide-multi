<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
class SubscriptionController extends Controller
{
    public function manage()
    {
        $tenant = tenant();
        
        //If not subscribed, redirect to subscribe
        if (!$tenant->subscribed()) {
            return redirect()->route('tenant.admin.subscription.subscribe');
        }

        // Get the current subscription
        $subscription = $tenant->subscription();
        
        // Get recent transactions (last 10)
        $transactions = $tenant->transactions()
            ->orderBy('billed_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'paddle_id' => $transaction->paddle_id,
                    'status' => $transaction->status,
                    'total' => $transaction->total(),
                    'tax' => $transaction->tax(),
                    'currency' => $transaction->currency,
                    'billed_at' => $transaction->billed_at,
                    'invoice_number' => $transaction->invoice_number,
                ];
            });

        // Get next payment info if subscription is active
        $nextPayment = null;
        $lastPayment = null;
        
        if ($subscription && $subscription->active()) {
            $next = $subscription->nextPayment();
            if ($next) {
                $nextPayment = [
                    'amount' => $next->amount(),
                    'currency' => $next->currency,
                    'date' => $next->date(),
                ];
            }
            
            $last = $subscription->lastPayment();
            if ($last) {
                $lastPayment = [
                    'amount' => $last->amount(),
                    'currency' => $last->currency,
                    'date' => $last->date(),
                ];
            }
        }

        // Get subscription details
        $subscriptionData = null;
        if ($subscription) {
            $subscriptionData = [
                'id' => $subscription->id,
                'paddle_id' => $subscription->paddle_id,
                'status' => $subscription->status,
                'paddle_status' => $subscription->paddle_status,
                'quantity' => $subscription->quantity,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
                'ends_at' => $subscription->ends_at,
                'paused_at' => $subscription->paused_at,
                'trial_ends_at' => $subscription->trial_ends_at,
                'on_trial' => $subscription->onTrial(),
                'on_grace_period' => $subscription->onGracePeriod(),
                'on_paused_grace_period' => $subscription->onPausedGracePeriod(),
                'canceled' => $subscription->canceled(),
                'active' => $subscription->active(),
                'past_due' => $subscription->pastDue(),
                'recurring' => $subscription->recurring(),
            ];
        }

        return Inertia::render('Tenant/admin/subscription/ManageSubscription', [
            'subscription' => $subscriptionData,
            'transactions' => $transactions,
            'nextPayment' => $nextPayment,
            'lastPayment' => $lastPayment,
            'plans' => config('subscriptions.plans'),
        ]);
    }

    public function subscribe(Request $request)
    {
        $tenant = tenant();
        if (!$tenant->customer) {
            // We need to switch to central database context to create the customer
            // Temporarily switch the default connection to central for the customer creation
            $originalConnection = config('database.default');
            config(['database.default' => config('tenancy.database.central_connection')]);
            
            try {
                // Get the tenant from central database and create customer
                $centralTenant = \App\Models\Central\Tenant::find($tenant->id);
                $centralTenant->createAsCustomer(['name' => auth()->user()->name, 'email' => auth()->user()->email]);
                
                // Refresh the current tenant instance to get the updated customer relationship
                $tenant->refresh();
            } finally {
                // Always restore the original connection
                config(['database.default' => $originalConnection]);
            }
        }

        return Inertia::render('Tenant/admin/subscription/Subscribe', [
            'token' => config('cashier.client_side_token'),
            'sandbox' => config('cashier.sandbox'),
            'plans' => config('subscriptions.plans'),
            'canHaveTrial' => $tenant->canHaveTrial(),
             'customer' => $tenant->customer,
             'success_url' => tenant()->getRoute('tenant.admin.subscription.receipt'),
        ]);
    }

    public function receipt(){
        //After payment, we redirect here while we wait for the webhooks from paddle to complete the subscription
        
        // If already subscribed, redirect to manage subscription
        if (tenant()->subscribed()) {
            return redirect()->route('tenant.admin.subscription.manage');
        }
        
        return Inertia::render('Tenant/admin/subscription/Receipt', [
            'subscribed' => fn () => tenant()->subscribed(),
        ]);
    }

    public function updatePaymentMethod(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->subscription();
        
        if (!$subscription) {
            return redirect()->route('tenant.admin.subscription.subscribe');
        }
        
        // Redirect to Paddle's hosted payment method update page
        return Inertia::location($subscription->redirectToUpdatePaymentMethod());
    }

    public function billingPortal(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->subscription();

        if (!$subscription) {
            return redirect()->route('tenant.admin.subscription.subscribe');
        }
        //I guess for now we just use this because i cant find a way to just redirect to the billing portal
        return Inertia::location($subscription->redirectToUpdatePaymentMethod());
    }

    public function cancel(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->subscription();
        
        if (!$subscription) {
            return redirect()->route('tenant.admin.subscription.subscribe');
        }
        
        // Cancel at period end (grace period)
        $subscription->cancel();
        
        return redirect()->route('tenant.admin.subscription.manage')
            ->with('success', 'Your subscription has been canceled. You will continue to have access until the end of your billing period.');
    }

    public function resume(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->subscription();
        
        if (!$subscription || !$subscription->onGracePeriod()) {
            return redirect()->route('tenant.admin.subscription.manage');
        }
        
        // Resume a canceled subscription
        $subscription->stopCancelation();
        
        return redirect()->route('tenant.admin.subscription.manage')
            ->with('success', 'Your subscription has been resumed.');
    }

    public function downloadInvoice(Request $request, $transactionId)
    {
        $tenant = tenant();
        $transaction = $tenant->transactions()->where('id', $transactionId)->firstOrFail();
        
        // Redirect to Paddle's invoice PDF
        return $transaction->redirectToInvoicePdf();
    }
}
