<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CoachingLogController extends Controller
{
    public function index(Request $request)
    {
        // Validate date range inputs
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        // Default to last 30 days if no dates provided
        // Use user's timezone for date calculations
        $userTimezone = auth()->user()->timezone ?? 'UTC';
        $startDate = $request->start_date ? 
            Carbon::parse($request->start_date, $userTimezone) : 
            now($userTimezone)->subDays(30);
        $endDate = $request->end_date ? 
            Carbon::parse($request->end_date, $userTimezone) : 
            now($userTimezone);
        
        // Convert dates to UTC for database queries since sessions are stored in UTC
        $startDateUtc = $startDate->utc();
        $endDateUtc = $endDate->utc();
        
        // Start with all clients assigned to this coach, then aggregate their sessions within date range
        $coachingLog = auth()->user()->assignedClients()
            ->with('company:id,name')
            ->withCount([
                'clientSessions as session_count' => function ($query) use ($startDateUtc, $endDateUtc) {
                    $query->where('coach_id', auth()->id())
                          ->whereNotNull('end_at') // Only completed sessions
                          ->whereBetween('end_at', [$startDateUtc, $endDateUtc]);
                }
            ])
            ->withSum([
                'clientSessions as total_minutes' => function ($query) use ($startDateUtc, $endDateUtc) {
                    $query->where('coach_id', auth()->id())
                          ->whereNotNull('end_at') // Only completed sessions
                          ->whereBetween('end_at', [$startDateUtc, $endDateUtc]);
                }
            ], 'duration')
            ->get()
            ->map(function ($client) {
                return [
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'company' => $client->company ? [
                            'id' => $client->company->id,
                            'name' => $client->company->name,
                        ] : null,
                    ],
                    'session_count' => (int) $client->session_count,
                    'total_hours' => round((float) ($client->total_minutes ?? 0) / 60, 1),
                ];
            })
            ->filter(fn($entry) => $entry['session_count'] > 0) // Only show clients with sessions in the date range
            ->values(); // Re-index the collection as a proper array
        
        return Inertia::render('Tenant/coach/growth-tracker/CoachingLog', [
            'coachingLog' => $coachingLog,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
