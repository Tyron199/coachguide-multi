<?php

namespace App\Http\Controllers\Tenant\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\CoachingTaskAction;
use App\Models\Tenant\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskActionController extends Controller
{
    /**
     * Submit a task action (evidence or completion)
     */
    public function store(Request $request, CoachingTask $task)
    {
        // Authorize - ensure this task belongs to the authenticated client
        if ($task->client_id !== auth()->id()) {
            abort(403, 'Unauthorized to submit for this task');
        }
        
        // Check if task is already completed or cancelled
        if (in_array($task->status->value, ['completed', 'cancelled'])) {
            return back()->withErrors(['task' => 'This task is already completed or cancelled.']);
        }
        
        // Validate based on evidence requirement
        if ($task->evidence_required) {
            $request->validate([
                'content' => 'required|string|max:1000',
                'attachments' => 'required|array|min:1',
                'attachments.*' => 'required|file|max:10240', // 10MB max per file
            ], [
                'content.required' => 'Please provide a comment about your submission.',
                'attachments.required' => 'Evidence is required for this task. Please upload at least one file.',
                'attachments.min' => 'Evidence is required for this task. Please upload at least one file.',
            ]);
        } else {
            $request->validate([
                'content' => 'nullable|string|max:1000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|max:10240',
            ]);
        }
        
        // Create the task action
        $taskAction = CoachingTaskAction::create([
            'coaching_task_id' => $task->id,
            'user_id' => auth()->id(),
            'content' => $request->content ?? 'Task completed.',
        ]);
        
        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store the file in tenant-specific storage (default disk is automatically tenant-scoped)
                $path = $file->store('task-attachments');
                
                // Create attachment record
                Attachment::create([
                    'attachable_type' => CoachingTaskAction::class,
                    'attachable_id' => $taskAction->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => basename($path),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }
        
        // Update task status based on evidence requirement
        if ($task->evidence_required) {
            // If evidence is required, set to 'review' for coach to check
            $task->update([
                'status' => 'review',
            ]);
            $message = 'Your evidence has been submitted for review.';
        } else {
            // If no evidence required, mark as completed
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $message = 'Task marked as complete!';
        }
        
        return to_route('tenant.client.tasks.index')
            ->with('success', $message);
    }
    
    /**
     * Delete a task action (submission)
     */
    public function destroy(CoachingTask $task, CoachingTaskAction $action)
    {
        // Authorize - ensure this action belongs to the authenticated client
        if ($action->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to delete this submission');
        }
        
        // Ensure this action belongs to the specified task
        if ($action->coaching_task_id !== $task->id) {
            abort(404, 'Submission not found for this task');
        }
        
        // Delete the action (attachments will be automatically deleted by the model observer)
        $action->delete();
        
        return back()->with('success', 'Submission deleted successfully.');
    }
}

