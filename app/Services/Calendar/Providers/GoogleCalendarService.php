<?php

namespace App\Services\Calendar\Providers;

use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\CalendarEvent;
use App\Services\Calendar\BaseCalendarService;
use App\Services\Calendar\CalendarEventData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService extends BaseCalendarService
{
    protected function getApiBaseUrl(): string
    {
        return 'https://www.googleapis.com/calendar/v3';
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
        $calendarId = 'primary'; // Use primary calendar by default
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'POST',
            "{$this->getApiBaseUrl()}/calendars/{$calendarId}/events",
            $payload
        );

        if (!$response->successful()) {
            Log::error('Google Calendar event creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'session_id' => $session->id
            ]);
            throw new \Exception('Failed to create Google Calendar event: ' . $response->body());
        }

        return $this->extractEventData($response->json());
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
        $calendarId = $calendarEvent->external_calendar_id ?: 'primary';
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'PUT',
            "{$this->getApiBaseUrl()}/calendars/{$calendarId}/events/{$calendarEvent->external_event_id}",
            $payload
        );

        if (!$response->successful()) {
            Log::error('Google Calendar event update failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'event_id' => $calendarEvent->external_event_id
            ]);
            throw new \Exception('Failed to update Google Calendar event: ' . $response->body());
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

        $calendarId = $calendarEvent->external_calendar_id ?: 'primary';
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'DELETE',
            "{$this->getApiBaseUrl()}/calendars/{$calendarId}/events/{$calendarEvent->external_event_id}"
        );

        if (!$response->successful() && $response->status() !== 410) { // 410 = already deleted
            Log::error('Google Calendar event deletion failed', [
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
        $calendarId = 'primary';
        
        $response = $this->makeAuthenticatedRequest(
            $integration,
            'GET',
            "{$this->getApiBaseUrl()}/calendars/{$calendarId}/events",
            [
                'timeMin' => $startDate->toISOString(),
                'timeMax' => $endDate->toISOString(),
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ]
        );

        if (!$response->successful()) {
            Log::error('Google Calendar events fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to fetch Google Calendar events: ' . $response->body());
        }

        $events = [];
        $data = $response->json();
        
        foreach ($data['items'] ?? [] as $eventData) {
            try {
                $events[] = $this->extractEventData($eventData);
            } catch (\Exception $e) {
                Log::warning('Failed to parse Google Calendar event', [
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
            'summary' => $this->generateEventTitle($session),
            'description' => $this->generateEventDescription($session),
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
                    'email' => $attendee['email'],
                    'displayName' => $attendee['name'],
                ];
            }, $attendees),
        ];

        // Add Google Meet conference if session type supports it
        if ($this->shouldCreateMeeting($session)) {
            $payload['conferenceData'] = [
                'createRequest' => [
                    'requestId' => 'coaching-session-' . $session->id,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ]
                ]
            ];
            $payload['conferenceDataVersion'] = 1;
        }

        return $payload;
    }

    protected function extractEventData(array $eventData): CalendarEventData
    {
        $startTime = $this->parseDateTime($eventData['start'] ?? []);
        $endTime = $this->parseDateTime($eventData['end'] ?? []);
        
        // Extract Google Meet link
        $meetingUrl = null;
        $meetingId = null;
        
        if (isset($eventData['conferenceData']['entryPoints'])) {
            foreach ($eventData['conferenceData']['entryPoints'] as $entryPoint) {
                if ($entryPoint['entryPointType'] === 'video') {
                    $meetingUrl = $entryPoint['uri'];
                    break;
                }
            }
            $meetingId = $eventData['conferenceData']['conferenceId'] ?? null;
        } elseif (isset($eventData['hangoutLink'])) {
            $meetingUrl = $eventData['hangoutLink'];
        }

        return new CalendarEventData(
            externalEventId: $eventData['id'],
            title: $eventData['summary'] ?? '',
            description: $eventData['description'] ?? '',
            startTime: $startTime,
            endTime: $endTime,
            location: $eventData['location'] ?? null,
            attendees: $this->extractAttendees($eventData['attendees'] ?? []),
            meetingUrl: $meetingUrl,
            meetingId: $meetingId,
            externalCalendarId: 'primary',
            rawData: $eventData
        );
    }

    private function parseDateTime(array $dateTimeData): Carbon
    {
        if (isset($dateTimeData['dateTime'])) {
            return Carbon::parse($dateTimeData['dateTime']);
        } elseif (isset($dateTimeData['date'])) {
            return Carbon::parse($dateTimeData['date'])->startOfDay();
        }
        
        throw new \Exception('Invalid date/time format in Google Calendar event');
    }

    private function extractAttendees(array $attendeesData): array
    {
        return array_map(function ($attendee) {
            return [
                'email' => $attendee['email'],
                'name' => $attendee['displayName'] ?? $attendee['email'],
                'status' => $attendee['responseStatus'] ?? 'needsAction',
            ];
        }, $attendeesData);
    }

    private function shouldCreateMeeting(CoachingSession $session): bool
    {
        // Create Google Meet for online and hybrid sessions
        return in_array($session->session_type, [
            \App\Enums\Tenant\CoachingSessionType::ONLINE,
            \App\Enums\Tenant\CoachingSessionType::HYBRID
        ]);
    }
}
