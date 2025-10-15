<?php

namespace App\Http\Controllers\Tenant\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CoachingTask;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the client dashboard
     */
    public function index()
    {
        $client = auth()->user();
        
        // Upcoming Sessions (next 5)
        $upcomingSessions = CoachingSession::where('client_id', $client->id)
            ->where('scheduled_at', '>', now())
            ->with(['coach:id,name'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'coach' => [
                        'name' => $session->coach->name,
                    ],
                    'scheduled_at' => $session->scheduled_at,
                    'duration' => $session->duration,
                    'session_type' => $session->session_type->value,
                ];
            });
        
        // Outstanding Tasks (next 10)
        $outstandingTasks = CoachingTask::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('deadline', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'status' => $task->status,
                ];
            });
        
        // Quick Stats - Total Sessions Completed
        $totalSessions = CoachingSession::where('client_id', $client->id)
            ->whereNotNull('end_at')
            ->count();
        
        // Quick Stats - Tasks Due This Week
        $tasksDueThisWeek = CoachingTask::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('deadline', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        
        // Quick Stats - Tasks Completed This Month
        $tasksCompletedThisMonth = CoachingTask::where('client_id', $client->id)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
        
        // Assigned Coach
        $assignedCoach = $client->assignedCoach 
            ? [
                'name' => $client->assignedCoach->name,
                'email' => $client->assignedCoach->email,
            ]
            : null;
        
        // Calendar Integration
        $hasMicrosoftCalendar = $client->hasMicrosoftCalendar();
        $hasGoogleCalendar = $client->hasGoogleCalendar();
        $connectedProvider = null;
        
        if ($hasMicrosoftCalendar) {
            $connectedProvider = 'microsoft';
        } elseif ($hasGoogleCalendar) {
            $connectedProvider = 'google';
        }
        
        return Inertia::render('Tenant/client/Dashboard', [
            'upcomingSessions' => $upcomingSessions,
            'outstandingTasks' => $outstandingTasks,
            'quickStats' => [
                'totalSessions' => $totalSessions,
                'tasksDueThisWeek' => $tasksDueThisWeek,
                'tasksCompletedThisMonth' => $tasksCompletedThisMonth,
            ],
            'assignedCoach' => $assignedCoach,
            'hasMicrosoftCalendar' => $hasMicrosoftCalendar,
            'hasGoogleCalendar' => $hasGoogleCalendar,
            'connectedProvider' => $connectedProvider,
        ]);
    }
}

