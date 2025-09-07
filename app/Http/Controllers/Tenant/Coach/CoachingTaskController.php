<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CoachingTaskReminder;
use App\Models\Tenant\User;
use App\Enums\Tenant\TaskReminderStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingTaskController extends Controller
{
    /**
     * Display tasks for a specific session
     */
    public function sessionTasks(CoachingSession $session)
    {
        // Authorize access to this session (through client)
        $this->authorize('view', $session->client);
        
        // Load session with relationships
        $session->load(['client', 'coach']);
        
        // Get tasks for this session
        $tasks = CoachingTask::where('session_id', $session->id)
            ->with(['coach', 'client', 'session.client', 'reminders'])
            ->withCount('actions')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Tenant/coach/coaching-session/SessionTasks', [
            'session' => $session,
            'tasks' => $tasks,
            'can' => [
                'create' => auth()->user()->can('create', CoachingTask::class),
                'update' => auth()->user()->can('update', $session->client),
                'delete' => auth()->user()->can('create', CoachingTask::class),
            ]
        ]);
    }

    /**
     * Display tasks for a specific client
     */
    public function clientTasks(User $client)
    {
        // Authorize access to this client
        $this->authorize('view', $client);
        
        // Load the client with basic relationships
        $client->load(['company', 'profile', 'assignedCoach']);
        
        // Get all tasks for this client
        $tasks = CoachingTask::where('client_id', $client->id)
            ->with(['session.client', 'coach', 'reminders'])
            ->withCount('actions')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Tenant/coach/client/ClientTasks', [
            'client' => $client,
            'tasks' => $tasks,
            'can' => [
                'create' => auth()->user()->can('create', CoachingTask::class),
                'update' => auth()->user()->can('update', $client),
                'delete' => auth()->user()->can('create', CoachingTask::class),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', CoachingTask::class);
        
        // Handle different contexts (client or session)
        $client = null;
        $session = null;
        
        if ($request->has('client_id')) {
            $client = User::findOrFail($request->client_id);
            $this->authorize('view', $client);
        }
        
        if ($request->has('session_id')) {
            $session = CoachingSession::findOrFail($request->session_id);
            $this->authorize('view', $session->client);
            $client = $session->client;
        }
        
        return Inertia::render('Tenant/coach/coaching-tasks/CreateTask', [
            'client' => $client,
            'session' => $session
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CoachingTask::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'required|exists:users,id',
            'session_id' => 'nullable|exists:coaching_sessions,id',
            'deadline' => 'nullable|date|after:now',
            'evidence_required' => 'nullable|boolean',
            'send_reminders' => 'nullable|boolean',
            'reminders' => 'nullable|array',
            'reminders.*' => 'nullable|in:1h,1d,2d,1w',
        ]);
        
        // Verify coach can access this client
        $client = User::findOrFail($request->client_id);
        $this->authorize('view', $client);
        
        $task = CoachingTask::create([
            'title' => $request->title,
            'description' => $request->description,
            'client_id' => $request->client_id,
            'session_id' => $request->session_id,
            'coach_id' => auth()->id(),
            'deadline' => $request->deadline,
            'status' => 'pending',
            'evidence_required' => $request->boolean('evidence_required'),
            'send_reminders' => $request->boolean('send_reminders'),
        ]);

        // Create reminders if deadline is set and reminders are enabled
        if ($request->deadline && $request->boolean('send_reminders') && $request->reminders) {
            $this->createReminders($task, $request->reminders);
        }
        
        // Redirect based on context
        if ($request->session_id) {
            return to_route('tenant.coaching-sessions.tasks', $request->session_id);
        } else {
            return to_route('tenant.clients.tasks', $request->client_id);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CoachingTask $task)
    {
        $this->authorize('view', $task->client);
        
        $task->load(['client', 'session.client', 'coach', 'actions.user', 'actions.attachments', 'reminders']);
        
        return Inertia::render('Tenant/coach/coaching-tasks/ShowTask', [
            'task' => $task
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoachingTask $task)
    {
        $this->authorize('view', $task->client);
        $this->authorize('update', $task);
        
        $task->load(['client', 'session.client', 'reminders']);
        
        return Inertia::render('Tenant/coach/coaching-tasks/EditTask', [
            'task' => $task,
            'client' => $task->client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoachingTask $task)
    {
        $this->authorize('view', $task->client);
        $this->authorize('update', $task);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
            'evidence_required' => 'nullable|boolean',
            'send_reminders' => 'nullable|boolean',
            'reminders' => 'nullable|array',
            'reminders.*' => 'nullable|in:1h,1d,2d,1w',
            'status' => 'nullable|in:pending,review,completed,cancelled',
        ]);
        
        $originalDeadline = $task->deadline;
        $deadlineChanged = $originalDeadline != $request->deadline;

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'evidence_required' => $request->boolean('evidence_required'),
            'send_reminders' => $request->boolean('send_reminders'),
            'status' => $request->status ?? $task->status,
        ]);

        // Handle reminder updates
        if ($request->deadline && $request->boolean('send_reminders') && $request->reminders) {
            // If deadline changed or reminders changed, recreate all reminders
            if ($deadlineChanged || $this->remindersChanged($task, $request->reminders)) {
                // Delete existing pending reminders and create new ones
                $task->reminders()->where('status', TaskReminderStatus::PENDING)->delete();
                $this->createReminders($task, $request->reminders);
            }
        } else {
            // Remove all pending reminders if deadline removed or reminders disabled
            $task->reminders()->where('status', TaskReminderStatus::PENDING)->delete();
        }
        
        // Redirect based on context
        if ($task->session_id) {
            return to_route('tenant.coaching-sessions.tasks', $task->session_id);
        } else {
            return to_route('tenant.clients.tasks', $task->client_id);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoachingTask $task)
    {
        $this->authorize('view', $task->client);
        $this->authorize('delete', $task);
        
        $clientId = $task->client_id;
        $sessionId = $task->session_id;
        
        $task->delete();
        
        // Redirect based on context
        if ($sessionId) {
            return to_route('tenant.coaching-sessions.tasks', $sessionId);
        } else {
            return to_route('tenant.clients.tasks', $clientId);
        }
    }

    /**
     * Create reminder records for a task
     */
    private function createReminders(CoachingTask $task, array $reminderTimes)
    {
        if (!$task->deadline) return;

        $reminderMap = [
            '1h' => 1, // hours
            '1d' => 24, // hours  
            '2d' => 48, // hours
            '1w' => 168, // hours (7 days * 24 hours)
        ];

        foreach ($reminderTimes as $reminderTime) {
            if (isset($reminderMap[$reminderTime])) {
                $hours = $reminderMap[$reminderTime];
                $remindAt = $task->deadline->copy()->subHours($hours);
                
                // Only create reminder if it's in the future
                if ($remindAt->isFuture()) {
                    CoachingTaskReminder::create([
                        'coaching_task_id' => $task->id,
                        'user_id' => $task->client_id,
                        'remind_at' => $remindAt,
                        'status' => TaskReminderStatus::PENDING,
                        'label' => $this->getReminderLabel($reminderTime),
                    ]);
                }
            }
        }
    }

    /**
     * Get human readable label for reminder time
     */
    private function getReminderLabel(string $reminderTime): string
    {
        return match($reminderTime) {
            '1h' => '1 hour before',
            '1d' => '1 day before',
            '2d' => '2 days before', 
            '1w' => '1 week before',
            default => $reminderTime,
        };
    }

    /**
     * Check if reminders have changed from current task
     */
    private function remindersChanged(CoachingTask $task, array $newReminders): bool
    {
        $currentReminders = $task->reminders()
            ->where('status', TaskReminderStatus::PENDING)
            ->get()
            ->map(function($reminder) {
                // Map labels back to codes
                return match(true) {
                    str_contains($reminder->label, '1 hour') => '1h',
                    str_contains($reminder->label, '1 day') => '1d', 
                    str_contains($reminder->label, '2 days') => '2d',
                    str_contains($reminder->label, '1 week') => '1w',
                    default => null,
                };
            })
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        sort($newReminders);
        
        return $currentReminders !== $newReminders;
    }
}
