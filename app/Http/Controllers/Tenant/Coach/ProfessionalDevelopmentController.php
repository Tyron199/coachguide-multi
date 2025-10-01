<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ProfessionalDevelopment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessionalDevelopmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProfessionalDevelopment::where('user_id', auth()->id());

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('course_title', 'like', "%{$search}%")
                  ->orWhere('training_provider', 'like', "%{$search}%");
            });
        }

        // Apply training type filter
        if ($request->has('training_type') && $request->training_type !== '' && $request->training_type !== 'all') {
            $query->where('training_type', $request->training_type);
        }

        // Get sorted records (most recent first)
        $developments = $query->orderBy('date_from', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'date_from' => $item->date_from->format('Y-m-d'),
                'date_to' => $item->date_to->format('Y-m-d'),
                'training_type' => $item->training_type,
                'training_provider' => $item->training_provider,
                'course_title' => $item->course_title,
                'accredited' => $item->accredited,
                'total_hours_theory' => $item->total_hours_theory,
                'total_hours_practical' => $item->total_hours_practical,
                'total_hours' => (float)$item->total_hours_theory + (float)$item->total_hours_practical,
                'created_at' => $item->created_at,
            ];
        });

        // Calculate statistics
        $stats = [
            'total_entries' => $developments->count(),
            'total_theory_hours' => $developments->sum('total_hours_theory'),
            'total_practical_hours' => $developments->sum('total_hours_practical'),
            'total_hours' => $developments->sum('total_hours'),
        ];

        return Inertia::render('Tenant/coach/growth-tracker/TrainingDevelopment', [
            'developments' => $developments,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'training_type' => $request->training_type,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tenant/coach/growth-tracker/TrainingDevelopment/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'training_type' => 'required|string|max:255',
            'training_provider' => 'required|string|max:255',
            'course_title' => 'required|string|max:255',
            'accredited' => 'required|boolean',
            'total_hours_theory' => 'nullable|numeric|min:0|max:99999.99',
            'total_hours_practical' => 'nullable|numeric|min:0|max:99999.99',
        ]);

        ProfessionalDevelopment::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()
            ->route('tenant.coach.growth-tracker.training-development.index')
            ->with('success', 'Training & Development entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProfessionalDevelopment $professionalDevelopment)
    {
        // Ensure the development belongs to the authenticated user
        if ($professionalDevelopment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Tenant/coach/growth-tracker/TrainingDevelopment/Show', [
            'development' => [
                'id' => $professionalDevelopment->id,
                'date_from' => $professionalDevelopment->date_from->format('Y-m-d'),
                'date_to' => $professionalDevelopment->date_to->format('Y-m-d'),
                'training_type' => $professionalDevelopment->training_type,
                'training_provider' => $professionalDevelopment->training_provider,
                'course_title' => $professionalDevelopment->course_title,
                'accredited' => $professionalDevelopment->accredited,
                'total_hours_theory' => $professionalDevelopment->total_hours_theory,
                'total_hours_practical' => $professionalDevelopment->total_hours_practical,
                'created_at' => $professionalDevelopment->created_at,
                'updated_at' => $professionalDevelopment->updated_at,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProfessionalDevelopment $professionalDevelopment)
    {
        // Ensure the development belongs to the authenticated user
        if ($professionalDevelopment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Tenant/coach/growth-tracker/TrainingDevelopment/Edit', [
            'development' => [
                'id' => $professionalDevelopment->id,
                'date_from' => $professionalDevelopment->date_from->format('Y-m-d'),
                'date_to' => $professionalDevelopment->date_to->format('Y-m-d'),
                'training_type' => $professionalDevelopment->training_type,
                'training_provider' => $professionalDevelopment->training_provider,
                'course_title' => $professionalDevelopment->course_title,
                'accredited' => $professionalDevelopment->accredited,
                'total_hours_theory' => $professionalDevelopment->total_hours_theory,
                'total_hours_practical' => $professionalDevelopment->total_hours_practical,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProfessionalDevelopment $professionalDevelopment)
    {
        // Ensure the development belongs to the authenticated user
        if ($professionalDevelopment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'training_type' => 'required|string|max:255',
            'training_provider' => 'required|string|max:255',
            'course_title' => 'required|string|max:255',
            'accredited' => 'required|boolean',
            'total_hours_theory' => 'nullable|numeric|min:0|max:99999.99',
            'total_hours_practical' => 'nullable|numeric|min:0|max:99999.99',
        ]);

        $professionalDevelopment->update($validated);

        return redirect()
            ->route('tenant.coach.growth-tracker.training-development.index')
            ->with('success', 'Training & Development entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProfessionalDevelopment $professionalDevelopment)
    {
        // Ensure the development belongs to the authenticated user
        if ($professionalDevelopment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $professionalDevelopment->delete();

        return back()->with('success', 'Training & Development entry deleted successfully.');
    }

    /**
     * Delete multiple professional development entries.
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'developments' => 'required|array',
            'developments.*' => 'exists:professional_developments,id',
        ]);
        
        $developments = ProfessionalDevelopment::whereIn('id', $request->developments)->get();
        
        foreach ($developments as $development) {
            // Ensure each development belongs to the authenticated user
            if ($development->user_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }
            $development->delete();
        }
        
        return back()->with('success', 'Training & Development entries deleted successfully.');
    }
}
