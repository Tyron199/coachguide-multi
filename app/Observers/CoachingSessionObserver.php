<?php

namespace App\Observers;

use App\Models\Tenant\CoachingSession;
use App\Jobs\Calendar\SyncCoachingSessionJob;
use App\Jobs\Calendar\DeleteCalendarEventsJob;
use Illuminate\Support\Facades\Log;

class CoachingSessionObserver
{
    /**
     * Handle the CoachingSession "created" event.
     */
    public function created(CoachingSession $coachingSession): void
    {
        Log::info("CoachingSessionObserver created event", [
            'session_id' => $coachingSession->id
        ]);
        if ($this->shouldSync($coachingSession)) {
            Log::info("Dispatching calendar sync job for created session", [
                'session_id' => $coachingSession->id
            ]);
            
            SyncCoachingSessionJob::dispatch(
                $coachingSession,
                'create'
            )->delay(now()->addSeconds(10)); // Small delay to ensure transaction is committed
        }
    }

    /**
     * Handle the CoachingSession "updated" event.
     */
    public function updated(CoachingSession $coachingSession): void
    {
        if ($this->shouldSync($coachingSession)) {
            // Check if relevant fields changed
            $relevantFields = [
                'scheduled_at', 'duration', 'session_type', 'sync_to_calendar'
            ];
            
            $hasRelevantChanges = collect($relevantFields)
                ->some(fn($field) => $coachingSession->wasChanged($field));
            
            if ($hasRelevantChanges) {
                Log::info("Dispatching calendar sync job for updated session", [
                    'session_id' => $coachingSession->id,
                    'changed_fields' => array_keys($coachingSession->getChanges())
                ]);
                
                SyncCoachingSessionJob::dispatch(
                    $coachingSession,
                    'update'
                )->delay(now()->addSeconds(5));
            }
        }
    }

    /**
     * Handle the CoachingSession "deleting" event.
     * This fires BEFORE the model is deleted, so relationships still work.
     */
    public function deleting(CoachingSession $coachingSession): void
    {
        // Collect calendar events while the session still exists
        $calendarEvents = $coachingSession->calendarEvents;
        
        if ($calendarEvents->isNotEmpty()) {
            Log::info("Dispatching calendar events deletion job for deleting session", [
                'session_id' => $coachingSession->id,
                'events_count' => $calendarEvents->count()
            ]);
            
            // Convert calendar events to simple array data
            $eventsToDelete = $calendarEvents->map(function ($event) use ($coachingSession) {
                return [
                    'external_event_id' => $event->external_event_id,
                    'external_calendar_id' => $event->external_calendar_id,
                    'provider' => $event->provider->value,
                    'user_id' => $event->user_id,
                    'session_id' => $coachingSession->id, // for logging only
                ];
            })->toArray();
            
            DeleteCalendarEventsJob::dispatch($eventsToDelete)
                ->delay(now()->addSeconds(5));
        }
    }

    /**
     * Handle the CoachingSession "restored" event.
     */
    public function restored(CoachingSession $coachingSession): void
    {
        // When a session is restored, recreate calendar events
        if ($this->shouldSync($coachingSession)) {
            Log::info("Dispatching calendar sync job for restored session", [
                'session_id' => $coachingSession->id
            ]);
            
            SyncCoachingSessionJob::dispatch(
                $coachingSession,
                'create'
            )->delay(now()->addSeconds(10));
        }
    }

    /**
     * Handle the CoachingSession "deleted" event.
     * This is kept for logging purposes, but the actual work is done in deleting()
     */
    public function deleted(CoachingSession $coachingSession): void
    {
        Log::info("CoachingSession deleted", [
            'session_id' => $coachingSession->id
        ]);
    }

    /**
     * Handle the CoachingSession "force deleted" event.
     * Note: For force delete, we might not get a deleting event, so we handle it here
     * but we need to be careful about the race condition
     */
    public function forceDeleted(CoachingSession $coachingSession): void
    {
        Log::info("CoachingSession force deleted", [
            'session_id' => $coachingSession->id
        ]);
        
        // For force delete, the calendar events might already be gone
        // This is a limitation we'll have to accept or handle differently
        // Most deletions should use soft delete anyway
    }

    /**
     * Check if the session should be synced to calendars
     */
    protected function shouldSync(CoachingSession $coachingSession): bool
    {
        // Don't sync if sync is disabled for this session
        if (!$coachingSession->sync_to_calendar) {
            return false;
        }

        // Don't sync if no one has calendar integrations
        $hasIntegrations = ($coachingSession->coach && $coachingSession->coach->calendarIntegrations()->exists()) ||
                          ($coachingSession->client && $coachingSession->client->calendarIntegrations()->exists());

        if (!$hasIntegrations) {
            Log::debug("Skipping calendar sync - no calendar integrations found", [
                'session_id' => $coachingSession->id
            ]);
            return false;
        }

        return true;
    }
}
