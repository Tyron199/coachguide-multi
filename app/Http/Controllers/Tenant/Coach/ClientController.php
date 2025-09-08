<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use App\Notifications\Tenant\SendClientInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Enums\Tenant\UserRole;
use App\Enums\Tenant\UserRegistrationStatus;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Authorize viewing clients list
        $this->authorize('viewAny', User::class);
        
        $isArchived = $request->boolean('archived', false);
        
        $query = User::role(UserRole::CLIENT)
            ->with(['company', 'assignedCoach'])
            ->when($isArchived, 
                fn($q) => $q->where('archived', true),
                fn($q) => $q->where('archived', false)
            );
        
        // Scope clients based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches (not admins) can only see their assigned clients
            $query->where('assigned_coach_id', auth()->id());
        }
        // Admins can see all clients (no additional filtering needed)
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }
        
        // No company filter
        if ($request->boolean('no_company', false)) {
            $query->whereNull('company_id');
        }
        
        // Coach filter
        if ($request->has('coach_id') && $request->coach_id) {
            $query->where('assigned_coach_id', $request->coach_id);
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
            case 'company':
                $query->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                      ->orderBy('companies.name', $sortDirection)
                      ->select('users.*'); // Ensure we only select user columns
                break;
            case 'coach':
                $query->leftJoin('users as coaches', 'users.assigned_coach_id', '=', 'coaches.id')
                      ->orderBy('coaches.name', $sortDirection)
                      ->select('users.*'); // Ensure we only select user columns
                break;
            case 'created_at':
            default:
                $query->orderBy('users.created_at', $sortDirection);
                break;
        }
        
        $clients = $query->paginate(config('app.pagination_limit'));
        
        // Get companies for the filter dropdown (scoped by role)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see companies that have clients assigned to them
            $companies = Company::whereHas('users', function($q) {
                $q->where('assigned_coach_id', auth()->id())
                  ->role(UserRole::CLIENT);
            })->orderBy('name')->get(['id', 'name']);
        } else {
            // Admins see all companies
            $companies = Company::orderBy('name')->get(['id', 'name']);
        }
        
        // Get coaches for the filter dropdown (only for admins)
        $coaches = [];
        if (auth()->user()->hasRole('admin')) {
            $coaches = User::role('coach')
                ->whereHas('assignedClients') // Only coaches who have assigned clients
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        
    
        
        return Inertia::render("Tenant/coach/clients/ListClients", [
            'clients' => $clients,
            'companies' => $companies,
            'coaches' => $coaches,
            'filters' => [
                'search' => $request->search,
                'company_id' => $request->company_id,
                'no_company' => $request->boolean('no_company', false),
                'coach_id' => $request->coach_id,
                'archived' => $isArchived,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ],
            'canSeeCoachColumn' => auth()->user()->hasRole('admin')
        ]);
    }

    /**
     * Display archived clients (convenience method for named route)
     */
    public function archived(Request $request)
    {
        return $this->index($request->merge(['archived' => true]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Authorize client creation
        $this->authorize('create', User::class);
        
        // Get companies for the dropdown (scoped by role)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see companies that have clients assigned to them (or all companies if they have no clients yet)
            $companies = Company::whereHas('users', function($q) {
                $q->where('assigned_coach_id', auth()->id())
                  ->role(UserRole::CLIENT);
            })->orWhereDoesntHave('users', function($q) {
                $q->role(UserRole::CLIENT);
            })->orderBy('name')->get(['id', 'name']);
        } else {
            // Admins see all companies
            $companies = Company::orderBy('name')->get(['id', 'name']);
        }
        
        // Get available coaches (admin only)
        $coaches = [];
        if (auth()->user()->hasRole('admin')) {
            $coaches = User::role('coach')->orderBy('name')->get(['id', 'name']);
        }
        
        return Inertia::render('Tenant/coach/clients/CreateClient', [
            'companies' => $companies,
            'coaches' => $coaches,
            'canAssignCoach' => auth()->user()->hasRole('admin')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Authorize client creation
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'new_company_name' => 'required_if:company_id,new|nullable|string|max:255|unique:companies,name',
            'company_id' => 'nullable|exists:companies,id',
            'assigned_coach_id' => 'nullable|exists:users,id',
            'send_invitation' => 'nullable|boolean',
        ]);

        //So if new company name then we need to create a new company.
        if ($request->new_company_name) {
            $company = Company::create([
                'name' => $request->new_company_name,
            ]);
        }else{
                $company = Company::find($request->company_id);
        }

        // Determine coach assignment
        $assignedCoachId = $request->assigned_coach_id ?? auth()->id();
        
        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'company_id' => $company->id ?? null,
            'assigned_coach_id' => $assignedCoachId,
            'password' => Hash::make(Str::random(32)), // temporary password
            'status' => UserRegistrationStatus::PENDING, // explicitly set to pending
            'email_verified_at' => null, // when they actually come through our registration, we'll tag this. Because the system is by invitation only.
        ]);

        $client->assignRole(UserRole::CLIENT);

        // Send invitation email if requested
        if ($request->boolean('send_invitation')) {
            $client->notify(new SendClientInvitation($client));
        }

        return to_route('tenant.coach.clients.show', $client);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $client)
    {
        // Authorize access to this client
        $this->authorize('view', $client);
        
        // Laravel automatically injects the User model based on the route parameter
        // Load the client with company, profile, and assigned coach relationships
        $client->load(['company', 'profile', 'assignedCoach']);
        
        return Inertia::render('Tenant/coach/client/ClientShow', [
            'client' => $client,
            'can' => [
                'update' => auth()->user()->can('update', $client),
                'delete' => auth()->user()->can('delete', $client),
            ]
        ]);
    }

    /**
     * Display the client's objectives and goals.
     */
    public function objectives(User $client)
    {
        // Authorize access to this client
        $this->authorize('view', $client);
        
        // Load the client with profile relationship
        $client->load(['company', 'profile']);
        
        return Inertia::render('Tenant/coach/client/ClientObjectives', [
            'client' => $client
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Load the client with company, profile, and assigned coach relationships
        $client->load(['company', 'profile', 'assignedCoach']);
        
        // Get all companies for the dropdown
        $companies = Company::orderBy('name')->get(['id', 'name']);
        
        // Get available coaches (admin only)
        $coaches = [];
        if (auth()->user()->hasRole('admin')) {
            $coaches = User::role('coach')->orderBy('name')->get(['id', 'name']);
        }
        
        return Inertia::render('Tenant/coach/client/ClientEdit', [
            'client' => $client,
            'companies' => $companies,
            'coaches' => $coaches,
            'canAssignCoach' => auth()->user()->hasRole('admin')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->id,
            'phone' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'assigned_coach_id' => 'nullable|exists:users,id',
            
            // Profile fields
            'address' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'medical_conditions' => 'nullable|array',
            'medical_conditions.*' => 'string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'preferred_method_of_communication' => 'nullable|in:email,phone,text',
        ]);

        // Prepare user update data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_id' => $request->company_id,
        ];
        
        // Only admins can update coach assignment
        if (auth()->user()->hasRole('admin') && $request->has('assigned_coach_id')) {
            $userData['assigned_coach_id'] = $request->assigned_coach_id;
        }
        
        // Update user fields
        $client->update($userData);

        // Update or create profile
        $profileData = [
            'address' => $request->address,
            'birthdate' => $request->birthdate,
            'medical_conditions' => $request->medical_conditions ?? [],
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'preferred_method_of_communication' => $request->preferred_method_of_communication,
        ];

        if ($client->profile) {
            $client->profile->update($profileData);
        } else {
            $client->profile()->create($profileData);
        }

        return to_route('tenant.coach.clients.show', $client)->with('success', 'Client updated successfully.');
    }

    /**
     * Show the form for editing client objectives.
     */
    public function editObjectives(User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Load the client with profile relationship
        $client->load(['company', 'profile']);
        
        return Inertia::render('Tenant/coach/client/ClientEditObjectives', [
            'client' => $client
        ]);
    }

    /**
     * Update client objectives.
     */
    public function updateObjectives(Request $request, User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Debug logging
        Log::info('UpdateObjectives Request Data:', $request->all());
        
        try {
            $request->validate([
                'goal_summary' => 'nullable|string',
                'objectives' => 'nullable|string',
                'focus_areas' => 'nullable|array',
                'focus_areas.*' => 'string|in:Confidence & Self-Belief,Leadership Development,Communication Skills,Emotional Intelligence,Career Direction / Change,Work-Life Integration,Team Relationships,Decision-Making,Stress Management,Sleep Quality,Mental Health,Flexibility',
            ]);
            Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            throw $e;
        }

        // Update or create profile
        $profileData = [
            'goal_summary' => $request->goal_summary,
            'objectives' => $request->objectives,
            'focus_areas' => $request->focus_areas ?? [],
        ];

        Log::info('Profile Data to Update:', $profileData);

        if ($client->profile) {
            $client->profile->update($profileData);
            Log::info('Updated existing profile for client:', ['client_id' => $client->id]);
        } else {
            $client->profile()->create($profileData);
            Log::info('Created new profile for client:', ['client_id' => $client->id]);
        }

        return to_route('tenant.coach.clients.objectives', $client)->with('success', 'Objectives updated successfully.');
    }

    /**
     * Archive the specified client.
     */
    public function archive(User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Update the archived status
        $client->update(['archived' => true]);
        
        return to_route('tenant.coach.clients.show', $client)->with('success', 'Client archived successfully.');
    }

    /**
     * Archive batch of clients
     */
    public function archiveBatch(Request $request)
    {
        $request->validate([
            'clients' => 'required|array',
            'clients.*' => 'exists:users,id',
        ]);
        
        $clients = User::whereIn('id', $request->clients)->get();

        foreach ($clients as $client) {
            // Authorize each client individually
            $this->authorize('update', $client);
            $client->update(['archived' => true]);
        }
        
        return to_route('tenant.coach.clients.index')->with('success', 'Clients archived successfully.');
    }

    /**
     * Unarchive the specified client.
     */
    public function unarchive(User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Update the archived status
        $client->update(['archived' => false]);
        
        return to_route('tenant.coach.clients.show', $client)->with('success', 'Client unarchived successfully.');
    }

    /**
     * Unarchive batch of clients
     */
    public function unarchiveBatch(Request $request)
    {
        $request->validate([
            'clients' => 'required|array',
            'clients.*' => 'exists:users,id',
        ]);
        
        $clients = User::whereIn('id', $request->clients)->get();

        foreach ($clients as $client) {
            // Authorize each client individually
            $this->authorize('update', $client);
            $client->update(['archived' => false]);
        }
        
        return to_route('tenant.coach.clients.archived')->with('success', 'Clients unarchived successfully.');
    }   

    /**
     * Send invitation email to an existing client.
     */
    public function sendInvitation(User $client)
    {
        // Authorize access to this client
        $this->authorize('update', $client);
        
        // Send the invitation email
        $client->notify(new SendClientInvitation($client));
        
        return back()->with('success', 'Invitation email sent successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(User $client)
    {
        // Authorize deletion of this client (includes archived check in policy)
        $this->authorize('delete', $client);
        
        // Soft delete the client model
        $client->delete();
        
        return to_route('tenant.coach.clients.archived')->with('success', 'Client deleted successfully.');
    }

    /**
     * Delete batch of clients
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'clients' => 'required|array',
            'clients.*' => 'exists:users,id',
        ]);
        
        $clients = User::whereIn('id', $request->clients)->get();
        
        foreach ($clients as $client) {
            // Authorize each client individually
            $this->authorize('delete', $client);
            $client->delete();
        }
        
        return to_route('tenant.coach.clients.archived')->with('success', 'Clients deleted successfully.');
    }   
    

    /**
     * Restore a soft-deleted client.
     */
    public function restore($id)
    {
        $client = User::withTrashed()->findOrFail($id);
        
        // Authorize restoration (use same permissions as update)
        $this->authorize('update', $client);
        
        $client->restore();
        
        return to_route('tenant.coach.clients.show', $client)->with('success', 'Client restored successfully.');
    }
    
    /**
     * Permanently delete a client (force delete).
     */
    public function forceDelete($id)
    {
        $client = User::withTrashed()->findOrFail($id);
        
        // Authorize permanent deletion (use delete permission)
        $this->authorize('delete', $client);
        
        // Force delete permanently removes the record
        $client->forceDelete();
        
        return to_route('tenant.coach.clients.index')->with('success', 'Client permanently deleted.');
    }
}
