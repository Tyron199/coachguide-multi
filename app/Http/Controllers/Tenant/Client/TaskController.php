<?php

namespace App\Http\Controllers\Tenant\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingTask;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the client's tasks
     */
    public function index(Request $request)
    {
        $client = auth()->user();
        
        // Get filter from request (all, pending, completed)
        $filter = $request->get('filter', 'all');
        
        $query = CoachingTask::where('client_id', $client->id)
            ->with(['coach:id,name', 'session:id,client_id,scheduled_at', 'reminders'])
            ->withCount('actions');
        
        // Apply filter
        if ($filter === 'pending') {
            $query->whereIn('status', ['pending', 'in_progress']);
        } elseif ($filter === 'completed') {
            $query->where('status', 'completed');
        }
        
        $tasks = $query->orderBy('deadline', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'deadline' => $task->deadline,
                    'status' => $task->status->value,
                    'evidence_required' => $task->evidence_required,
                    'created_at' => $task->created_at,
                    'completed_at' => $task->completed_at,
                    'coach' => $task->coach ? ['name' => $task->coach->name] : null,
                    'session' => $task->session ? [
                        'session_number' => $task->session->session_number,
                        'scheduled_at' => $task->session->scheduled_at,
                    ] : null,
                    'reminders_count' => $task->reminders->count(),
                    'actions_count' => $task->actions_count,
                    'is_overdue' => $task->isOverdue(),
                ];
            });
        
        return Inertia::render('Tenant/client/Tasks', [
            'tasks' => $tasks,
            'filter' => $filter,
        ]);
    }
    
    /**
     * Display the specified task
     */
    public function show(CoachingTask $task)
    {
        // Authorize - ensure this task belongs to the authenticated client
        if ($task->client_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this task');
        }
        
        // Load relationships
        $task->load([
            'coach:id,name',
            'session:id,client_id,scheduled_at',
            'actions.user:id,name',
            'actions.attachments',
            'reminders'
        ]);
        
        return Inertia::render('Tenant/client/TaskShow', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'deadline' => $task->deadline,
                'status' => $task->status->value,
                'evidence_required' => $task->evidence_required,
                'send_reminders' => $task->send_reminders,
                'created_at' => $task->created_at,
                'completed_at' => $task->completed_at,
                'coach' => $task->coach ? [
                    'id' => $task->coach->id,
                    'name' => $task->coach->name,
                ] : null,
                'session' => $task->session ? [
                    'id' => $task->session->id,
                    'session_number' => $task->session->session_number,
                    'scheduled_at' => $task->session->scheduled_at,
                ] : null,
                'reminders' => $task->reminders->map(fn($r) => [
                    'id' => $r->id,
                    'remind_at' => $r->remind_at,
                    'status' => $r->status->value,
                    'label' => $r->label,
                ]),
                'actions' => $task->actions->map(fn($action) => [
                    'id' => $action->id,
                    'content' => $action->content,
                    'created_at' => $action->created_at,
                    'user' => [
                        'id' => $action->user->id,
                        'name' => $action->user->name,
                    ],
                    'attachments' => $action->attachments->map(fn($att) => [
                        'id' => $att->id,
                        'filename' => $att->original_name,
                        'size' => $att->file_size,
                        'mime_type' => $att->mime_type,
                        'download_url' => route('tenant.coach.attachments.download', $att->id),
                    ]),
                ]),
                'is_overdue' => $task->isOverdue(),
            ]
        ]);
    }
}

