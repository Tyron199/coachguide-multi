<?php

namespace App\Services\Calendar;

use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CalendarIntegration;
use App\Models\Tenant\CalendarEvent;
use Carbon\Carbon;

interface CalendarServiceInterface
{
    /**
     * Create a calendar event from a coaching session
     */
    public function createEvent(CoachingSession $session, CalendarIntegration $integration): CalendarEventData;
    
    /**
     * Update an existing calendar event
     */
    public function updateEvent(CalendarEvent $calendarEvent, CoachingSession $session): CalendarEventData;
    
    /**
     * Delete a calendar event
     */
    public function deleteEvent(CalendarEvent $calendarEvent): bool;
    
    /**
     * Fetch events from the calendar within a date range
     */
    public function fetchEvents(CalendarIntegration $integration, Carbon $startDate, Carbon $endDate): array;
    
    /**
     * Get the provider name
     */
    public function getProviderName(): string;
    
    /**
     * Check if the integration has valid tokens
     */
    public function hasValidTokens(CalendarIntegration $integration): bool;
}
