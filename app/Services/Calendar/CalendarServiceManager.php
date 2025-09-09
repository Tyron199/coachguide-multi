<?php

namespace App\Services\Calendar;

use App\Services\Calendar\Providers\GoogleCalendarService;
use App\Services\Calendar\Providers\MicrosoftCalendarService;
use App\Services\OAuth\OauthProviderType;
use App\Services\OAuth\OAuthProviderManager;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class CalendarServiceManager
{
    protected array $services = [];
    protected OAuthProviderManager $oauthManager;

    public function __construct(OAuthProviderManager $oauthManager)
    {
        $this->oauthManager = $oauthManager;
        $this->registerServices();
    }

    /**
     * Register all available calendar services
     */
    protected function registerServices(): void
    {
        $this->services = [
            OauthProviderType::GOOGLE->value => GoogleCalendarService::class,
            OauthProviderType::MICROSOFT->value => MicrosoftCalendarService::class,
        ];
    }

    /**
     * Get a calendar service instance
     */
    public function getService(OauthProviderType $provider): CalendarServiceInterface
    {
        if (!$this->isProviderSupported($provider)) {
            throw new InvalidArgumentException("Unsupported calendar provider: {$provider->value}");
        }

        $serviceClass = $this->services[$provider->value];
        
        return new $serviceClass($provider, $this->oauthManager);
    }

    /**
     * Check if a provider is supported
     */
    public function isProviderSupported(OauthProviderType $provider): bool
    {
        return isset($this->services[$provider->value]);
    }

    /**
     * Get all supported providers
     */
    public function getSupportedProviders(): array
    {
        return array_map(
            fn($key) => OauthProviderType::from($key),
            array_keys($this->services)
        );
    }

    /**
     * Sync a coaching session to all connected calendars
     */
    public function syncCoachingSession(CoachingSession $session, string $action = 'create'): array
    {
        $results = [];
        
        // Get all users who should have this session synced to their calendars
        $usersToSync = $this->getUsersForSync($session);
        
        foreach ($usersToSync as $user) {
            $integrations = $user->calendarIntegrations;
            
            foreach ($integrations as $integration) {
                try {
                    $result = $this->syncToIntegration($session, $integration, $action);
                    $results[] = [
                        'user_id' => $user->id,
                        'provider' => $integration->provider->value,
                        'success' => true,
                        'data' => $result,
                    ];
                } catch (\Exception $e) {
                    Log::error("Calendar sync failed for user {$user->id} provider {$integration->provider->value}", [
                        'session_id' => $session->id,
                        'action' => $action,
                        'error' => $e->getMessage()
                    ]);
                    
                    $results[] = [
                        'user_id' => $user->id,
                        'provider' => $integration->provider->value,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }
        
        return $results;
    }

    /**
     * Sync to a specific calendar integration
     */
    protected function syncToIntegration(CoachingSession $session, CalendarIntegration $integration, string $action): ?CalendarEventData
    {
        $service = $this->getService($integration->provider);
        
        return match ($action) {
            'create' => $this->handleCreate($session, $integration, $service),
            'update' => $this->handleUpdate($session, $integration, $service),
            'delete' => $this->handleDelete($session, $integration, $service),
            default => throw new InvalidArgumentException("Unsupported sync action: {$action}")
        };
    }

    /**
     * Handle event creation
     */
    protected function handleCreate(CoachingSession $session, CalendarIntegration $integration, CalendarServiceInterface $service): CalendarEventData
    {
        // Check if event already exists
        $existingEvent = $session->calendarEvents()
            ->where('user_id', $integration->user_id)
            ->where('provider', $integration->provider)
            ->first();
            
        if ($existingEvent) {
            Log::info("Calendar event already exists, skipping creation", [
                'session_id' => $session->id,
                'user_id' => $integration->user_id,
                'provider' => $integration->provider->value
            ]);
            return $this->extractEventDataFromModel($existingEvent);
        }

        $eventData = $service->createEvent($session, $integration);
        
        // Store the calendar event reference
        $session->calendarEvents()->create([
            'user_id' => $integration->user_id,
            'provider' => $integration->provider,
            'external_event_id' => $eventData->externalEventId,
            'external_calendar_id' => $eventData->externalCalendarId,
            'meeting_url' => $eventData->meetingUrl,
            'meeting_id' => $eventData->meetingId,
            'sync_status' => 'created',
            'last_synced_at' => now(),
            'external_data' => $eventData->rawData,
        ]);
        
        return $eventData;
    }

    /**
     * Handle event update
     */
    protected function handleUpdate(CoachingSession $session, CalendarIntegration $integration, CalendarServiceInterface $service): ?CalendarEventData
    {
        $calendarEvent = $session->calendarEvents()
            ->where('user_id', $integration->user_id)
            ->where('provider', $integration->provider)
            ->first();
            
        if (!$calendarEvent) {
            // Event doesn't exist, create it instead
            return $this->handleCreate($session, $integration, $service);
        }

        $eventData = $service->updateEvent($calendarEvent, $session);
        
        // Update the stored reference
        $calendarEvent->update([
            'meeting_url' => $eventData->meetingUrl,
            'meeting_id' => $eventData->meetingId,
            'sync_status' => 'updated',
            'last_synced_at' => now(),
            'external_data' => $eventData->rawData,
            'sync_error' => null,
        ]);
        
        return $eventData;
    }

    /**
     * Handle event deletion
     */
    protected function handleDelete(CoachingSession $session, CalendarIntegration $integration, CalendarServiceInterface $service): ?CalendarEventData
    {
        $calendarEvent = $session->calendarEvents()
            ->where('user_id', $integration->user_id)
            ->where('provider', $integration->provider)
            ->first();
            
        if (!$calendarEvent) {
            Log::info("Calendar event not found for deletion", [
                'session_id' => $session->id,
                'user_id' => $integration->user_id,
                'provider' => $integration->provider->value
            ]);
            return null;
        }

        $success = $service->deleteEvent($calendarEvent);
        
        if ($success) {
            $calendarEvent->update([
                'sync_status' => 'deleted',
                'last_synced_at' => now(),
            ]);
        }
        
        return null;
    }

    /**
     * Get users who should have this session synced to their calendars
     */
    protected function getUsersForSync(CoachingSession $session): array
    {
        $users = [];
        
        // Add coach if they have calendar integrations
        if ($session->coach && $session->coach->calendarIntegrations()->exists()) {
            $users[] = $session->coach;
        }
        
        // Add client if they have calendar integrations
        if ($session->client && $session->client->calendarIntegrations()->exists()) {
            $users[] = $session->client;
        }
        
        return $users;
    }

    /**
     * Extract CalendarEventData from a CalendarEvent model
     */
    protected function extractEventDataFromModel(\App\Models\Tenant\CalendarEvent $calendarEvent): CalendarEventData
    {
        return new CalendarEventData(
            externalEventId: $calendarEvent->external_event_id,
            title: 'Existing Event', // We don't store the title
            description: '',
            startTime: $calendarEvent->coachingSession->scheduled_at,
            endTime: $calendarEvent->coachingSession->planned_end_time,
            meetingUrl: $calendarEvent->meeting_url,
            meetingId: $calendarEvent->meeting_id,
            externalCalendarId: $calendarEvent->external_calendar_id,
            rawData: $calendarEvent->external_data ?? []
        );
    }
}
