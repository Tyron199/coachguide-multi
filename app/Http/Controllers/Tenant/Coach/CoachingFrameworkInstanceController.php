<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingFrameworkInstance;
use App\Models\Tenant\CoachingSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingFrameworkInstanceController extends Controller
{
    /**
     * Display framework instances for a specific session
     */
    public function sessionInstances(CoachingSession $session)
    {
        // Authorize access to this session
        $this->authorize('view', $session->client);

        // Load session with relationships
        $session->load(['client', 'coach']);

        // Get framework instances for this session
        $instances = CoachingFrameworkInstance::where('session_id', $session->id)
            ->with(['framework', 'coach', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tenant/coach/coaching-session/SessionFrameworks', [
            'session' => $session,
            'instances' => $instances,
            'can' => [
                'create' => true, // All coaches can assign frameworks
                'update' => auth()->user()->can('update', $session->client),
                'delete' => true, // Can remove framework instances
            ]
        ]);
    }

    /**
     * Display a specific framework instance for editing
     */
    public function show(CoachingFrameworkInstance $instance)
    {
        // Authorize access through session/client
        $this->authorize('view', $instance->client);

        // Load relationships
        $instance->load(['framework', 'session.client', 'coach', 'client']);

        return Inertia::render('Tenant/coach/coaching-session/SessionFrameworkInstance', [
            'instance' => $instance,
            'framework' => $instance->framework,
            'session' => $instance->session,
            'client' => $instance->client,
            'can' => [
                'update' => auth()->user()->can('update', $instance->client),
                'delete' => true,
            ]
        ]);
    }

    /**
     * Update form data for a framework instance
     */
    public function update(Request $request, CoachingFrameworkInstance $instance)
    {
        // Authorize access through session/client
        $this->authorize('update', $instance->client);

        // Validate that the request contains form_data
        $request->validate([
            'form_data' => 'required|array',
            'completed' => 'nullable|boolean',
        ]);

        // Update the instance
        $updateData = [
            'form_data' => $request->form_data,
        ];

        // Mark as completed if requested
        if ($request->boolean('completed')) {
            $updateData['completed_at'] = now();
        } elseif ($instance->completed_at && !$request->boolean('completed')) {
            // If unchecking completed, remove completion date
            $updateData['completed_at'] = null;
        }

        $instance->update($updateData);

        // Check if this is a form submission (has redirect intent)
        // If the request came from the form submit button, redirect to frameworks list
        if ($request->has('redirect_to_list') || (!$request->ajax() && $request->boolean('completed'))) {
            if ($instance->session_id) {
                return to_route('tenant.coach.coaching-sessions.frameworks', $instance->session_id)
                    ->with('success', 'Framework progress saved successfully.');
            } else {
                return to_route('tenant.coach.coaching-frameworks.index')
                    ->with('success', 'Framework progress saved successfully.');
            }
        }

        // For auto-save and regular updates, stay on the same page
        return back()->with('success', 'Framework progress saved successfully.');
    }

    /**
     * Remove a framework instance from a session
     */
    public function destroy(CoachingFrameworkInstance $instance)
    {
        // Authorize access through session/client
        $this->authorize('update', $instance->client);

        $sessionId = $instance->session_id;
        $frameworkName = $instance->framework->name;

        $instance->delete();

        // Redirect back to session frameworks
        if ($sessionId) {
            return to_route('tenant.coach.coaching-sessions.frameworks', $sessionId)
                ->with('success', "{$frameworkName} removed from session successfully.");
        } else {
            return to_route('tenant.coach.coaching-frameworks.index')
                ->with('success', "{$frameworkName} instance deleted successfully.");
        }
    }
}
