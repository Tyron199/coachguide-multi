<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SendAdminInvitation;
use App\Notifications\Tenant\AdminRoleAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Enums\Tenant\UserRole;
use App\Enums\Tenant\UserRegistrationStatus;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Display a listing of the administrators.
     */
    public function index(Request $request)
    {
        // Only admins can manage other admins
        $this->authorize('viewAny', User::class);
        
        $isArchived = $request->boolean('archived', false);
        
        $query = User::role(UserRole::ADMIN)
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
            case 'created_at':
            default:
                $query->orderBy('users.created_at', $sortDirection);
                break;
        }
        
        $admins = $query->paginate(config('app.pagination_limit'));
        
        return Inertia::render("Tenant/admin/administrators/ListAdmins", [
            'administrators' => $admins,
            'filters' => [
                'search' => $request->search,
                'archived' => $isArchived,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ],
            'currentUserId' => auth()->id() // For preventing self-deletion in UI
        ]);
    }

    /**
     * Display archived administrators (convenience method for named route)
     */
    public function archived(Request $request)
    {
        return $this->index($request->merge(['archived' => true]));
    }

    /**
     * Show the form for creating a new administrator.
     */
    public function create()
    {
        // Only admins can create other admins
        $this->authorize('create', User::class);
        
        return Inertia::render('Tenant/admin/administrators/CreateAdmin');
    }

    /**
     * Store a newly created administrator in storage.
     */
    public function store(Request $request)
    {
        // Only admins can create other admins
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:255',
            'send_invitation' => 'nullable|boolean',
        ]);

        // Check if user with this email already exists
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // If user already has admin role, return error
            if ($existingUser->hasRole(UserRole::ADMIN)) {
                return back()->withErrors([
                    'email' => 'An administrator with this email address already exists.'
                ])->withInput();
            }
            
            // If user exists but doesn't have admin role, add the admin role
            $existingUser->assignRole(UserRole::ADMIN);
            
            // Update other fields if provided
            $existingUser->update([
                'name' => $request->name,
                'phone' => $request->phone ?: $existingUser->phone,
            ]);
            
            // Send appropriate notification if requested
            if ($request->boolean('send_invitation')) {
                // If user is already active (not pending), send role addition notification
                if ($existingUser->status !== UserRegistrationStatus::PENDING) {
                    $existingUser->notify(new AdminRoleAdded($existingUser));
                } else {
                    // If user is still pending, send regular invitation
                    $existingUser->notify(new SendAdminInvitation($existingUser));
                }
            }
            
            return to_route('tenant.admin.administrators.show', $existingUser)
                ->with('success', 'Administrator role added to existing user successfully.');
        }
        
        // If user doesn't exist, create new user
        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make(Str::random(32)), // temporary password
            'status' => UserRegistrationStatus::PENDING, // explicitly set to pending
            'email_verified_at' => null, // when they actually come through our registration, we'll tag this
            'archived' => false,
        ]);

        $admin->assignRole(UserRole::ADMIN);

        // Send invitation email if requested
        if ($request->boolean('send_invitation')) {
            $admin->notify(new SendAdminInvitation($admin));
        }

        return to_route('tenant.admin.administrators.show', $admin);
    }

    /**
     * Display the specified administrator.
     */
    public function show(User $admin)
    {
        // Only admins can view other admin details
        $this->authorize('view', $admin);
        
        // Get platform statistics for this admin view
        $stats = [
            'total_coaches' => User::role(UserRole::COACH)->count(),
            'active_coaches' => User::role(UserRole::COACH)->where('archived', false)->count(),
            'total_clients' => User::role(UserRole::CLIENT)->count(),
            'active_clients' => User::role(UserRole::CLIENT)->where('archived', false)->count(),
            'total_admins' => User::role(UserRole::ADMIN)->count(),
            'active_admins' => User::role(UserRole::ADMIN)->where('archived', false)->count(),
        ];
        
        return Inertia::render('Tenant/admin/administrators/ShowAdmin', [
            'admin' => $admin,
            'stats' => $stats,
            'can' => [
                'update' => auth()->user()->can('update', $admin),
                'delete' => auth()->user()->can('delete', $admin) && auth()->id() !== $admin->id, // Prevent self-deletion
            ],
            'isSelf' => auth()->id() === $admin->id
        ]);
    }

    /**
     * Show the form for editing the specified administrator.
     */
    public function edit(User $admin)
    {
        // Only admins can edit other admins
        $this->authorize('update', $admin);
        
        return Inertia::render('Tenant/admin/administrators/EditAdmin', [
            'admin' => $admin,
            'isSelf' => auth()->id() === $admin->id
        ]);
    }

    /**
     * Update the specified administrator in storage.
     */
    public function update(Request $request, User $admin)
    {
        // Only admins can update other admins
        $this->authorize('update', $admin);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:255',
        ]);

        // Update admin fields
        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return to_route('tenant.admin.administrators.show', $admin)->with('success', 'Administrator updated successfully.');
    }

    /**
     * Archive the specified administrator.
     */
    public function archive(User $admin)
    {
        // Only admins can archive other admins, but not themselves
        $this->authorize('update', $admin);
        
        // Prevent self-archiving
        if (auth()->id() === $admin->id) {
            return back()->withErrors(['error' => 'You cannot archive yourself.']);
        }
        
        // Update the archived status
        $admin->update(['archived' => true]);
        
        return to_route('tenant.admin.administrators.show', $admin)->with('success', 'Administrator archived successfully.');
    }

    /**
     * Archive batch of administrators
     */
    public function archiveBatch(Request $request)
    {
        $request->validate([
            'admins' => 'required|array',
            'admins.*' => 'exists:users,id',
        ]);
        
        $admins = User::whereIn('id', $request->admins)->get();
        $currentUserId = auth()->id();

        foreach ($admins as $admin) {
            // Skip self-archiving
            if ($admin->id === $currentUserId) {
                continue;
            }
            
            // Authorize each admin individually
            $this->authorize('update', $admin);
            $admin->update(['archived' => true]);
        }
        
        return to_route('tenant.admin.administrators.index')->with('success', 'Administrators archived successfully.');
    }

    /**
     * Unarchive the specified administrator.
     */
    public function unarchive(User $admin)
    {
        // Only admins can unarchive other admins
        $this->authorize('update', $admin);
        
        // Update the archived status
        $admin->update(['archived' => false]);
        
        return to_route('tenant.admin.administrators.show', $admin)->with('success', 'Administrator unarchived successfully.');
    }

    /**
     * Unarchive batch of administrators
     */
    public function unarchiveBatch(Request $request)
    {
        $request->validate([
            'admins' => 'required|array',
            'admins.*' => 'exists:users,id',
        ]);
        
        $admins = User::whereIn('id', $request->admins)->get();

        foreach ($admins as $admin) {
            // Authorize each admin individually
            $this->authorize('update', $admin);
            $admin->update(['archived' => false]);
        }
        
        return to_route('tenant.admin.administrators.archived')->with('success', 'Administrators unarchived successfully.');
    }

    /**
     * Send invitation email to an existing administrator.
     */
    public function sendInvitation(User $admin)
    {
        // Only admins can send invitations
        $this->authorize('update', $admin);
        
        // Send the invitation email
        $admin->notify(new SendAdminInvitation($admin));
        
        return back()->with('success', 'Invitation email sent successfully.');
    }

    /**
     * Remove the specified administrator from storage (soft delete).
     */
    public function destroy(User $admin)
    {
        // Only admins can delete other admins (includes archived check in policy)
        $this->authorize('delete', $admin);
        
        // Prevent self-deletion
        if (auth()->id() === $admin->id) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }
        
        // Soft delete the admin model
        $admin->delete();
        
        return to_route('tenant.admin.administrators.archived')->with('success', 'Administrator deleted successfully.');
    }

    /**
     * Delete batch of administrators
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'admins' => 'required|array',
            'admins.*' => 'exists:users,id',
        ]);
        
        $admins = User::whereIn('id', $request->admins)->get();
        $currentUserId = auth()->id();
        
        foreach ($admins as $admin) {
            // Skip self-deletion
            if ($admin->id === $currentUserId) {
                continue;
            }
            
            // Authorize each admin individually
            $this->authorize('delete', $admin);
            $admin->delete();
        }
        
        return to_route('tenant.admin.administrators.archived')->with('success', 'Administrators deleted successfully.');
    }

    /**
     * Restore a soft-deleted administrator.
     */
    public function restore($id)
    {
        $admin = User::withTrashed()->findOrFail($id);
        
        // Only admins can restore other admins (use same permissions as update)
        $this->authorize('update', $admin);
        
        $admin->restore();
        
        return to_route('tenant.admin.administrators.show', $admin)->with('success', 'Administrator restored successfully.');
    }
    
    /**
     * Permanently delete an administrator (force delete).
     */
    public function forceDelete($id)
    {
        $admin = User::withTrashed()->findOrFail($id);
        
        // Only admins can permanently delete other admins
        $this->authorize('delete', $admin);
        
        // Prevent self-deletion
        if (auth()->id() === $admin->id) {
            return back()->withErrors(['error' => 'You cannot permanently delete yourself.']);
        }
        
        // Force delete permanently removes the record
        $admin->forceDelete();
        
        return to_route('tenant.admin.administrators.index')->with('success', 'Administrator permanently deleted.');
    }
}
