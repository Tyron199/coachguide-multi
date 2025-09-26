<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingFramework;
use App\Models\Tenant\CoachingFrameworkInstance;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingFrameworkController extends Controller
{
    /**
     * Display a listing of all frameworks
     */
    public function index(Request $request)
    {
        return $this->getFrameworks($request);
    }

    /**
     * Display only coaching models
     */
    public function models(Request $request)
    {
        return $this->getFrameworks($request->merge(['category' => 'models']));
    }

    /**
     * Display only coaching tools
     */
    public function tools(Request $request)
    {
        return $this->getFrameworks($request->merge(['category' => 'tools']));
    }

    /**
     * Display only profiling frameworks
     */
    public function profiling(Request $request)
    {
        return Inertia::render('Tenant/coach/coaching-frameworks/Profiling');
    }
    /**
     * Get frameworks with filtering and search
     */
    private function getFrameworks(Request $request)
    {
        $query = CoachingFramework::active();

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Subcategory filter
        if ($request->has('subcategory') && $request->subcategory) {
            $query->where('subcategory', $request->subcategory);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Best for filter
        if ($request->has('best_for') && $request->best_for) {
            $query->whereJsonContains('best_for', $request->best_for);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'category':
                $query->orderBy('category', $sortDirection);
                break;
            case 'subcategory':
                $query->orderBy('subcategory', $sortDirection);
                break;
            case 'usage_count':
                $query->withCount('instances')
                      ->orderBy('instances_count', $sortDirection);
                break;
            default:
                $query->orderBy('name', $sortDirection);
                break;
        }

        $frameworks = $query->paginate(config('app.pagination_limit'));

        // Get filter options
        $subcategories = CoachingFramework::active()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->distinct()
            ->pluck('subcategory')
            ->filter()
            ->sort()
            ->values();

        $bestForOptions = CoachingFramework::active()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->get()
            ->pluck('best_for')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('Tenant/coach/coaching-frameworks/Index', [
            'frameworks' => $frameworks,
            'subcategories' => $subcategories,
            'bestForOptions' => $bestForOptions,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'best_for' => $request->best_for,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'currentCategory' => $request->category, // For sidebar highlighting
        ]);
    }

    /**
     * Display a specific framework (preview/read-only)
     */
    public function show(CoachingFramework $framework)
    {
        // Load usage statistics
        $framework->loadCount(['instances', 'instances as completed_instances_count' => function($q) {
            $q->whereNotNull('completed_at');
        }]);

        return Inertia::render('Tenant/coach/coaching-frameworks/Show', [
            'framework' => $framework,
            'can' => [
                'assign' => true, // All coaches can assign frameworks
            ]
        ]);
    }

    /**
     * Show assignment page for selecting client/session
     */
    public function showAssignment(Request $request, CoachingFramework $framework = null)
    {
        // Get pre-selection context
        $preSelectedFramework = $framework;
        $preSelectedClientId = $request->get('client_id');
        $preSelectedSessionId = $request->get('session_id');
        
        // Get available frameworks (if not pre-selected)
        $frameworks = $preSelectedFramework ? null : CoachingFramework::active()->orderBy('name')->get();
        
        // Get coach's clients
        $clients = User::role('client')
            ->where('assigned_coach_id', auth()->id())
            ->where('archived', false)
            ->whereHas('upcomingClientSessions')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        
        // If session is pre-selected, get its details
        $preSelectedSession = null;
        if ($preSelectedSessionId) {
            $preSelectedSession = CoachingSession::with('client')->find($preSelectedSessionId);
            if ($preSelectedSession) {
                $this->authorize('view', $preSelectedSession->client);
                $preSelectedClientId = $preSelectedSession->client_id;
            }
        }
        
        return Inertia::render('Tenant/coach/coaching-frameworks/Assign', [
            'frameworks' => $frameworks,
            'clients' => $clients,
            'preSelected' => [
                'framework' => $preSelectedFramework,
                'client_id' => $preSelectedClientId,
                'session_id' => $preSelectedSessionId,
                'session' => $preSelectedSession,
            ],
            
            // Sessions loaded only when client_id is provided (lazy + optional)
            'clientSessions' => Inertia::optional(fn() => 
                $request->get('client_id') 
                    ? CoachingSession::where('client_id', $request->get('client_id'))
                        ->where('scheduled_at', '>', now())
                        ->orderBy('scheduled_at', 'asc')
                        ->get(['id', 'client_id', 'scheduled_at', 'duration', 'session_type'])
                        ->map(function($session) {
                            return [
                                'id' => $session->id,
                                'scheduled_at' => $session->scheduled_at,
                                'duration' => $session->duration,
                                'session_type' => $session->session_type,
                                'session_number' => $session->session_number,
                                'formatted_duration' => $session->formatted_duration,
                            ];
                        })
                    : []
            ),
        ]);
    }

    /**
     * Store the assignment (creates instance)
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'framework_id' => 'required|exists:coaching_frameworks,id',
            'client_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:coaching_sessions,id',
        ]);

        $framework = CoachingFramework::findOrFail($request->framework_id);
        $session = CoachingSession::findOrFail($request->session_id);

        // Authorize access to this session
        $this->authorize('view', $session->client);

        // Verify the client matches the session
        if ($session->client_id !== (int)$request->client_id) {
            abort(422, 'Session does not belong to the selected client.');
        }

        // Create framework instance
        $instance = CoachingFrameworkInstance::create([
            'framework_id' => $framework->id,
            'session_id' => $session->id,
            'coach_id' => auth()->id(),
            'client_id' => $session->client_id,
            'schema_snapshot' => $framework->schema,
            'form_data' => [], // Empty initially
        ]);

        // Redirect to the instance edit page
        return to_route('tenant.coach.coaching-framework-instances.show', $instance)
            ->with('success', "{$framework->name} assigned to session successfully.");
    }

    /**
     * API: Get modal data for framework assignment
     */
    public function getModalData(Request $request)
    {
        // Get frameworks
        $frameworks = CoachingFramework::select(['id', 'name', 'description', 'category', 'subcategory'])
            ->orderBy('name')
            ->get();

        // Get clients based on role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see their assigned clients
            $clients = User::role('client')
                ->where('assigned_coach_id', auth()->id())
                ->where('archived', false)
                ->select(['id', 'name', 'email'])
                ->orderBy('name')
                ->get();
        } else {
            // Admins see all active clients
            $clients = User::role('client')
                ->where('archived', false)
                ->select(['id', 'name', 'email'])
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'frameworks' => $frameworks,
            'clients' => $clients,
        ]);
    }

    /**
     * API: Store framework assignment
     */
    public function apiAssign(Request $request)
    {
        $request->validate([
            'framework_id' => 'required|exists:coaching_frameworks,id',
            'client_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:coaching_sessions,id',
        ]);

        $framework = CoachingFramework::findOrFail($request->framework_id);
        $session = CoachingSession::findOrFail($request->session_id);

        // Authorize access to this session
        $this->authorize('view', $session->client);

        // Verify the client matches the session
        if ($session->client_id !== (int)$request->client_id) {
            return response()->json([
                'success' => false,
                'message' => 'Session does not belong to the selected client.',
            ], 422);
        }

        // Check if framework is already assigned to this session
        $existingInstance = CoachingFrameworkInstance::where([
            'framework_id' => $framework->id,
            'session_id' => $session->id,
        ])->first();

        if ($existingInstance) {
            return response()->json([
                'success' => false,
                'message' => 'This framework is already assigned to this session.',
            ], 422);
        }

        // Create framework instance
        $instance = CoachingFrameworkInstance::create([
            'framework_id' => $framework->id,
            'session_id' => $session->id,
            'coach_id' => auth()->id(),
            'client_id' => $session->client_id,
            'schema_snapshot' => $framework->schema,
            'form_data' => [], // Empty initially
        ]);

        // Load relationships for response
        $instance->load(['framework', 'session.client']);

        return response()->json([
            'success' => true,
            'message' => "{$framework->name} assigned to session successfully.",
            'instance' => [
                'id' => $instance->id,
                'framework' => [
                    'id' => $instance->framework->id,
                    'name' => $instance->framework->name,
                    'category' => $instance->framework->category,
                ],
                'session' => [
                    'id' => $instance->session->id,
                    'session_number' => $instance->session->session_number,
                ],
                'progress_percentage' => 0,
                'completed_at' => null,
            ]
        ]);
    }
}
