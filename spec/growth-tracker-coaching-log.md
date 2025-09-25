# Growth Tracker - Coaching Log Specification

## Overview
The Coaching Log is a simple report table within the Growth Tracker section that provides coaches with a date-range filtered view of their coaching activities. This feature aggregates coaching session data within a specified date range, grouped by client.

## Requirements

The Coaching Log should display:

1. **Date Range Filter** - Allow coaches to select start and end dates for the report
2. **Report Table** with the following columns:
   - **Client Name** - The name of the client
   - **Number of Sessions** - Total coaching sessions conducted with this client within the date range
   - **Total Hours** - Total hours of coaching provided to this client within the date range

## Current System Analysis

### Existing Models and Relationships
- `User` model has `assignedClients()` and `coachSessions()` relationships
- `CoachingSession` model tracks sessions with `coach_id`, `client_id`, `duration`, `scheduled_at`

### Current Implementation Status
- Basic `CoachingLogController` exists but only renders empty page
- Vue component shows placeholder pattern
- Route is configured in `routes/tenant/coach.php`

## Implementation Plan

### Backend Implementation

#### CoachingLogController Enhancement
- Add date range filtering parameters (`start_date`, `end_date`)
- Query coaching sessions within date range for the authenticated coach
- Group results by client and aggregate:
  - Count of sessions per client
  - Sum of duration (hours) per client
- Return structured data for frontend consumption

#### Database Query Strategy
```php
$sessions = CoachingSession::where('coach_id', auth()->id())
    ->whereBetween('scheduled_at', [$startDate, $endDate])
    ->with('client:id,name')
    ->selectRaw('client_id, COUNT(*) as session_count, SUM(duration) as total_minutes')
    ->groupBy('client_id')
    ->get();
```

### Frontend Implementation

#### Date Range Filter Component
- Use existing date picker components
- Default to current month or last 30 days
- Allow custom date range selection

#### Simple Report Table
- Display client name, session count, and total hours
- No pagination needed (limited by date range)
- No sorting needed (simple report)
- Clean, minimal design

## Technical Implementation Details

### Backend Changes Required

1. **CoachingLogController Enhancement**
```php
public function index(Request $request)
{
    // Validate date range inputs
    $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);
    
    // Default to last 30 days if no dates provided
    $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subDays(30);
    $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();
    
    // Get coaching sessions grouped by client within date range
    $coachingLog = CoachingSession::where('coach_id', auth()->id())
        ->whereBetween('scheduled_at', [$startDate, $endDate])
        ->with('client:id,name')
        ->selectRaw('client_id, COUNT(*) as session_count, SUM(duration) as total_minutes')
        ->groupBy('client_id')
        ->get()
        ->map(function ($item) {
            return [
                'client' => $item->client,
                'session_count' => $item->session_count,
                'total_hours' => round($item->total_minutes / 60, 1),
            ];
        });
    
    return Inertia::render('Tenant/coach/growth-tracker/CoachingLog', [
        'coachingLog' => $coachingLog,
        'filters' => [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]
    ]);
}
```

### Frontend Changes Required

1. **Updated CoachingLog.vue Page**
- Add date range picker component
- Simple table to display results
- Handle date range changes and reload data

2. **Type Definitions**
```typescript
interface CoachingLogEntry {
    client: {
        id: number;
        name: string;
    };
    session_count: number;
    total_hours: number;
}

interface CoachingLogFilters {
    start_date: string;
    end_date: string;
}
```

## Success Criteria

1. **Functional Requirements**
   - ✅ Date range filter (defaults to last 30 days)
   - ✅ Display coaching sessions grouped by client
   - ✅ Show session count and total hours per client
   - ✅ Responsive table design

2. **Performance Requirements**
   - ✅ Fast query execution with date range filtering
   - ✅ Simple, lightweight implementation

3. **User Experience Requirements**
   - ✅ Intuitive date range selection
   - ✅ Clear data presentation
   - ✅ Consistent with existing application design

---

*Simple coaching log report table with date range filtering.*
