<?php

namespace App\Jobs\Calendar;

use App\Models\Tenant\CoachingSession;
use App\Services\Calendar\CalendarServiceManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\TenantAwareJob;

class SyncCoachingSessionJob extends TenantAwareJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CoachingSession $session,
        public string $action,
        TenantWithDatabase $tenant
    ) {
        $this->initializeTenancy($tenant);
    }

    /**
     * Execute the job.
     */
    public function handle(CalendarServiceManager $calendarManager): void
    {
        try {
            Log::info("Starting calendar sync job", [
                'session_id' => $this->session->id,
                'action' => $this->action,
                'tenant' => tenant('id')
            ]);

            // Check if session should be synced
            if (!$this->session->shouldSyncToCalendar()) {
                Log::info("Session sync disabled, skipping", [
                    'session_id' => $this->session->id
                ]);
                return;
            }

            $results = $calendarManager->syncCoachingSession($this->session, $this->action);

            // Log results
            foreach ($results as $result) {
                if ($result['success']) {
                    Log::info("Calendar sync successful", [
                        'session_id' => $this->session->id,
                        'action' => $this->action,
                        'provider' => $result['provider'],
                        'user_id' => $result['user_id']
                    ]);
                } else {
                    Log::warning("Calendar sync failed", [
                        'session_id' => $this->session->id,
                        'action' => $this->action,
                        'provider' => $result['provider'],
                        'user_id' => $result['user_id'],
                        'error' => $result['error']
                    ]);
                }
            }

            // Check if all syncs failed
            $allFailed = collect($results)->every(fn($result) => !$result['success']);
            if ($allFailed && count($results) > 0) {
                throw new \Exception('All calendar sync attempts failed');
            }

        } catch (\Exception $e) {
            Log::error("Calendar sync job failed", [
                'session_id' => $this->session->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // If this is the final attempt, we could notify someone
            if ($this->attempts() >= $this->tries) {
                $this->notifyOfFinalFailure($e);
            }

            throw $e;
        }
    }

    /**
     * Handle job failure after all retries
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Calendar sync job permanently failed", [
            'session_id' => $this->session->id,
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);

        // Mark calendar events as failed
        $this->session->calendarEvents()->update([
            'sync_status' => 'failed',
            'sync_error' => $exception->getMessage()
        ]);
    }

    /**
     * Notify of final failure (could send email, Slack notification, etc.)
     */
    protected function notifyOfFinalFailure(\Exception $exception): void
    {
        // For now, just log it. You could add email notifications here
        Log::critical("Calendar sync permanently failed", [
            'session_id' => $this->session->id,
            'action' => $this->action,
            'session_title' => "Coaching Session #{$this->session->session_number}",
            'coach' => $this->session->coach?->name,
            'client' => $this->session->client?->name,
            'error' => $exception->getMessage()
        ]);
    }
}
