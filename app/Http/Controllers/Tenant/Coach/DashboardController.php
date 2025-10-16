<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\CoachingContract;
use App\Enums\Tenant\ContractStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the coach dashboard
     */
    public function index()
    {
        $coach = auth()->user();
        
        // Active Clients Count
        $activeClients = $coach->assignedClients()
            ->whereHas('roles', fn($q) => $q->where('name', 'client'))
            ->where('archived', false)
            ->count();
        
        // Sessions Today
        $sessionsToday = CoachingSession::where('coach_id', $coach->id)
            ->whereDate('scheduled_at', today())
            ->where('scheduled_at', '>', now())
            ->count();
        
        // Sessions This Week
        $sessionsThisWeek = CoachingSession::where('coach_id', $coach->id)
            ->whereBetween('scheduled_at', [now(), now()->endOfWeek()])
            ->count();
        
        // Outstanding Actions (Tasks)
        $outstandingActionsCount = CoachingTask::where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
        
        // CPD Hours (from completed sessions this month, convert minutes to hours)
        $cpdHours = CoachingSession::where('coach_id', $coach->id)
            ->whereNotNull('end_at')
            ->whereMonth('end_at', now()->month)
            ->whereYear('end_at', now()->year)
            ->sum('duration') / 60;
        
        $cpdHours = round($cpdHours, 1);
        
        // Monthly Target (hardcoded for now, could be user preference later)
        $monthlyTarget = 10;
        
        // Upcoming Sessions (next 3) - includes currently active sessions
        $upcomingSessions = CoachingSession::where('coach_id', $coach->id)
            ->where('end_at', '>', now())
            ->with(['client:id,name,email'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(3)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'client' => [
                        'name' => $session->client->name,
                        'email' => $session->client->email,
                    ],
                    'scheduled_at' => $session->scheduled_at,
                    'duration' => $session->duration,
                    'session_type' => $session->session_type->value,
                    'is_active' => $session->is_active,
                ];
            });
        
        // Outstanding Actions (next 3)
        $outstandingActions = CoachingTask::where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['client:id,name'])
            ->orderBy('deadline', 'asc')
            ->limit(3)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'client' => $task->client->name,
                    'due_date' => $task->deadline,
                    'status' => $task->status,
                ];
            });
        
        // Quick Stats - Sessions Completed This Month
        $sessionsCompleted = CoachingSession::where('coach_id', $coach->id)
            ->whereNotNull('end_at')
            ->whereMonth('end_at', now()->month)
            ->whereYear('end_at', now()->year)
            ->count();
        
        // Quick Stats - Contracts Signed This Month
        $contractsSigned = CoachingContract::where('coach_id', $coach->id)
            ->whereIn('status', [
                ContractStatus::COUNTERSIGNED,
                ContractStatus::ACTIVE,
            ])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Quick Stats - Action Completion Rate
        $totalTasksThisMonth = CoachingTask::where('coach_id', $coach->id)
            ->whereNotNull('deadline')
            ->whereMonth('deadline', now()->month)
            ->whereYear('deadline', now()->year)
            ->count();
        
        $completedTasksThisMonth = CoachingTask::where('coach_id', $coach->id)
            ->where('status', 'completed')
            ->whereNotNull('deadline')
            ->whereMonth('deadline', now()->month)
            ->whereYear('deadline', now()->year)
            ->count();
        
        $completionRate = $totalTasksThisMonth > 0 
            ? round(($completedTasksThisMonth / $totalTasksThisMonth) * 100)
            : 0;
        
        // Calendar Integration
        $hasMicrosoftCalendar = $coach->hasMicrosoftCalendar();
        $hasGoogleCalendar = $coach->hasGoogleCalendar();
        $connectedProvider = null;
        
        if ($hasMicrosoftCalendar) {
            $connectedProvider = 'microsoft';
        } elseif ($hasGoogleCalendar) {
            $connectedProvider = 'google';
        }
        
        return Inertia::render('Tenant/coach/Dashboard', [
            'dashboardStats' => [
                'activeClients' => $activeClients,
                'upcomingSessions' => [
                    'today' => $sessionsToday,
                    'thisWeek' => $sessionsThisWeek,
                ],
                'outstandingActions' => $outstandingActionsCount,
                'cpdHours' => $cpdHours,
                'monthlyTarget' => $monthlyTarget,
            ],
            'upcomingSessions' => $upcomingSessions,
            'outstandingActions' => $outstandingActions,
            'quickStats' => [
                'sessionsCompleted' => $sessionsCompleted,
                'contractsSigned' => $contractsSigned,
                'completionRate' => $completionRate,
            ],
            'hasMicrosoftCalendar' => $hasMicrosoftCalendar,
            'hasGoogleCalendar' => $hasGoogleCalendar,
            'connectedProvider' => $connectedProvider,
        ]);
    }
}

