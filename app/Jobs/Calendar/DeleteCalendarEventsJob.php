<?php

namespace App\Jobs\Calendar;

use App\Models\Tenant\CalendarIntegration;
use App\Services\Calendar\CalendarServiceManager;
use App\Services\OAuth\OauthProviderType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteCalendarEventsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 300]; // 30sec, 2min, 5min - shorter backoff for deletes

    /**
     * Create a new job instance.
     * 
     * @param array $eventsToDelete Array of events with structure:
     *   [
     *     [
     *       'external_event_id' => 'abc123',
     *       'external_calendar_id' => 'primary', 
     *       'provider' => 'microsoft',
     *       'user_id' => 1,
     *       'session_id' => 123 // for logging only
     *     ]
     *   ]
     */
    public function __construct(
        public array $eventsToDelete
    ) {
        // Job accepts only the minimal data needed for deletion
    }

    /**
     * Execute the job.
     */
    public function handle(CalendarServiceManager $calendarManager): void
    {
        Log::info("Starting calendar events deletion job", [
            'events_count' => count($this->eventsToDelete)
        ]);

        $results = [];

        foreach ($this->eventsToDelete as $eventData) {
            try {
                $result = $this->deleteCalendarEvent($calendarManager, $eventData);
                $results[] = $result;
                
                if ($result['success']) {
                    Log::info("Calendar event deleted successfully", [
                        'external_event_id' => $eventData['external_event_id'],
                        'provider' => $eventData['provider'],
                        'user_id' => $eventData['user_id']
                    ]);
                } else {
                    Log::warning("Calendar event deletion failed", [
                        'external_event_id' => $eventData['external_event_id'],
                        'provider' => $eventData['provider'],
                        'user_id' => $eventData['user_id'],
                        'error' => $result['error']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Exception during calendar event deletion", [
                    'external_event_id' => $eventData['external_event_id'],
                    'provider' => $eventData['provider'],
                    'error' => $e->getMessage()
                ]);
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'event_data' => $eventData
                ];
            }
        }

        // Check if all deletions failed
        $allFailed = collect($results)->every(fn($result) => !$result['success']);
        if ($allFailed && count($results) > 0) {
            throw new \Exception('All calendar event deletions failed');
        }
    }

    /**
     * Delete a single calendar event
     */
    protected function deleteCalendarEvent(CalendarServiceManager $calendarManager, array $eventData): array
    {
        try {
            // Get the calendar integration for this user and provider
            $integration = CalendarIntegration::where('user_id', $eventData['user_id'])
                ->where('provider', OauthProviderType::from($eventData['provider']))
                ->first();

            if (!$integration) {
                return [
                    'success' => false,
                    'error' => 'Calendar integration not found',
                    'event_data' => $eventData
                ];
            }

            // Create a mock CalendarEvent for the service to use
            $mockCalendarEvent = new \App\Models\Tenant\CalendarEvent([
                'external_event_id' => $eventData['external_event_id'],
                'external_calendar_id' => $eventData['external_calendar_id'],
                'provider' => OauthProviderType::from($eventData['provider']),
                'user_id' => $eventData['user_id']
            ]);

            // Set the user relationship so the service can access it
            $mockCalendarEvent->setRelation('user', $integration->user);

            $service = $calendarManager->getService(OauthProviderType::from($eventData['provider']));
            $success = $service->deleteEvent($mockCalendarEvent);

            return [
                'success' => $success,
                'event_data' => $eventData
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'event_data' => $eventData
            ];
        }
    }

    /**
     * Handle job failure after all retries
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Calendar events deletion job permanently failed", [
            'events_count' => count($this->eventsToDelete),
            'error' => $exception->getMessage(),
            'events' => $this->eventsToDelete
        ]);

        // Could notify administrators about failed deletions
        // These events will remain orphaned in external calendars
    }
}
