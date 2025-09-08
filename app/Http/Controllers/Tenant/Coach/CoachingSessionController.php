<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) //upcoming by default
    {
        return $this->getSessions($request, true);
    }

    public function past(Request $request)
    {
        return $this->getSessions($request, false);
    }

    public function calendar(Request $request)
    {
        return $this->getCalendarSessions($request);
    }

    private function getSessions(Request $request, bool $upcoming = true)
    {
        $query = CoachingSession::with(['client', 'coach'])
            ->when($upcoming, 
                fn($q) => $q->where('end_at', '>', now()),
                fn($q) => $q->where('end_at', '<', now())
            );
        
        // Scope sessions based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches (not admins) can only see their sessions
            $query->where('coach_id', auth()->id());
        }
        // Admins can see all sessions (no additional filtering needed)
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->whereHas('client', function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Client filter
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }
        
        // Session type filter
        if ($request->has('session_type') && $request->session_type) {
            $query->where('session_type', $request->session_type);
        }
        
        // Attendance filter
        if ($request->has('client_attended') && $request->client_attended !== null) {
            $query->where('client_attended', $request->boolean('client_attended'));
        }
        
        // Coach filter (only for admins)
        if ($request->has('coach_id') && $request->coach_id && auth()->user()->hasRole('admin')) {
            $query->where('coach_id', $request->coach_id);
        }
        

        
        // Sorting functionality
        $sortBy = $request->get('sort_by', 'scheduled_at');
        $sortDirection = $request->get('sort_direction', $upcoming ? 'asc' : 'desc');
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = $upcoming ? 'asc' : 'desc';
        }
        
        // Handle different sort columns
        switch ($sortBy) {
            case 'client':
                $query->leftJoin('users as clients', 'coaching_sessions.client_id', '=', 'clients.id')
                      ->orderBy('clients.name', $sortDirection)
                      ->select('coaching_sessions.*');
                break;
            case 'coach':
                $query->leftJoin('users as coaches', 'coaching_sessions.coach_id', '=', 'coaches.id')
                      ->orderBy('coaches.name', $sortDirection)
                      ->select('coaching_sessions.*');
                break;
            case 'scheduled_at':
                $query->orderBy('coaching_sessions.scheduled_at', $sortDirection);
                break;
            case 'duration':
                $query->orderBy('coaching_sessions.duration', $sortDirection);
                break;
            case 'session_type':
                $query->orderBy('coaching_sessions.session_type', $sortDirection);
                break;
            case 'client_attended':
                $query->orderBy('coaching_sessions.client_attended', $sortDirection);
                break;
            case 'session_number':
            default:
                // For session number, we'll order by scheduled_at as a proxy
                $query->orderBy('coaching_sessions.scheduled_at', $sortDirection);
                break;
        }
        
        // Table view always uses pagination
        $sessions = $query->paginate(config('app.pagination_limit'));
        
        // Get clients for the filter dropdown (scoped by role)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see their assigned clients
            $clients = User::role('client')
                ->where('assigned_coach_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            // Admins see all clients who have sessions
            $clients = User::role('client')
                ->whereHas('clientSessions')
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        
        // Get coaches for the filter dropdown (only for admins)
        $coaches = [];
        if (auth()->user()->hasRole('admin')) {
            $coaches = User::role('coach')
                ->whereHas('coachSessions') // Only coaches who have sessions
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        
        return Inertia::render("Tenant/coach/coaching-sessions/ListCoachingSessions", [
            'sessions' => $sessions,
            'clients' => $clients,
            'coaches' => $coaches,
            'filters' => [
                'search' => $request->search,
                'client_id' => $request->client_id,
                'coach_id' => $request->coach_id,
                'session_type' => $request->session_type,
                'client_attended' => $request->client_attended,
                'upcoming' => $upcoming,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'view' => 'table'
            ],
            'canSeeCoachColumn' => auth()->user()->hasRole('admin'),
            'view' => 'table'
        ]);
    }

    private function getCalendarSessions(Request $request)
    {
        $query = CoachingSession::with(['client', 'coach']);
        
        // Auto-apply date range - default to current 7-day period if no range provided
        if (!$request->has('date_from') || !$request->has('date_to')) {
            $today = now()->startOfDay();
            $endDate = $today->copy()->addDays(6)->endOfDay();
            
            $request->merge([
                'date_from' => $today->toDateString(),
                'date_to' => $endDate->toDateString()
            ]);
        }
        

        $dateFrom = now()->parse($request->date_from)->startOfDay();
        $dateTo = now()->parse($request->date_to)->endOfDay();
        $query->whereBetween('scheduled_at', [$dateFrom, $dateTo]);
        
        // Scope sessions based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            $query->where('coach_id', auth()->id());
        }
        
        // Apply filters (same as table view)
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->whereHas('client', function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }
        
        if ($request->has('session_type') && $request->session_type) {
            $query->where('session_type', $request->session_type);
        }
        
        if ($request->has('client_attended') && $request->client_attended !== null) {
            $query->where('client_attended', $request->boolean('client_attended'));
        }
        
        if ($request->has('coach_id') && $request->coach_id && auth()->user()->hasRole('admin')) {
            $query->where('coach_id', $request->coach_id);
        }
        
        // Calendar view: get all sessions in date range (no pagination)
        $sessions = $query->orderBy('scheduled_at', 'asc')->get();
        
        // Get filter options (same as table view)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            $clients = User::role('client')
                ->where('assigned_coach_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $clients = User::role('client')
                ->whereHas('clientSessions')
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        
        $coaches = [];
        if (auth()->user()->hasRole('admin')) {
            $coaches = User::role('coach')
                ->whereHas('coachSessions')
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        
        // Format as a paginated response for consistency with frontend
        $perPage = max($sessions->count(), 1); // Prevent division by zero
        $paginatedSessions = new \Illuminate\Pagination\LengthAwarePaginator(
            $sessions,
            $sessions->count(),
            $perPage,
            1,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return Inertia::render("Tenant/coach/coaching-sessions/ListCoachingSessions", [
            'sessions' => $paginatedSessions,
            'clients' => $clients,
            'coaches' => $coaches,
            'filters' => [
                'search' => $request->search,
                'client_id' => $request->client_id,
                'coach_id' => $request->coach_id,
                'session_type' => $request->session_type,
                'client_attended' => $request->client_attended,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'view' => 'calendar'
            ],
            'canSeeCoachColumn' => auth()->user()->hasRole('admin'),
            'view' => 'calendar'
        ]);
    }

    /**
     * Update attendance for multiple sessions
     */
    public function updateAttendance(Request $request)
    {
        $request->validate([
            'sessions' => 'required|array',
            'sessions.*' => 'exists:coaching_sessions,id',
            'attended' => 'required|boolean',
        ]);
        
        $sessions = CoachingSession::whereIn('id', $request->sessions)->get();
        
        foreach ($sessions as $session) {
            // Authorize each session individually (coaches can only update their own sessions)
            if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
                if ($session->coach_id !== auth()->id()) {
                    abort(403, 'Unauthorized to update this session');
                }
            }
            
            $session->update(['client_attended' => $request->attended]);
        }
        
        return back()->with('success', 'Session attendance updated successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get clients for the dropdown (scoped by role)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see their assigned clients
            $clients = User::role('client')
                ->where('assigned_coach_id', auth()->id())
                ->where('archived', false)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } else {
            // Admins see all active clients
            $clients = User::role('client')
                ->where('archived', false)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }
        
        return Inertia::render('Tenant/coach/coaching-sessions/ScheduleSession', [
            'clients' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:15|max:480', // 15 minutes to 8 hours
            'session_type' => 'required|in:in_person,online,hybrid',
        ]);

        // Combine date and time - Laravel will handle timezone conversion based on app config
        $scheduledAt = \Carbon\Carbon::createFromFormat(
            'Y-m-d H:i', 
            $request->scheduled_date . ' ' . $request->scheduled_time
        );
        $startAt = $scheduledAt; // The planned start time (same as scheduled)
        
        // Calculate the planned end time based on duration
        $endAt = $scheduledAt->copy()->addMinutes((int) $request->duration);

        // Determine coach - if admin, they can schedule for any coach, otherwise it's themselves
        $coachId = auth()->id();
        
        // Verify the client is assigned to this coach (unless admin)
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            $client = User::findOrFail($request->client_id);
            if ($client->assigned_coach_id !== auth()->id()) {
                abort(403, 'You can only schedule sessions for your assigned clients.');
            }
        }

        $session = CoachingSession::create([
            'client_id' => $request->client_id,
            'coach_id' => $coachId,
            'scheduled_at' => $scheduledAt,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration' => $request->duration,
            'session_type' => $request->session_type,
            'client_attended' => true, // Default to true, assuming attendance
        ]);

        return to_route('tenant.coach.coaching-sessions.show', $session)
            ->with('success', 'Session scheduled successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CoachingSession $coachingSession)
    {
        // Load relationships
        $coachingSession->load(['client', 'coach']);
        
        // Authorization: coaches can only view their own sessions unless admin
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            if ($coachingSession->coach_id !== auth()->id()) {
                abort(403, 'Unauthorized to view this session');
            }
        }
        
        return Inertia::render('Tenant/coach/coaching-session/SessionShow', [
            'session' => $coachingSession,
            'can' => [
                'update' => auth()->user()->hasRole('admin') || 
                          (auth()->user()->hasRole('coach') && $coachingSession->coach_id === auth()->id()),
                'delete' => auth()->user()->hasRole('admin') || 
                          (auth()->user()->hasRole('coach') && $coachingSession->coach_id === auth()->id()),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoachingSession $coachingSession)
    {
        // Load relationships
        $coachingSession->load(['client', 'coach']);
        
        // Authorization: coaches can only edit their own sessions unless admin
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            if ($coachingSession->coach_id !== auth()->id()) {
                abort(403, 'Unauthorized to edit this session');
            }
        }
        
        return Inertia::render('Tenant/coach/coaching-session/SessionEdit', [
            'session' => $coachingSession
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoachingSession $coachingSession)
    {
        // Authorization: coaches can only update their own sessions unless admin
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            if ($coachingSession->coach_id !== auth()->id()) {
                abort(403, 'Unauthorized to update this session');
            }
        }
        
        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:15|max:480', // 15 minutes to 8 hours
            'session_type' => 'required|in:in_person,online,hybrid',
            'client_attended' => 'sometimes|boolean',
        ]);

        // Combine date and time
        $scheduledAt = \Carbon\Carbon::createFromFormat(
            'Y-m-d H:i', 
            $request->scheduled_date . ' ' . $request->scheduled_time
        );
        $startAt = $scheduledAt; // The planned start time (same as scheduled)
        
        // Calculate the planned end time based on duration
        $endAt = $scheduledAt->copy()->addMinutes((int) $request->duration);

        // Prepare update data
        $updateData = [
            'scheduled_at' => $scheduledAt,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration' => $request->duration,
            'session_type' => $request->session_type,
        ];
        
        // Only update attendance if it's provided (for past sessions)
        if ($request->has('client_attended')) {
            $updateData['client_attended'] = $request->boolean('client_attended');
        }
        
        $coachingSession->update($updateData);

        return to_route('tenant.coach.coaching-sessions.show', $coachingSession)
            ->with('success', 'Session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoachingSession $coachingSession)
    {
        // Authorization: coaches can only delete their own sessions unless admin
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            if ($coachingSession->coach_id !== auth()->id()) {
                abort(403, 'Unauthorized to delete this session');
            }
        }
        
        // Delete the coaching session
        $coachingSession->delete();
        
        return to_route('tenant.coach.coaching-sessions.index')->with('success', 'Session deleted successfully.');
    }

    /**
     * Delete batch of coaching sessions
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'sessions' => 'required|array',
            'sessions.*' => 'exists:coaching_sessions,id',
        ]);
        
        $coachingSessions = CoachingSession::whereIn('id', $request->sessions)->get();
        
        foreach ($coachingSessions as $coachingSession) {
            // Authorization: coaches can only delete their own sessions unless admin
            if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
                if ($coachingSession->coach_id !== auth()->id()) {
                    abort(403, 'Unauthorized to delete this session');
                }
            }
            $coachingSession->delete();
        }
        
        return back()->with('success', 'Sessions deleted successfully.');
    }  
}
