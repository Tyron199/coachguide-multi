<?php

namespace App\Console\Commands;

use App\Enums\Tenant\TaskReminderStatus;
use App\Jobs\SendTaskRemindersJob;
use App\Models\Tenant\CoachingTaskReminder;
use Illuminate\Console\Command;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class SendTaskReminders extends Command
{
    use TenantAwareCommand, HasATenantsOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders {--tenants=* : Tenant IDs to process (optional - defaults to all tenants)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending task reminders to clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Query pending reminders that are due
        $reminders = CoachingTaskReminder::where('status', TaskReminderStatus::PENDING)
            ->where('remind_at', '<=', now())
            ->with(['coachingTask.client', 'coachingTask.coach', 'user'])
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('No pending reminders to send.');
            return Command::SUCCESS;
        }

        $this->info("Found {$reminders->count()} reminder(s) to send...");

        $dispatched = 0;
        foreach ($reminders as $reminder) {
            try {
                // Dispatch job to send the reminder
                SendTaskRemindersJob::dispatch($reminder);
                
                $this->line("Dispatched reminder #{$reminder->id} for task '{$reminder->coachingTask->title}' to {$reminder->user->email}");
                $dispatched++;
            } catch (\Exception $e) {
                $this->error("Failed to dispatch reminder #{$reminder->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully dispatched {$dispatched} reminder(s).");

        return Command::SUCCESS;
    }
}

