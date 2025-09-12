<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Coach\StoreCustomFrameworkRequest;
use App\Http\Requests\Tenant\Coach\UpdateCustomFrameworkRequest;
use App\Models\Tenant\CoachingFramework;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomFrameworkController extends Controller
{
    /**
     * Display a listing of custom frameworks
     */
    public function index()
    {
        $frameworks = CoachingFramework::userCreated()
            ->where('created_by_coach_id', auth()->id())
            ->with('instances')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($framework) {
                return [
                    'id' => $framework->id,
                    'slug' => $framework->slug,
                    'name' => $framework->name,
                    'description' => $framework->description,
                    'category' => $framework->category,
                    'subcategory' => $framework->subcategory,
                    'best_for' => $framework->best_for,
                    'is_active' => $framework->is_active,
                    'usage_count' => $framework->usage_count,
                    'completed_count' => $framework->completed_count,
                    'created_at' => $framework->created_at,
                    'updated_at' => $framework->updated_at,
                ];
            });

        return Inertia::render('Tenant/coach/custom-frameworks/Index', [
            'frameworks' => $frameworks,
        ]);
    }

    /**
     * Show the form for creating a new custom framework
     */
    public function create()
    {
        return Inertia::render('Tenant/coach/custom-frameworks/Create', [
            'existingSubcategories' => $this->getExistingSubcategories(),
            'existingBestForOptions' => $this->getExistingBestForOptions(),
            'draft' => session('framework_draft'),
        ]);
    }

    /**
     * Store a newly created custom framework
     */
    public function store(StoreCustomFrameworkRequest $request)
    {
        $framework = CoachingFramework::create([
            'slug' => $this->generateUniqueSlug($request->name),
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'best_for' => $request->best_for ?? [],
            'schema' => $this->buildSchema($request->fields ?? []),
            'is_system' => false,
            'created_by_coach_id' => auth()->id(),
            'is_active' => false, // Start as inactive until properly built
        ]);

        // Clear any draft from session
        session()->forget('framework_draft');

        // Redirect to edit page to continue building
        return redirect()->route('tenant.coach.custom-frameworks.edit', $framework->id)
            ->with('success', 'Framework created! Now add your questions and customize it.');
    }

    /**
     * Display the specified custom framework
     */
    public function show(CoachingFramework $framework)
    {
        // Ensure user can only view their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Tenant/coach/custom-frameworks/Show', [
            'framework' => [
                'id' => $framework->id,
                'slug' => $framework->slug,
                'name' => $framework->name,
                'description' => $framework->description,
                'category' => $framework->category,
                'subcategory' => $framework->subcategory,
                'best_for' => $framework->best_for,
                'schema' => $framework->schema,
                'is_active' => $framework->is_active,
                'usage_count' => $framework->usage_count,
                'completed_count' => $framework->completed_count,
                'created_at' => $framework->created_at,
                'updated_at' => $framework->updated_at,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified custom framework
     */
    public function edit(CoachingFramework $framework)
    {
        // Ensure user can only edit their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Tenant/coach/custom-frameworks/Edit', [
            'framework' => [
                'id' => $framework->id,
                'slug' => $framework->slug,
                'name' => $framework->name,
                'description' => $framework->description,
                'category' => $framework->category,
                'subcategory' => $framework->subcategory,
                'best_for' => $framework->best_for,
                'schema' => $framework->schema,
                'fields' => $this->extractFieldsFromSchema($framework->schema),
            ],
            'existingSubcategories' => $this->getExistingSubcategories(),
            'existingBestForOptions' => $this->getExistingBestForOptions(),
        ]);
    }

    /**
     * Update the specified custom framework
     */
    public function update(UpdateCustomFrameworkRequest $request, CoachingFramework $framework)
    {
        // Ensure user can only update their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'best_for' => $request->best_for ?? [],
            'schema' => $this->buildSchema($request->fields ?? []),
        ];

        // Handle activation
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        $framework->update($updateData);

        return back()->with('success', 'Framework auto-saved successfully.');
    }

    /**
     * Publish the specified custom framework
     */
    public function publish(Request $request, CoachingFramework $framework)
    {
        // Ensure user can only publish their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'best_for' => $request->best_for ?? [],
            'schema' => $this->buildSchema($request->fields ?? []),
            'is_active' => true, // Mark as active when publishing
        ];

        $framework->update($updateData);

        return redirect()->route('tenant.coach.custom-frameworks.index')
            ->with('success', 'Framework published successfully!');
    }

    /**
     * Remove the specified custom framework
     */
    public function destroy(CoachingFramework $framework)
    {
        // Ensure user can only delete their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        // Check if framework has instances
        if ($framework->instances()->count() > 0) {
            return back()->withErrors([
                'framework' => 'Cannot delete framework that has been used in coaching sessions.'
            ]);
        }

        $framework->delete();

        return redirect()->route('tenant.coach.custom-frameworks.index')
            ->with('success', 'Custom framework deleted successfully!');
    }

    /**
     * Toggle active status of a custom framework
     */
    public function toggleActive(CoachingFramework $framework)
    {
        // Ensure user can only toggle their own custom frameworks
        if ($framework->created_by_coach_id !== auth()->id()) {
            abort(403);
        }

        $framework->update([
            'is_active' => !$framework->is_active
        ]);

        $status = $framework->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Framework {$status} successfully.",
            'is_active' => $framework->is_active
        ]);
    }

    /**
     * Save draft data to session (API endpoint)
     */
    public function saveDraft(Request $request)
    {
        session(['framework_draft' => $request->all()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully.'
        ]);
    }


    /**
     * Preview framework structure (API endpoint)
     */
    public function preview(Request $request)
    {
        $schema = $this->buildSchema($request->fields ?? []);
        
        return response()->json([
            'success' => true,
            'schema' => $schema,
            'preview_data' => $this->generatePreviewData($schema)
        ]);
    }

    /**
     * Validate framework data (API endpoint)
     */
    public function validateFramework(Request $request)
    {
        $errors = [];

        // Check for duplicate field keys
        $fieldKeys = collect($request->fields ?? [])->pluck('key')->filter();
        $duplicateKeys = $fieldKeys->duplicates();
        
        if ($duplicateKeys->isNotEmpty()) {
            $errors['fields'] = 'Duplicate field keys found: ' . $duplicateKeys->implode(', ');
        }

        // Check slug uniqueness
        if ($request->name) {
            $slug = $this->generateSlug($request->name);
            $exists = CoachingFramework::where('slug', $slug)
                ->when($request->framework_id, function ($query, $id) {
                    return $query->where('id', '!=', $id);
                })
                ->exists();
                
            if ($exists) {
                $errors['name'] = 'A framework with this name already exists.';
            }
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }

    /**
     * Get existing subcategories for dropdown
     */
    private function getExistingSubcategories(): array
    {
        return CoachingFramework::whereNotNull('subcategory')
            ->where('subcategory', '!=', '')
            ->distinct()
            ->pluck('subcategory')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get existing best_for options
     */
    private function getExistingBestForOptions(): array
    {
        return CoachingFramework::whereNotNull('best_for')
            ->get()
            ->pluck('best_for')
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Generate a unique slug from name
     */
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = $this->generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (CoachingFramework::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Build JSON schema from fields array
     */
    private function buildSchema(array $fields): array
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'type' => 'object',
            'properties' => [],
            'required' => []
        ];

        foreach ($fields as $field) {
            if (!empty($field['key']) && !empty($field['title'])) {
                $schema['properties'][$field['key']] = [
                    'type' => 'string',
                    'title' => $field['title'],
                    'description' => $field['description'] ?? ''
                ];
            }
        }

        // If no valid fields, add a placeholder
        if (empty($schema['properties'])) {
            $schema['properties']['placeholder'] = [
                'type' => 'string',
                'title' => 'Add your first question',
                'description' => 'This framework needs questions to be useful'
            ];
        }

        return $schema;
    }

    /**
     * Extract fields array from schema for editing
     */
    private function extractFieldsFromSchema(array $schema): array
    {
        $fields = [];
        
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $key => $property) {
                $fields[] = [
                    'id' => Str::uuid()->toString(),
                    'key' => $key,
                    'title' => $property['title'] ?? '',
                    'description' => $property['description'] ?? ''
                ];
            }
        }

        return $fields;
    }

    /**
     * Generate preview data for testing
     */
    private function generatePreviewData(array $schema): array
    {
        $previewData = [];
        
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $key => $property) {
                $previewData[$key] = '';
            }
        }

        return $previewData;
    }
}
