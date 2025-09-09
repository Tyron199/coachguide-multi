<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Tenant\CoachingSession;
use Carbon\Carbon;

class NoSessionOverlap implements ValidationRule
{
    private int $coachId;
    private string $scheduledDate;
    private string $scheduledTime;
    private int $duration;
    private string $timezone;
    private ?int $excludeSessionId;

    public function __construct(
        int $coachId,
        string $scheduledDate,
        string $scheduledTime,
        int $duration,
        string $timezone,
        ?int $excludeSessionId = null
    ) {
        $this->coachId = $coachId;
        $this->scheduledDate = $scheduledDate;
        $this->scheduledTime = $scheduledTime;
        $this->duration = $duration;
        $this->timezone = $timezone;
        $this->excludeSessionId = $excludeSessionId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // Parse the scheduled datetime in user's timezone and convert to UTC
            $scheduledAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                $this->scheduledDate . ' ' . $this->scheduledTime,
                $this->timezone
            )->utc();

            $endAt = $scheduledAt->copy()->addMinutes($this->duration);

            // Find existing sessions for this coach that might overlap
            $query = CoachingSession::where('coach_id', $this->coachId)
                ->where(function ($q) use ($scheduledAt, $endAt) {
                    // Check for overlaps: existing session overlaps if:
                    // 1. It starts before our session ends AND ends after our session starts
                    $q->where(function ($subQuery) use ($scheduledAt, $endAt) {
                        $subQuery->where('scheduled_at', '<', $endAt)
                                ->where('end_at', '>', $scheduledAt);
                    });
                });

            // Exclude current session if updating
            if ($this->excludeSessionId) {
                $query->where('id', '!=', $this->excludeSessionId);
            }

            $conflictingSessions = $query->with(['client'])->get();

            if ($conflictingSessions->isNotEmpty()) {
                $conflictDetails = $conflictingSessions->map(function ($session) {
                    $sessionStart = $session->scheduled_at->setTimezone($this->timezone);
                    $sessionEnd = $session->end_at->setTimezone($this->timezone);
                    
                    return sprintf(
                        '%s with %s (%s - %s)',
                        $sessionStart->format('M j, Y'),
                        $session->client->name,
                        $sessionStart->format('g:i A'),
                        $sessionEnd->format('g:i A')
                    );
                })->join(', ');

                $fail("This session conflicts with existing session(s): {$conflictDetails}");
            }
        } catch (\Exception $e) {
            $fail('Unable to validate session timing. Please check your date and time values.');
        }
    }
}
