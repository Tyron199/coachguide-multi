<?php

namespace App\Console\Commands\Calendar;

use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\User;
use App\Models\Central\Tenant;
use App\Services\Calendar\CalendarServiceManager;
use App\Services\OAuth\OauthProviderType;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestCalendarSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:test-sync 
                            {tenant : The tenant ID to initialize}
                            {action=create : The action to perform (create, update, delete, fetch)}
                            {--session-id= : Specific coaching session ID to sync}
                            {--user-id= : Specific user ID to test with (only for fetch action)}
                            {--provider= : Specific provider to test (only for fetch action - google, microsoft)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test calendar synchronization functionality';

    protected CalendarServiceManager $calendarManager;

    public function __construct(CalendarServiceManager $calendarManager)
    {
        parent::__construct();
        $this->calendarManager = $calendarManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $action = $this->argument('action');
        
        // Initialize tenant context
        if (!$this->initializeTenant($tenantId)) {
            return 1;
        }
        
        $this->info("Testing calendar sync - Tenant: {$tenantId}, Action: {$action}");
        
        try {
            match ($action) {
                'create' => $this->testCreateEvent(),
                'update' => $this->testUpdateEvent(),
                'delete' => $this->testDeleteEvent(),
                'fetch' => $this->testFetchEvents(),
                default => $this->error("Unknown action: {$action}")
            };
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    protected function testCreateEvent()
    {
        $session = $this->getCoachingSession();
        if (!$session) return;

        $this->info("Testing event creation for session ID: {$session->id}");
        $this->line("Session: {$this->generateEventTitle($session)} at {$session->scheduled_at}");
        $this->line("Session Type: {$session->session_type->value}");
        $this->line("Duration: {$session->formatted_duration}");
        $this->line("Should create meeting: " . ($this->shouldCreateMeetingForSession($session) ? 'Yes' : 'No'));
        
        // Show which users have calendar integrations
        $this->displaySessionCalendarInfo($session);

        $results = $this->calendarManager->syncCoachingSession($session, 'create');
        
        $this->displayResults($results);
    }

    protected function testUpdateEvent()
    {
        $session = $this->getCoachingSession();
        if (!$session) return;

        if ($session->calendarEvents()->count() === 0) {
            $this->warn("No calendar events found for this session. Creating instead...");
            return $this->testCreateEvent();
        }

        $this->info("Testing event update for session ID: {$session->id}");
        
        $results = $this->calendarManager->syncCoachingSession($session, 'update');
        
        $this->displayResults($results);
    }

    protected function testDeleteEvent()
    {
        $session = $this->getCoachingSession();
        if (!$session) return;

        if ($session->calendarEvents()->count() === 0) {
            $this->warn("No calendar events found for this session to delete.");
            return;
        }

        $this->info("Testing event deletion for session ID: {$session->id}");
        
        $results = $this->calendarManager->syncCoachingSession($session, 'delete');
        
        $this->displayResults($results);
    }

    protected function testFetchEvents()
    {
        // For fetch, we need specific user/provider since it's not session-based
        $integration = $this->getCalendarIntegration();
        if (!$integration) return;

        $this->info("Testing event fetching for user: {$integration->user->name} ({$integration->provider->value})");

        $service = $this->calendarManager->getService($integration->provider);
        
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $this->line("Fetching events from {$startDate->toDateString()} to {$endDate->toDateString()}");

        $events = $service->fetchEvents($integration, $startDate, $endDate);
        
        $this->info("Found " . count($events) . " events:");
        
        foreach ($events as $event) {
            $this->line("- {$event->title} ({$event->startTime->format('Y-m-d H:i')})");
            if ($event->meetingUrl) {
                $this->line("  Meeting: {$event->meetingUrl}");
            }
        }
    }

    protected function getCoachingSession(): ?CoachingSession
    {
        $sessionId = $this->option('session-id');
        
        if ($sessionId) {
            $session = CoachingSession::find($sessionId);
            if (!$session) {
                $this->error("Coaching session with ID {$sessionId} not found.");
                return null;
            }
            return $session;
        }

        // Get the first available session
        $session = CoachingSession::with(['client', 'coach'])
            ->whereHas('client.calendarIntegrations')
            ->orWhereHas('coach.calendarIntegrations')
            ->first();

        if (!$session) {
            $this->error("No coaching sessions found with users that have calendar integrations.");
            $this->line("Please create a coaching session and ensure users have connected calendars.");
            return null;
        }

        return $session;
    }

    protected function getCalendarIntegration(): ?CalendarIntegration
    {
        $userId = $this->option('user-id');
        $provider = $this->option('provider');

        $query = CalendarIntegration::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($provider) {
            $providerEnum = OauthProviderType::tryFrom($provider);
            if (!$providerEnum) {
                $this->error("Invalid provider: {$provider}. Use 'google' or 'microsoft'.");
                return null;
            }
            $query->where('provider', $providerEnum);
        }

        $integration = $query->first();

        if (!$integration) {
            $this->error("No calendar integration found with the specified criteria.");
            $this->line("Available integrations:");
            
            $available = CalendarIntegration::with('user')->get();
            foreach ($available as $avail) {
                $this->line("- User ID: {$avail->user_id} ({$avail->user->name}) - Provider: {$avail->provider->value}");
            }
            
            return null;
        }

        return $integration;
    }

    protected function displayResults(array $results)
    {
        foreach ($results as $result) {
            $status = $result['success'] ? '✅' : '❌';
            $provider = strtoupper($result['provider']);
            
            $this->line("{$status} {$provider} (User ID: {$result['user_id']})");
            
            if ($result['success'] && isset($result['data'])) {
                $data = $result['data'];
                $this->line("   Event ID: {$data->externalEventId}");
                if ($data->meetingUrl) {
                    $this->line("   ✅ Meeting URL: {$data->meetingUrl}");
                } else {
                    $this->line("   ❌ No meeting URL created");
                }
                if ($data->meetingId) {
                    $this->line("   Meeting ID: {$data->meetingId}");
                }
                
                // Show raw data for debugging
                if (!empty($data->rawData)) {
                    $this->line("   Raw response preview:");
                    if (isset($data->rawData['isOnlineMeeting'])) {
                        $this->line("     isOnlineMeeting: " . ($data->rawData['isOnlineMeeting'] ? 'true' : 'false'));
                    }
                    if (isset($data->rawData['onlineMeetingProvider'])) {
                        $this->line("     onlineMeetingProvider: {$data->rawData['onlineMeetingProvider']}");
                    }
                    if (isset($data->rawData['onlineMeeting'])) {
                        $this->line("     onlineMeeting exists: yes");
                        if (isset($data->rawData['onlineMeeting']['joinUrl'])) {
                            $this->line("     joinUrl: {$data->rawData['onlineMeeting']['joinUrl']}");
                        }
                    } else {
                        $this->line("     onlineMeeting exists: no");
                    }
                }
            } elseif (!$result['success']) {
                $this->line("   Error: {$result['error']}");
            }
        }
    }

    /**
     * Display calendar integration info for a session
     */
    protected function displaySessionCalendarInfo(CoachingSession $session)
    {
        $this->line("Calendar integrations:");
        
        if ($session->coach) {
            $coachIntegrations = $session->coach->calendarIntegrations;
            if ($coachIntegrations->count() > 0) {
                $this->line("  Coach ({$session->coach->name}):");
                foreach ($coachIntegrations as $integration) {
                    $this->line("    - {$integration->provider->value}");
                }
            } else {
                $this->line("  Coach ({$session->coach->name}): No calendar integrations");
            }
        }
        
        if ($session->client) {
            $clientIntegrations = $session->client->calendarIntegrations;
            if ($clientIntegrations->count() > 0) {
                $this->line("  Client ({$session->client->name}):");
                foreach ($clientIntegrations as $integration) {
                    $this->line("    - {$integration->provider->value}");
                }
            } else {
                $this->line("  Client ({$session->client->name}): No calendar integrations");
            }
        }
        
        $this->line("");
    }

    /**
     * Generate event title from coaching session
     */
    protected function generateEventTitle(CoachingSession $session): string
    {
        $sessionNumber = $session->session_number;
        $clientName = $session->client?->name ?? 'Client';
        
        return "Coaching Session #{$sessionNumber} - {$clientName}";
    }

    /**
     * Check if session should create a meeting (for debugging)
     */
    protected function shouldCreateMeetingForSession(CoachingSession $session): bool
    {
        // This should match the logic in the calendar services
        return in_array($session->session_type, [
            \App\Enums\Tenant\CoachingSessionType::ONLINE,
            \App\Enums\Tenant\CoachingSessionType::HYBRID
        ]);
    }

    /**
     * Initialize tenant context
     */
    protected function initializeTenant(string $tenantId): bool
    {
        try {
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID '{$tenantId}' not found.");
                $this->line("Available tenants:");
                
                $tenants = Tenant::limit(10)->get();
                foreach ($tenants as $t) {
                    $this->line("- {$t->id}");
                }
                
                return false;
            }
            
            // Initialize tenancy for this tenant
            tenancy()->initialize($tenant);
            
            $this->line("✅ Initialized tenant: {$tenant->id}");
            
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to initialize tenant: {$e->getMessage()}");
            return false;
        }
    }
}
