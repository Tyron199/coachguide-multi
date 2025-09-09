<?php

namespace App\Services\Calendar;

use Carbon\Carbon;

class CalendarEventData
{
    public function __construct(
        public string $externalEventId,
        public string $title,
        public string $description,
        public Carbon $startTime,
        public Carbon $endTime,
        public ?string $location = null,
        public array $attendees = [],
        public ?string $meetingUrl = null,
        public ?string $meetingId = null,
        public ?string $externalCalendarId = null,
        public array $rawData = []
    ) {}
    
    /**
     * Create from array (useful for API responses)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            externalEventId: $data['external_event_id'],
            title: $data['title'],
            description: $data['description'],
            startTime: Carbon::parse($data['start_time']),
            endTime: Carbon::parse($data['end_time']),
            location: $data['location'] ?? null,
            attendees: $data['attendees'] ?? [],
            meetingUrl: $data['meeting_url'] ?? null,
            meetingId: $data['meeting_id'] ?? null,
            externalCalendarId: $data['external_calendar_id'] ?? null,
            rawData: $data['raw_data'] ?? []
        );
    }
    
    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'external_event_id' => $this->externalEventId,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->startTime->toISOString(),
            'end_time' => $this->endTime->toISOString(),
            'location' => $this->location,
            'attendees' => $this->attendees,
            'meeting_url' => $this->meetingUrl,
            'meeting_id' => $this->meetingId,
            'external_calendar_id' => $this->externalCalendarId,
            'raw_data' => $this->rawData,
        ];
    }
}
