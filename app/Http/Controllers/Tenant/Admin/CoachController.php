<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SendCoachInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Enums\Tenant\UserRole;
use App\Enums\Tenant\UserRegistrationStatus;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    /**
     * Display a listing of the coaches.
     */
    public function index(Request $request)
    {
        // Only admins can manage coaches
        $this->authorize('viewAny', User::class);
        
        $isArchived = $request->boolean('archived', false);
        
        $query = User::role(UserRole::COACH)
            ->withCount('assignedClients') // Count assigned clients
            ->when($isArchived, 
                fn($q) => $q->where('archived', true),
                fn($q) => $q->where('archived', false)
            );
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Sorting functionality
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        // Handle different sort columns
        switch ($sortBy) {
            case 'name':
                $query->orderBy('users.name', $sortDirection);
                break;
            case 'email':
                $query->orderBy('users.email', $sortDirection);
                break;
            case 'clients_count':
                $query->orderBy('assigned_clients_count', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('users.created_at', $sortDirection);
                break;
        }
        
        $coaches = $query->paginate(config('app.pagination_limit'));
        
        return Inertia::render("Tenant/admin/coaches/ListCoaches", [
            'coaches' => $coaches,
            'filters' => [
                'search' => $request->search,
                'archived' => $isArchived,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ]
        ]);
    }

    /**
     * Display archived coaches (convenience method for named route)
     */
    public function archived(Request $request)
    {
        return $this->index($request->merge(['archived' => true]));
    }

    /**
     * Show the form for creating a new coach.
     */
    public function create()
    {
        // Only admins can create coaches
        $this->authorize('create', User::class);
        
        return Inertia::render('Tenant/admin/coaches/CreateCoach');
    }

    /**
     * Store a newly created coach in storage.
     */
    public function store(Request $request)
    {
        // Only admins can create coaches
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:255',
            'send_invitation' => 'nullable|boolean',
        ]);

        $coach = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make(Str::random(32)), // temporary password
            'status' => UserRegistrationStatus::PENDING, // explicitly set to pending
            'email_verified_at' => null, // when they actually come through our registration, we'll tag this
            'archived' => false,
        ]);

        $coach->assignRole(UserRole::COACH);

        // Send invitation email if requested
        if ($request->boolean('send_invitation')) {
            $coach->notify(new SendCoachInvitation($coach));
        }

        return to_route('tenant.admin.coaches.show', $coach);
    }

    /**
     * Display the specified coach.
     */
    public function show(User $coach)
    {
        // Only admins can view coach details
        $this->authorize('view', $coach);
        
        // Load the coach with assigned clients and their count
        $coach->load(['assignedClients' => function($query) {
            $query->where('archived', false)->orderBy('name');
        }]);
        
        // Get some basic stats for the coach
        $stats = [
            'total_clients' => $coach->assignedClients()->count(),
            'active_clients' => $coach->assignedClients()->where('archived', false)->count(),
            'archived_clients' => $coach->assignedClients()->where('archived', true)->count(),
            'total_sessions' => $coach->coachSessions()->count(),
            'upcoming_sessions' => $coach->coachSessions()->where('scheduled_at', '>', now())->count(),
        ];
        
        return Inertia::render('Tenant/admin/coaches/ShowCoach', [
            'coach' => $coach,
            'stats' => $stats,
            'can' => [
                'update' => auth()->user()->can('update', $coach),
                'delete' => auth()->user()->can('delete', $coach),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified coach.
     */
    public function edit(User $coach)
    {
        // Only admins can edit coaches
        $this->authorize('update', $coach);
        
        return Inertia::render('Tenant/admin/coaches/EditCoach', [
            'coach' => $coach
        ]);
    }

    /**
     * Update the specified coach in storage.
     */
    public function update(Request $request, User $coach)
    {
        // Only admins can update coaches
        $this->authorize('update', $coach);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $coach->id,
            'phone' => 'nullable|string|max:255',
        ]);

        // Update coach fields
        $coach->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return to_route('tenant.admin.coaches.show', $coach)->with('success', 'Coach updated successfully.');
    }

    /**
     * Archive the specified coach.
     */
    public function archive(User $coach)
    {
        // Only admins can archive coaches
        $this->authorize('update', $coach);
        
        // Update the archived status
        $coach->update(['archived' => true]);
        
        return to_route('tenant.admin.coaches.show', $coach)->with('success', 'Coach archived successfully.');
    }

    /**
     * Archive batch of coaches
     */
    public function archiveBatch(Request $request)
    {
        $request->validate([
            'coaches' => 'required|array',
            'coaches.*' => 'exists:users,id',
        ]);
        
        $coaches = User::whereIn('id', $request->coaches)->get();

        foreach ($coaches as $coach) {
            // Authorize each coach individually
            $this->authorize('update', $coach);
            $coach->update(['archived' => true]);
        }
        
        return to_route('tenant.admin.coaches.index')->with('success', 'Coaches archived successfully.');
    }

    /**
     * Unarchive the specified coach.
     */
    public function unarchive(User $coach)
    {
        // Only admins can unarchive coaches
        $this->authorize('update', $coach);
        
        // Update the archived status
        $coach->update(['archived' => false]);
        
        return to_route('tenant.admin.coaches.show', $coach)->with('success', 'Coach unarchived successfully.');
    }

    /**
     * Unarchive batch of coaches
     */
    public function unarchiveBatch(Request $request)
    {
        $request->validate([
            'coaches' => 'required|array',
            'coaches.*' => 'exists:users,id',
        ]);
        
        $coaches = User::whereIn('id', $request->coaches)->get();

        foreach ($coaches as $coach) {
            // Authorize each coach individually
            $this->authorize('update', $coach);
            $coach->update(['archived' => false]);
        }
        
        return to_route('tenant.admin.coaches.archived')->with('success', 'Coaches unarchived successfully.');
    }

    /**
     * Send invitation email to an existing coach.
     */
    public function sendInvitation(User $coach)
    {
        // Only admins can send invitations
        $this->authorize('update', $coach);
        
        // Send the invitation email
        $coach->notify(new SendCoachInvitation($coach));
        
        return back()->with('success', 'Invitation email sent successfully.');
    }

    /**
     * Remove the specified coach from storage (soft delete).
     */
    public function destroy(User $coach)
    {
        // Only admins can delete coaches (includes archived check in policy)
        $this->authorize('delete', $coach);
        
        // Soft delete the coach model
        $coach->delete();
        
        return to_route('tenant.admin.coaches.archived')->with('success', 'Coach deleted successfully.');
    }

    /**
     * Delete batch of coaches
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'coaches' => 'required|array',
            'coaches.*' => 'exists:users,id',
        ]);
        
        $coaches = User::whereIn('id', $request->coaches)->get();
        
        foreach ($coaches as $coach) {
            // Authorize each coach individually
            $this->authorize('delete', $coach);
            $coach->delete();
        }
        
        return to_route('tenant.admin.coaches.archived')->with('success', 'Coaches deleted successfully.');
    }

    /**
     * Restore a soft-deleted coach.
     */
    public function restore($id)
    {
        $coach = User::withTrashed()->findOrFail($id);
        
        // Only admins can restore coaches (use same permissions as update)
        $this->authorize('update', $coach);
        
        $coach->restore();
        
        return to_route('tenant.admin.coaches.show', $coach)->with('success', 'Coach restored successfully.');
    }
    
    /**
     * Permanently delete a coach (force delete).
     */
    public function forceDelete($id)
    {
        $coach = User::withTrashed()->findOrFail($id);
        
        // Only admins can permanently delete coaches
        $this->authorize('delete', $coach);
        
        // Force delete permanently removes the record
        $coach->forceDelete();
        
        return to_route('tenant.admin.coaches.index')->with('success', 'Coach permanently deleted.');
    }
}
