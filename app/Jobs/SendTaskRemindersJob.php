<?php

namespace App\Jobs;

use App\Enums\Tenant\TaskReminderStatus;
use App\Models\Tenant\CoachingTaskReminder;
use App\Notifications\Tenant\TaskReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTaskRemindersJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CoachingTaskReminder $reminder
    ) {
        // Tenancy is handled automatically with QueueTenancyBootstrapper
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load relationships
            $this->reminder->load(['coachingTask.client', 'coachingTask.coach', 'user']);

            Log::info("Processing task reminder", [
                'reminder_id' => $this->reminder->id,
                'task_id' => $this->reminder->coaching_task_id,
                'user_id' => $this->reminder->user_id,
                'remind_at' => $this->reminder->remind_at,
                'tenant' => tenant('id')
            ]);

            // Verify task is still active (not completed or cancelled)
            $task = $this->reminder->coachingTask;
            if (in_array($task->status, ['completed', 'cancelled'])) {
                Log::info("Task already completed/cancelled, skipping reminder", [
                    'task_id' => $task->id,
                    'status' => $task->status
                ]);
                
                // Mark reminder as sent to prevent future attempts
                $this->reminder->update(['status' => TaskReminderStatus::SENT]);
                return;
            }

            // Send notification to the client
            $client = $this->reminder->user;
            $client->notify(new TaskReminderNotification($task, $this->reminder));

            // Update reminder status to sent
            $this->reminder->update(['status' => TaskReminderStatus::SENT]);

            Log::info("Task reminder sent successfully", [
                'reminder_id' => $this->reminder->id,
                'task_id' => $task->id,
                'client_id' => $client->id,
                'client_email' => $client->email
            ]);

        } catch (\Exception $e) {
            Log::error("Task reminder job failed", [
                'reminder_id' => $this->reminder->id,
                'task_id' => $this->reminder->coaching_task_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure after all retries
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Task reminder job permanently failed", [
            'reminder_id' => $this->reminder->id,
            'task_id' => $this->reminder->coaching_task_id,
            'user_id' => $this->reminder->user_id,
            'error' => $exception->getMessage()
        ]);

        // Mark reminder as failed
        $this->reminder->update(['status' => TaskReminderStatus::FAILED]);
    }
}

