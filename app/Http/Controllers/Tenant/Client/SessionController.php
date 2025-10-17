<?php

namespace App\Http\Controllers\Tenant\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingSession;
use Inertia\Inertia;

class SessionController extends Controller
{
    /**
     * Display a listing of the client's sessions
     */
    public function index()
    {
        $client = auth()->user();
        
        // Get all sessions for this client
        $sessions = CoachingSession::where('client_id', $client->id)
            ->with(['coach:id,name'])
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_number' => $session->session_number,
                    'coach' => [
                        'name' => $session->coach->name,
                    ],
                    'scheduled_at' => $session->scheduled_at,
                    'duration' => $session->duration,
                    'formatted_duration' => $session->formatted_duration,
                    'session_type' => $session->session_type->value,
                    'is_past' => $session->is_past,
                ];
            });
        
        // Split into upcoming and past
        $upcomingSessions = $sessions->filter(fn($s) => !$s['is_past'])->values();
        $pastSessions = $sessions->filter(fn($s) => $s['is_past'])->values();
        
        return Inertia::render('Tenant/client/Sessions', [
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
        ]);
    }
    
    /**
     * Display the specified session
     */
    public function show(CoachingSession $session)
    {
        // Authorize - ensure this session belongs to the authenticated client
        if ($session->client_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this session');
        }
        
        // Load relationships
        $session->load([
            'coach:id,name',
            'coachingTasks' => function ($query) {
                $query->orderBy('deadline', 'asc')
                      ->orderBy('created_at', 'desc');
            },
            'coachingTasks.coach:id,name'
        ]);
        
        return Inertia::render('Tenant/client/SessionShow', [
            'session' => [
                'id' => $session->id,
                'session_number' => $session->session_number,
                'coach' => [
                    'id' => $session->coach->id,
                    'name' => $session->coach->name,
                ],
                'scheduled_at' => $session->scheduled_at,
                'duration' => $session->duration,
                'formatted_duration' => $session->formatted_duration,
                'session_type' => $session->session_type->value,
                'is_past' => $session->is_past,
                'is_active' => $session->is_active,
                'tasks' => $session->coachingTasks->map(fn($task) => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'status' => $task->status->value,
                    'evidence_required' => $task->evidence_required,
                    'is_overdue' => $task->isOverdue(),
                ]),
            ]
        ]);
    }
}

