<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Supervision;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupervisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Supervision::where('user_id', auth()->id())
            ->with('attachments');

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supervisor_name', 'like', "%{$search}%")
                  ->orWhere('themes_discussed', 'like', "%{$search}%")
                  ->orWhere('supervision_type', 'like', "%{$search}%");
            });
        }

        // Apply supervision type filter
        if ($request->has('supervision_type') && $request->supervision_type !== '' && $request->supervision_type !== 'all') {
            $query->where('supervision_type', $request->supervision_type);
        }

        // Get sorted records (most recent first)
        $supervisions = $query->orderBy('supervision_date', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'supervision_date' => $item->supervision_date->format('Y-m-d'),
                'duration_minutes' => $item->duration_minutes,
                'duration_hours' => round($item->duration_minutes / 60, 1),
                'supervisor_name' => $item->supervisor_name,
                'supervisor_contact' => $item->supervisor_contact,
                'supervisor_accreditation' => $item->supervisor_accreditation,
                'supervision_type' => $item->supervision_type,
                'session_format' => $item->session_format,
                'themes_discussed' => $item->themes_discussed,
                'reflections' => $item->reflections,
                'action_points' => $item->action_points,
                'ethical_considerations' => $item->ethical_considerations,
                'impact_on_practice' => $item->impact_on_practice,
                'attachments_count' => $item->attachments->count(),
                'created_at' => $item->created_at,
            ];
        });

        // Calculate statistics
        $stats = [
            'total_entries' => $supervisions->count(),
            'total_hours' => round($supervisions->sum('duration_minutes') / 60, 1),
            'total_attachments' => $supervisions->sum('attachments_count'),
        ];

        return Inertia::render('Tenant/coach/growth-tracker/SupervisionLog', [
            'supervisions' => $supervisions,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'supervision_type' => $request->supervision_type,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tenant/coach/growth-tracker/SupervisionLog/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supervision_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1|max:999',
            'supervisor_name' => 'required|string|max:255',
            'supervisor_contact' => 'nullable|string|max:255',
            'supervisor_accreditation' => 'nullable|string|max:255',
            'supervision_type' => 'required|string|max:255',
            'session_format' => 'required|string|max:255',
            'themes_discussed' => 'required|string',
            'reflections' => 'nullable|string',
            'action_points' => 'nullable|string',
            'ethical_considerations' => 'nullable|string',
            'impact_on_practice' => 'nullable|string',
        ]);

        $supervision = Supervision::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        // Handle file attachments if present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('supervision-attachments');
                $supervision->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => basename($path),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('tenant.coach.growth-tracker.supervision-log.index')
            ->with('success', 'Supervision log entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supervision $supervision)
    {
        // Ensure the supervision belongs to the authenticated user
        if ($supervision->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $supervision->load('attachments');

        return Inertia::render('Tenant/coach/growth-tracker/SupervisionLog/Show', [
            'supervision' => [
                'id' => $supervision->id,
                'supervision_date' => $supervision->supervision_date->format('Y-m-d'),
                'duration_minutes' => $supervision->duration_minutes,
                'supervisor_name' => $supervision->supervisor_name,
                'supervisor_contact' => $supervision->supervisor_contact,
                'supervisor_accreditation' => $supervision->supervisor_accreditation,
                'supervision_type' => $supervision->supervision_type,
                'session_format' => $supervision->session_format,
                'themes_discussed' => $supervision->themes_discussed,
                'reflections' => $supervision->reflections,
                'action_points' => $supervision->action_points,
                'ethical_considerations' => $supervision->ethical_considerations,
                'impact_on_practice' => $supervision->impact_on_practice,
                'attachments' => $supervision->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'formatted_size' => $a->formatted_size,
                    'is_image' => $a->is_image,
                    'url' => $a->url,
                ]),
                'created_at' => $supervision->created_at,
                'updated_at' => $supervision->updated_at,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supervision $supervision)
    {
        // Ensure the supervision belongs to the authenticated user
        if ($supervision->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $supervision->load('attachments');

        return Inertia::render('Tenant/coach/growth-tracker/SupervisionLog/Edit', [
            'supervision' => [
                'id' => $supervision->id,
                'supervision_date' => $supervision->supervision_date->format('Y-m-d'),
                'duration_minutes' => $supervision->duration_minutes,
                'supervisor_name' => $supervision->supervisor_name,
                'supervisor_contact' => $supervision->supervisor_contact,
                'supervisor_accreditation' => $supervision->supervisor_accreditation,
                'supervision_type' => $supervision->supervision_type,
                'session_format' => $supervision->session_format,
                'themes_discussed' => $supervision->themes_discussed,
                'reflections' => $supervision->reflections,
                'action_points' => $supervision->action_points,
                'ethical_considerations' => $supervision->ethical_considerations,
                'impact_on_practice' => $supervision->impact_on_practice,
                'attachments' => $supervision->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'original_name' => $a->original_name,
                    'formatted_size' => $a->formatted_size,
                    'is_image' => $a->is_image,
                    'url' => $a->url,
                ]),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supervision $supervision)
    {
        // Ensure the supervision belongs to the authenticated user
        if ($supervision->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'supervision_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1|max:999',
            'supervisor_name' => 'required|string|max:255',
            'supervisor_contact' => 'nullable|string|max:255',
            'supervisor_accreditation' => 'nullable|string|max:255',
            'supervision_type' => 'required|string|max:255',
            'session_format' => 'required|string|max:255',
            'themes_discussed' => 'required|string',
            'reflections' => 'nullable|string',
            'action_points' => 'nullable|string',
            'ethical_considerations' => 'nullable|string',
            'impact_on_practice' => 'nullable|string',
        ]);

        $supervision->update($validated);

        // Handle new file attachments if present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('supervision-attachments');
                $supervision->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => basename($path),
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('tenant.coach.growth-tracker.supervision-log.index')
            ->with('success', 'Supervision log entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supervision $supervision)
    {
        // Ensure the supervision belongs to the authenticated user
        if ($supervision->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $supervision->delete();

        return back()->with('success', 'Supervision log entry deleted successfully.');
    }

    /**
     * Delete multiple supervision entries.
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'supervisions' => 'required|array',
            'supervisions.*' => 'exists:supervisions,id',
        ]);
        
        $supervisions = Supervision::whereIn('id', $request->supervisions)->get();
        
        foreach ($supervisions as $supervision) {
            // Ensure each supervision belongs to the authenticated user
            if ($supervision->user_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }
            $supervision->delete();
        }
        
        return back()->with('success', 'Supervision log entries deleted successfully.');
    }
}
