<?php

namespace App\Services\Calendar\Providers;

use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\CalendarEvent;
use App\Services\Calendar\BaseCalendarService;
use App\Services\Calendar\CalendarEventData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MicrosoftCalendarService extends BaseCalendarService
{
    protected function getApiBaseUrl(): string
    {
        return 'https://graph.microsoft.com/v1.0';
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function createEvent(CoachingSession $session, CalendarIntegration $integration): CalendarEventData
    {
        $payload = $this->buildEventPayload($session);
        
        Log::info('Creating Microsoft Calendar event', [
            'session_id' => $session->id,
            'session_type' => $session->session_type->value,
            'should_create_meeting' => $this->shouldCreateMeeting($session),
            'payload_has_online_meeting' => isset($payload['isOnlineMeeting']),
            'payload' => $payload
        ]);
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'POST',
            "{$this->getApiBaseUrl()}/me/events",
            $payload
        );

        if (!$response->successful()) {
            Log::error('Microsoft Calendar event creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'session_id' => $session->id,
                'payload' => $payload
            ]);
            throw new \Exception('Failed to create Microsoft Calendar event: ' . $response->body());
        }

        $responseData = $response->json();
        Log::info('Microsoft Calendar event created', [
            'session_id' => $session->id,
            'event_id' => $responseData['id'] ?? 'unknown',
            'has_online_meeting' => isset($responseData['onlineMeeting']),
            'is_online_meeting' => $responseData['isOnlineMeeting'] ?? false,
            'online_meeting_provider' => $responseData['onlineMeetingProvider'] ?? null
        ]);
        //Log all response data raw

        Log::info('Microsoft Calendar event response data', [
            'response_data' => $responseData
        ]);

        return $this->extractEventData($responseData);
    }

    public function updateEvent(CalendarEvent $calendarEvent, CoachingSession $session): CalendarEventData
    {
        $integration = $calendarEvent->user->calendarIntegrations()
            ->where('provider', $this->provider)
            ->first();

        if (!$integration) {
            throw new \Exception('No calendar integration found for user');
        }

        $payload = $this->buildEventPayload($session);
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'PATCH',
            "{$this->getApiBaseUrl()}/me/events/{$calendarEvent->external_event_id}",
            $payload
        );

        if (!$response->successful()) {
            Log::error('Microsoft Calendar event update failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'event_id' => $calendarEvent->external_event_id
            ]);
            throw new \Exception('Failed to update Microsoft Calendar event: ' . $response->body());
        }

        return $this->extractEventData($response->json());
    }

    public function deleteEvent(CalendarEvent $calendarEvent): bool
    {
        $integration = $calendarEvent->user->calendarIntegrations()
            ->where('provider', $this->provider)
            ->first();

        if (!$integration) {
            throw new \Exception('No calendar integration found for user');
        }
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'DELETE',
            "{$this->getApiBaseUrl()}/me/events/{$calendarEvent->external_event_id}"
        );

        if (!$response->successful() && $response->status() !== 404) { // 404 = already deleted
            Log::error('Microsoft Calendar event deletion failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'event_id' => $calendarEvent->external_event_id
            ]);
            return false;
        }

        return true;
    }

    public function fetchEvents(CalendarIntegration $integration, Carbon $startDate, Carbon $endDate): array
    {
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'GET',
            "{$this->getApiBaseUrl()}/me/calendarview",
            [
                'startDateTime' => $startDate->toISOString(),
                'endDateTime' => $endDate->toISOString(),
                '$orderby' => 'start/dateTime',
            ]
        );

        if (!$response->successful()) {
            Log::error('Microsoft Calendar events fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to fetch Microsoft Calendar events: ' . $response->body());
        }

        $events = [];
        $data = $response->json();
        
        foreach ($data['value'] ?? [] as $eventData) {
            try {
                $events[] = $this->extractEventData($eventData);
            } catch (\Exception $e) {
                Log::warning('Failed to parse Microsoft Calendar event', [
                    'event_id' => $eventData['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $events;
    }

    protected function buildEventPayload(CoachingSession $session): array
    {
        $attendees = $this->getAttendeesFromSession($session);
        
        $payload = [
            'subject' => $this->generateEventTitle($session),
            'body' => [
                'contentType' => 'text',
                'content' => $this->generateEventDescription($session),
            ],
            'start' => [
                'dateTime' => $session->scheduled_at->toISOString(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $session->planned_end_time->toISOString(),
                'timeZone' => config('app.timezone'),
            ],
            'attendees' => array_map(function ($attendee) {
                return [
                    'emailAddress' => [
                        'address' => $attendee['email'],
                        'name' => $attendee['name'],
                    ],
                    'type' => $attendee['role'] === 'coach' ? 'required' : 'optional',
                ];
            }, $attendees),
        ];

        // Add Teams meeting if session type supports it
        if ($this->shouldCreateMeeting($session)) {
            $payload['isOnlineMeeting'] = true;
            $payload['onlineMeetingProvider'] = 'teamsForBusiness';
        }

        return $payload;
    }

    protected function extractEventData(array $eventData): CalendarEventData
    {
        $startTime = Carbon::parse($eventData['start']['dateTime']);
        $endTime = Carbon::parse($eventData['end']['dateTime']);
        
        // Extract Teams meeting link
        $meetingUrl = null;
        $meetingId = null;
        
        if (isset($eventData['onlineMeeting'])) {
            $meetingUrl = $eventData['onlineMeeting']['joinUrl'] ?? null;
            $meetingId = $eventData['onlineMeeting']['conferenceId'] ?? null;
        }

        return new CalendarEventData(
            externalEventId: $eventData['id'],
            title: $eventData['subject'] ?? '',
            description: $eventData['body']['content'] ?? '',
            startTime: $startTime,
            endTime: $endTime,
            location: $eventData['location']['displayName'] ?? null,
            attendees: $this->extractAttendees($eventData['attendees'] ?? []),
            meetingUrl: $meetingUrl,
            meetingId: $meetingId,
            externalCalendarId: null, // Microsoft Graph uses user's default calendar
            rawData: $eventData
        );
    }

    private function extractAttendees(array $attendeesData): array
    {
        return array_map(function ($attendee) {
            return [
                'email' => $attendee['emailAddress']['address'],
                'name' => $attendee['emailAddress']['name'] ?? $attendee['emailAddress']['address'],
                'status' => strtolower($attendee['status']['response'] ?? 'none'),
            ];
        }, $attendeesData);
    }

    private function shouldCreateMeeting(CoachingSession $session): bool
    {
        // Create Teams meeting for online and hybrid sessions
        return in_array($session->session_type, [
            \App\Enums\Tenant\CoachingSessionType::ONLINE,
            \App\Enums\Tenant\CoachingSessionType::HYBRID
        ]);
    }
}
